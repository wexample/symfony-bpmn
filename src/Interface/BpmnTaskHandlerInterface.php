<?php

namespace Wexample\SymfonyBpmn\Interface;

/**
 * What the application does for an automatic task — a service, send, script
 * or business rule task —, run before the process goes on past it. Tagged by
 * the class; it says which tasks it does.
 */
interface BpmnTaskHandlerInterface
{
    public const string TAG = 'wexample_bpmn.task_handler';

    public function supports(string $processId, string $taskId): bool;

    /**
     * @return array<string, mixed> variables to set on the subject — read by
     *                              the conditions met next
     */
    public function handle(BpmnSubjectInterface $subject, string $processId, string $taskId): array;
}
