<?php

namespace Wexample\SymfonyBpmn\Tests\Fixtures\App\Handler;

use Wexample\SymfonyBpmn\Interface\BpmnSubjectInterface;
use Wexample\SymfonyBpmn\Interface\BpmnTaskHandlerInterface;

/**
 * What the platform does in the example processes: the lease generated, the
 * letter rendered, archived — each noted in the variables.
 */
class PlatformHandler implements BpmnTaskHandlerInterface
{
    /** @var list<string> */
    public array $done = [];

    public function supports(string $processId, string $taskId): bool
    {
        return in_array($taskId, ['generate_lease', 'render_pdf', 'archive'], true);
    }

    public function handle(BpmnSubjectInterface $subject, string $processId, string $taskId): array
    {
        $this->done[] = $taskId;

        return [$taskId . '_done' => true];
    }
}
