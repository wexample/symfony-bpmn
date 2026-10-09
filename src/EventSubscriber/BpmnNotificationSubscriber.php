<?php

namespace Wexample\SymfonyBpmn\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wexample\SymfonyBpmn\Event\BpmnMessageEvent;
use Wexample\SymfonyBpmn\Interface\BpmnRecipientResolverInterface;
use Wexample\SymfonyHelpers\Entity\Interfaces\AbstractEntityInterface;
use Wexample\SymfonyNotification\Service\NotificationService;

/**
 * A message of a process naming a `notification` — a category of
 * symfony-notification —, sent to whom the application's resolvers say.
 * Registered by the extension where symfony-notification is installed.
 */
class BpmnNotificationSubscriber implements EventSubscriberInterface
{
    /**
     * @param iterable<BpmnRecipientResolverInterface> $resolvers
     */
    public function __construct(
        private readonly NotificationService $notifications,
        #[AutowireIterator(BpmnRecipientResolverInterface::TAG)]
        private readonly iterable $resolvers = [],
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [BpmnMessageEvent::class => 'onMessage'];
    }

    public function onMessage(BpmnMessageEvent $event): void
    {
        if ($event->notification === null) {
            return;
        }

        $recipients = [];

        foreach ($this->resolvers as $resolver) {
            array_push($recipients, ...$resolver->getRecipients($event));
        }

        if (! $recipients) {
            return;
        }

        $this->notifications->notify(
            $event->notification,
            $recipients,
            $event->subject instanceof AbstractEntityInterface ? $event->subject : null,
            ['process' => $event->processId, 'node' => $event->nodeId],
        );
    }
}
