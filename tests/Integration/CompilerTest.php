<?php

namespace Wexample\SymfonyBpmn\Tests\Integration;

use Wexample\SymfonyBpmn\Class\CompiledTransition;
use Wexample\SymfonyBpmn\Service\BpmnRegistry;

class CompilerTest extends AbstractBpmnTestCase
{
    private function registry(): BpmnRegistry
    {
        return self::getContainer()->get('test.registry');
    }

    public function testTheExamplesAreFoundAndSound(): void
    {
        $this->assertSame(['article_review', 'engagement_letter', 'maintenance_request', 'public_expenditure', 'tenant_move_in', 'visit_booking'], array_keys($this->registry()->all()));

        foreach (array_keys($this->registry()->all()) as $id) {
            $this->assertTrue($this->registry()->validate($id)->isValid(), $id);
            $this->assertNotEmpty($this->registry()->compile($id)->transitions);
        }
    }

    public function testAChoiceGivesATransitionPerWayUnderItsConditions(): void
    {
        $compiled = $this->registry()->compile('maintenance_request');
        $getQuote = $compiled->getTransitionsOf('get_quote');

        $this->assertCount(2, $getQuote);
        [$above, $below] = $getQuote;
        $this->assertSame(['ask_landlord'], $above->to);
        $this->assertSame(["variables['quote'] > 200"], $above->conditions);
        $this->assertSame(['agent_decides'], $below->to);
        $this->assertSame([], $below->conditions);
        $this->assertSame(["variables['quote'] > 200"], $below->negated);
        $this->assertSame('agent', $below->lane);
        $this->assertTrue($below->isHuman());

        $this->assertTrue($compiled->getTransition('ask_landlord')->automatic);
        $this->assertSame('works_done', $compiled->getTransition('works_done_received')->message);
    }

    public function testAParallelSplitMarksBothWaysAndTheJoinWaitsForBoth(): void
    {
        $compiled = $this->registry()->compile('tenant_move_in');

        $this->assertSame(['send_lease', 'pay_deposit'], $compiled->getTransition('generate_lease')->to);

        $join = $compiled->getTransition('lease_and_deposit_done');
        $this->assertSame(CompiledTransition::KIND_JOIN, $join->kind);
        $this->assertSame(['lease_and_deposit_done__flow_lease_signed_by_all_lease_and_deposit_done', 'lease_and_deposit_done__flow_pay_deposit_lease_and_deposit_done'], $join->from);
        $this->assertSame(['call_for_funds'], $join->to);
    }

    public function testABoundaryTimerIsATimedTransitionOfItsActivity(): void
    {
        $compiled = $this->registry()->compile('engagement_letter');
        $timer = $compiled->getTransition('client_silent');

        $this->assertSame(CompiledTransition::KIND_BOUNDARY, $timer->kind);
        $this->assertSame(['client_signs'], $timer->from);
        $this->assertSame(['remind_client'], $timer->to);
        $this->assertSame('P7D', $timer->timer);
        $this->assertTrue($timer->isTimed());
        $this->assertFalse($timer->isHuman());
    }

    public function testAThrowEventPassedIsRaisedByTheTransition(): void
    {
        $compiled = $this->registry()->compile('article_review');
        [$published] = array_values(array_filter($compiled->getTransitionsOf('review'), static fn ($transition) => $transition->to === ['published']));

        $this->assertSame(['announce'], $published->throws);
        $this->assertSame(["variables['approved']"], $published->conditions);
    }
}
