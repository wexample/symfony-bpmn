<?php

namespace Wexample\SymfonyBpmn\Event;

use Wexample\SymfonyBpmn\Interface\BpmnSubjectInterface;

/**
 * A message a process sends — a send task done, a throw event passed —: what
 * an application, or symfony-notification, turns into a mail, a chat post.
 * `notification` is the category its node names (`wex:property
 * notification`).
 */
final class BpmnMessageEvent
{
    public function __construct(
        public readonly BpmnSubjectInterface $subject,
        public readonly string $processId,
        public readonly string $nodeId,
        public readonly ?string $messageRef,
        public readonly ?string $notification,
        public readonly ?string $laneId,
    ) {
    }
}
