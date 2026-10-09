<?php

namespace Wexample\SymfonyBpmn\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Wexample\SymfonyBpmn\Interface\BpmnSubjectInterface;
use Wexample\SymfonyBpmn\Message\BpmnTimerMessage;
use Wexample\SymfonyBpmn\Service\BpmnRunner;

/**
 * Fires a timer that came due, if its subject still waits for it — the
 * subject may have moved on, or been deleted, meanwhile.
 */
#[AsMessageHandler]
class BpmnTimerMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BpmnRunner $runner,
    ) {
    }

    public function __invoke(BpmnTimerMessage $message): void
    {
        $subject = class_exists($message->subjectClass) ? $this->entityManager->find($message->subjectClass, $message->subjectId) : null;

        if (! $subject instanceof BpmnSubjectInterface || $subject->getBpmnProcessId() !== $message->processId) {
            return;
        }

        if ($this->runner->fireTimer($subject, $message->transition)) {
            $this->entityManager->flush();
        }
    }
}
