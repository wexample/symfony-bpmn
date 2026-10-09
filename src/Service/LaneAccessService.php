<?php

namespace Wexample\SymfonyBpmn\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wexample\SymfonyBpmn\Interface\BpmnGroupSubjectInterface;
use Wexample\SymfonyBpmn\Interface\BpmnSubjectInterface;

/**
 * Who does what a lane does: the `role` it names, or the `grant` it names on
 * the subject's group — by the configuration, else by the lane itself. A lane
 * naming neither is anyone's; no lane, the same.
 */
class LaneAccessService
{
    /**
     * @param array<string, array<string, array{role: ?string, grant: ?string}>> $lanes
     */
    public function __construct(
        private readonly BpmnRegistry $registry,
        private readonly ?AuthorizationCheckerInterface $authorizationChecker = null,
        #[Autowire(param: 'wexample_symfony_bpmn.lanes')]
        private readonly array $lanes = [],
    ) {
    }

    /**
     * @return array{role: ?string, grant: ?string}
     */
    public function getRule(string $processId, string $laneId): array
    {
        $configured = $this->lanes[$processId][$laneId] ?? [];
        $lane = $this->registry->get($processId)->getLane($laneId);

        return [
            'role' => $configured['role'] ?? $lane?->getProperty('role'),
            'grant' => $configured['grant'] ?? $lane?->getProperty('grant'),
        ];
    }

    public function mayAct(BpmnSubjectInterface $subject, string $processId, ?string $laneId): bool
    {
        if ($laneId === null) {
            return true;
        }

        $rule = $this->getRule($processId, $laneId);

        if ($rule['role'] === null && $rule['grant'] === null) {
            return true;
        }

        if ($this->authorizationChecker === null) {
            return false;
        }

        if ($rule['role'] !== null && $this->authorizationChecker->isGranted($rule['role'])) {
            return true;
        }

        if ($rule['grant'] !== null && $subject instanceof BpmnGroupSubjectInterface && ($group = $subject->getBpmnGroup()) !== null) {
            return $this->authorizationChecker->isGranted($rule['grant'], $group);
        }

        return false;
    }
}
