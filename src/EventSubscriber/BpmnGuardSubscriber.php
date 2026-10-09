<?php

namespace Wexample\SymfonyBpmn\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;
use Wexample\SymfonyBpmn\Interface\BpmnSubjectInterface;
use Wexample\SymfonyBpmn\Service\BpmnConditionEvaluator;
use Wexample\SymfonyBpmn\Service\BpmnActorContext;
use Wexample\SymfonyBpmn\Service\LaneAccessService;

/**
 * Blocks the transitions of a BPMN workflow whose conditions do not hold,
 * and — done by someone — those of a lane the signed-in user is not of.
 * What the application does, or a timer, is not asked for a lane.
 */
class BpmnGuardSubscriber implements EventSubscriberInterface
{
    public const string BLOCKED_BY_CONDITION = 'bpmn.condition';
    public const string BLOCKED_BY_LANE = 'bpmn.lane';

    public function __construct(
        private readonly BpmnConditionEvaluator $conditions,
        private readonly LaneAccessService $laneAccess,
        private readonly BpmnActorContext $actor,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return ['workflow.guard' => 'onGuard'];
    }

    public function onGuard(GuardEvent $event): void
    {
        $workflowName = $event->getWorkflowName();
        $subject = $event->getSubject();

        if (! str_starts_with($workflowName, 'bpmn.') || ! $subject instanceof BpmnSubjectInterface) {
            return;
        }

        $metadata = $event->getMetadata('bpmn_conditions', $event->getTransition()) ?? [];
        $negated = $event->getMetadata('bpmn_negated', $event->getTransition()) ?? [];

        if (! $this->conditions->holds($subject, $metadata, $negated)) {
            $event->addTransitionBlocker(new TransitionBlocker('Its conditions do not hold.', self::BLOCKED_BY_CONDITION));

            return;
        }

        $automatic = $event->getMetadata('bpmn_automatic', $event->getTransition())
            || $event->getMetadata('bpmn_timer', $event->getTransition()) !== null
            || $event->getMetadata('bpmn_kind', $event->getTransition()) === 'join';

        if ($automatic || $this->actor->isSystem()) {
            return;
        }

        $processId = substr($workflowName, strlen('bpmn.'));

        if (! $this->laneAccess->mayAct($subject, $processId, $event->getMetadata('bpmn_lane', $event->getTransition()))) {
            $event->addTransitionBlocker(new TransitionBlocker('Not done by someone of its lane.', self::BLOCKED_BY_LANE));
        }
    }
}
