<?php

namespace Wexample\SymfonyBpmn\Tests\Integration;

use Wexample\SymfonyBpmn\Exception\BpmnRuntimeException;
use Wexample\SymfonyBpmn\Tests\Fixtures\App\Handler\PlatformHandler;

class RunnerTest extends AbstractBpmnTestCase
{
    public function testARepairBelowTheThresholdIsDecidedByTheAgent(): void
    {
        $request = $this->folder();
        $runner = $this->runner();

        $this->signIn('ROLE_TENANT');
        $runner->start($request, 'maintenance_request');
        $this->assertSame(['get_quote'], $runner->getWaitingNodes($request));

        // The tenant does not get the quote.
        $this->expectRefusal(fn () => $runner->complete($request, 'get_quote', ['quote' => 150]), 'Not done by someone of its lane');

        $this->signIn('ROLE_AGENT');
        $runner->complete($request, 'get_quote', ['quote' => 150]);
        $this->assertSame(['agent_decides'], $runner->getWaitingNodes($request));

        $runner->complete($request, 'agent_decides');
        // `tell_landlord` is the platform's: done on its own, its message told.
        $this->assertSame(['order_works'], $runner->getWaitingNodes($request));
        $this->assertSame(['tell_landlord'], $this->messageNodes());

        $runner->complete($request, 'order_works');
        $this->assertSame(['works_done_received'], $runner->getWaitingNodes($request));
        $this->assertFalse($runner->isFinished($request));

        $this->assertTrue($runner->receive($request, 'works_done'));
        $this->assertSame(['repaired'], $runner->getWaitingNodes($request));
        $this->assertTrue($runner->isFinished($request));
    }

    public function testAboveTheThresholdTheLandlordDecidesAndIsReminded(): void
    {
        $request = $this->folder();
        $runner = $this->runner();

        $this->signIn('ROLE_TENANT');
        $runner->start($request, 'maintenance_request');
        $this->signIn('ROLE_AGENT');
        $runner->complete($request, 'get_quote', ['quote' => 350]);

        $this->assertSame(['landlord_decides'], $runner->getWaitingNodes($request));
        $this->assertSame('rental.quote_approval', $this->messages[0]->notification);
        $this->assertSame(['landlord_silent'], $this->timers()->getTransitions());
        $this->assertEqualsWithDelta((new \DateTimeImmutable('+3 days'))->getTimestamp(), $this->timers()->scheduled[0][1]->getTimestamp(), 5);

        // Three days later: reminded, waited for again — a timer anew.
        $this->assertTrue($runner->fireTimer($request, 'landlord_silent'));
        $this->assertSame(['landlord_decides'], $runner->getWaitingNodes($request));
        $this->assertSame(['ask_landlord', 'remind_landlord'], $this->messageNodes());
        $this->assertSame(['landlord_silent', 'landlord_silent'], $this->timers()->getTransitions());

        // The landlord lane is ROLE_OWNER by configuration, not ROLE_LANDLORD.
        $this->signIn('ROLE_LANDLORD');
        $this->assertSame([], $runner->getAvailableNodes($request));
        $this->signIn('ROLE_OWNER');
        $this->assertSame(['landlord_decides'], $runner->getAvailableNodes($request));

        $runner->complete($request, 'landlord_decides', ['approved' => false]);
        $this->assertSame(['refused'], $runner->getWaitingNodes($request));

        // Answered: the timer, when it comes, finds nothing to fire.
        $this->assertFalse($runner->fireTimer($request, 'landlord_silent'));
    }

    public function testLeaseAndDepositRunTogetherAndJoin(): void
    {
        $lease = $this->folder();
        $runner = $this->runner();

        $this->signIn('ROLE_AGENT');
        $runner->start($lease, 'tenant_move_in');

        $this->assertSame(['generate_lease'], self::getContainer()->get(PlatformHandler::class)->done);
        $this->assertTrue($lease->getBpmnVariables()['generate_lease_done']);
        $this->assertEqualsCanonicalizing(['lease_signed_by_all', 'pay_deposit'], $runner->getWaitingNodes($lease));

        $this->signIn('ROLE_TENANT');
        $runner->complete($lease, 'pay_deposit');
        // Half the join: still waiting for the lease.
        $this->assertSame(['lease_signed_by_all'], $runner->getWaitingNodes($lease));

        $runner->receive($lease, 'lease_signed');
        $this->assertSame(['register_guarantee'], $runner->getWaitingNodes($lease));
        $this->assertContains('guarantee_overdue', $this->timers()->getTransitions());
    }

    public function testATaskOfAnotherLaneOrNotWaitedForIsRefused(): void
    {
        $letter = $this->folder();
        $this->signIn('ROLE_MANAGER');
        $this->runner()->start($letter, 'engagement_letter');

        $this->expectRefusal(fn () => $this->runner()->complete($letter, 'partner_signs'), 'cannot be completed now');
    }

    public function testAGrantIsAskedOnTheGroupOfTheSubject(): void
    {
        $article = $this->folder('editorial');
        $runner = $this->runner();

        $this->signIn('TEAM_editorial_contributor');
        $runner->start($article, 'article_review');
        $this->assertSame([], $runner->getAvailableNodes($article), 'A contributor does not review.');

        $this->signIn('TEAM_studio_administrator');
        $this->assertSame([], $runner->getAvailableNodes($article), 'Administrator of another team.');

        $this->signIn('TEAM_editorial_administrator');
        $this->assertSame(['review'], $runner->getAvailableNodes($article));
        $runner->complete($article, 'review', ['approved' => true]);

        $this->assertTrue($runner->isFinished($article));
        $this->assertSame('article.published', $this->messages[0]->notification);
        $this->assertSame('article_published', $this->messages[0]->messageRef);
    }

    public function testAStartedSubjectIsNotStartedAgain(): void
    {
        $letter = $this->folder();
        $this->signIn('ROLE_MANAGER');
        $this->runner()->start($letter, 'engagement_letter');

        $this->expectException(BpmnRuntimeException::class);
        $this->runner()->start($letter, 'engagement_letter');
    }

    private function expectRefusal(callable $action, string $message): void
    {
        try {
            $action();
            $this->fail('Not refused.');
        } catch (BpmnRuntimeException $exception) {
            $this->assertStringContainsString($message, $exception->getMessage());
        }
    }
}
