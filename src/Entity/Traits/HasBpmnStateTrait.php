<?php

namespace Wexample\SymfonyBpmn\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Where an entity stands in its process (BpmnSubjectInterface): which
 * process, the places marked, the variables its conditions read.
 */
trait HasBpmnStateTrait
{
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
    protected ?string $bpmnProcessId = null;

    /**
     * @var array<string, int>
     */
    #[ORM\Column(type: Types::JSON, options: ['default' => '{}'])]
    protected array $bpmnMarking = [];

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON, options: ['default' => '{}'])]
    protected array $bpmnVariables = [];

    public function getBpmnProcessId(): ?string
    {
        return $this->bpmnProcessId;
    }

    public function setBpmnProcessId(?string $processId): static
    {
        $this->bpmnProcessId = $processId;

        return $this;
    }

    public function getBpmnMarking(): array
    {
        return $this->bpmnMarking;
    }

    public function setBpmnMarking(array $marking, array $context = []): static
    {
        $this->bpmnMarking = $marking;

        return $this;
    }

    public function getBpmnVariables(): array
    {
        return $this->bpmnVariables;
    }

    public function setBpmnVariables(array $variables): static
    {
        $this->bpmnVariables = $variables;

        return $this;
    }
}
