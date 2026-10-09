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

    public function testAVisitIsRemindedTheDayAndTheHourBefore(): void
    {
        $visit = $this->folder();
        $runner = $this->runner();

        $this->signIn('ROLE_PROSPECT');
        $runner->start($visit, 'visit_booking');
        $this->signIn('ROLE_AGENT');
        $runner->complete($visit, 'qualify');

        // Slots offered: an answer, or two days of silence.
        $this->assertSame(['answer'], $runner->getWaitingNodes($visit));
        $this->assertSame(['no_answer'], $this->timers()->getTransitions());

        $runner->receive($visit, 'slot_chosen', ['visit_at' => '2026-10-20T10:00:00+00:00']);
        $this->assertSame(['day_before'], $runner->getWaitingNodes($visit));
        // A date given as ISO 8601 text, then as a DateTime: both scheduled.
        $this->assertSame('2026-10-19T10:00:00+00:00', end($this->timers()->scheduled)[1]->format(DATE_ATOM));

        $runner->fireTimer($visit, 'day_before');
        $this->assertSame(['hour_before'], $runner->getWaitingNodes($visit));
        $this->assertSame('2026-10-20T09:00:00+00:00', end($this->timers()->scheduled)[1]->format(DATE_ATOM));

        $runner->fireTimer($visit, 'hour_before');
        $this->assertSame(['visit'], $runner->getWaitingNodes($visit));
        $this->assertSame(['offer_slots', 'remind_day_before', 'remind_hour_before'], $this->messageNodes());

        $runner->complete($visit, 'visit');
        $this->assertSame(['visited'], $runner->getWaitingNodes($visit));
    }

    public function testASilentProspectIsRelaunchedOnceThenGivenUp(): void
    {
        $visit = $this->folder();
        $runner = $this->runner();

        $this->signIn('ROLE_PROSPECT');
        $runner->start($visit, 'visit_booking');
        $this->signIn('ROLE_AGENT');
        $runner->complete($visit, 'qualify');

        $runner->fireTimer($visit, 'no_answer');
        $this->assertSame(['answer'], $runner->getWaitingNodes($visit));
        $this->assertTrue($visit->getBpmnVariables()['relaunched']);

        $runner->fireTimer($visit, 'no_answer');
        $this->assertSame(['given_up'], $runner->getWaitingNodes($visit));
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
