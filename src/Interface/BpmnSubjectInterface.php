<?php

namespace Wexample\SymfonyBpmn\Interface;

/**
 * What a process runs on: an entity of the application — a lease, a letter,
 * an article — holding which process it follows, where it stands in it, and
 * the variables its conditions read. HasBpmnStateTrait keeps all three.
 */
interface BpmnSubjectInterface
{
    public function getId(): mixed;

    public function getBpmnProcessId(): ?string;

    public function setBpmnProcessId(?string $processId): static;

    /**
     * The places marked: place => 1.
     *
     * @return array<string, int>
     */
    public function getBpmnMarking(): array;

    /**
     * @param array<string, int> $marking
     */
    public function setBpmnMarking(array $marking, array $context = []): static;

    /**
     * What the conditions read, as `variables['quote']`.
     *
     * @return array<string, mixed>
     */
    public function getBpmnVariables(): array;

    /**
     * @param array<string, mixed> $variables
     */
    public function setBpmnVariables(array $variables): static;
}
