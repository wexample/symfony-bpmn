<?php

namespace Wexample\SymfonyBpmn\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wexample\SymfonyActivity\Service\ActivityRecorder;
use Wexample\SymfonyActivity\Service\ActivitySubjectService;
use Wexample\SymfonyBpmn\Event\BpmnTransitionEvent;

/**
 * Each step of a process kept in the history of its subject, symfony-
 * activity's `bpmn` category: what was done, by whom — or by the
 * application. Registered by the extension where symfony-activity is
 * installed; kept as long as the application keeps the category.
 */
class BpmnActivitySubscriber implements EventSubscriberInterface
{
    public const string CATEGORY = 'bpmn';

    public function __construct(
        private readonly ActivityRecorder $recorder,
        private readonly ActivitySubjectService $subjects,
        private readonly ?Security $security = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [BpmnTransitionEvent::class => 'onTransition'];
    }

    public function onTransition(BpmnTransitionEvent $event): void
    {
        $transition = $event->transition;
        // Who did it, when the application did not, and it can be named.
        $actor = $event->system ? null : $this->security?->getUser();

        $this->recorder->record(
            $event->subject,
            self::CATEGORY,
            'bpmn.' . $transition->kind,
            array_filter([
                'process' => $event->processId,
                'node' => $transition->node,
                'label' => $transition->label,
                'lane' => $transition->lane,
                'to' => implode(',', $transition->to),
                'by' => $event->system ? 'application' : null,
            ], static fn ($value) => $value !== null && $value !== ''),
            $actor !== null && $this->subjects->supports($actor) ? $actor : null,
        );
    }
}
