<?php

namespace Wexample\SymfonyBpmn\Tests\Fixtures\App\Handler;

use DateTimeImmutable;
use Wexample\SymfonyBpmn\Interface\BpmnSubjectInterface;
use Wexample\SymfonyBpmn\Interface\BpmnTaskHandlerInterface;

/**
 * The platform of the visit booking: the reminders planned from the slot
 * chosen, the prospect relaunched once.
 */
class VisitHandler implements BpmnTaskHandlerInterface
{
    public function supports(string $processId, string $taskId): bool
    {
        return $processId === 'visit_booking' && in_array($taskId, ['plan_reminders', 'relaunch'], true);
    }

    public function handle(BpmnSubjectInterface $subject, string $processId, string $taskId): array
    {
        if ($taskId === 'relaunch') {
            return ['relaunched' => true];
        }

        $visit = new DateTimeImmutable($subject->getBpmnVariables()['visit_at']);

        return [
            'remind_day_before' => $visit->modify('-1 day')->format(DATE_ATOM),
            'remind_hour_before' => $visit->modify('-1 hour'),
        ];
    }
}
