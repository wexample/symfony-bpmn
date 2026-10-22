<?php

namespace Wexample\SymfonyBpmn\Interface;

use Wexample\SymfonyBpmn\Event\BpmnMessageEvent;

/**
 * Who a message of a process is for — the landlord of this lease, the
 * members of this team —: what the drawing cannot say. With
 * symfony-notification installed, each message naming a `notification` is
 * sent to them. Tagged by the class; every resolver is asked.
 */
interface BpmnRecipientResolverInterface
{
    public const string TAG = 'wexample_bpmn.recipient_resolver';

    /**
     * @return iterable<object> entities symfony-notification can notify
     */
    public function getRecipients(BpmnMessageEvent $event): iterable;
}
