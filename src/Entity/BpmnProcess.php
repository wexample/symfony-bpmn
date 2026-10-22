<?php

namespace Wexample\SymfonyBpmn\Entity;

use Symfony\Component\Uid\Uuid;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * A process the application holds, as a list shows it: what it is called, how
 * large it is, whether it holds together.
 *
 * Read from the `.bpmn` files of the registry, never stored: no `#[ORM\Entity]`.
 * Its id derives from what identifies it, so a list read twice holds the same
 * entities.
 */
#[PseudocodeExport(inherited: true)]
class BpmnProcess extends AbstractEntity
{
    public const string ID_NAMESPACE = '38dc4450-63f2-5afc-82b5-d967f42f4689';

    protected string $processId;

    protected ?string $name;

    protected ?string $documentation;

    protected bool $executable;

    protected int $laneCount;

    protected int $nodeCount;

    protected int $flowCount;

    protected bool $valid;

    protected int $errorCount;

    protected int $warningCount;

    public function __construct(
        string $processId,
        ?string $name,
        ?string $documentation,
        bool $executable,
        int $laneCount,
        int $nodeCount,
        int $flowCount,
        bool $valid,
        int $errorCount,
        int $warningCount,
    ) {
        parent::__construct();

        $this->processId = $processId;
        $this->name = $name;
        $this->documentation = $documentation;
        $this->executable = $executable;
        $this->laneCount = $laneCount;
        $this->nodeCount = $nodeCount;
        $this->flowCount = $flowCount;
        $this->valid = $valid;
        $this->errorCount = $errorCount;
        $this->warningCount = $warningCount;

        $this->setId(Uuid::v5(Uuid::fromString(self::ID_NAMESPACE), implode("\0", [(string) $processId])));
    }

    public function getProcessId(): string
    {
        return $this->processId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDocumentation(): ?string
    {
        return $this->documentation;
    }

    public function getExecutable(): bool
    {
        return $this->executable;
    }

    public function getLaneCount(): int
    {
        return $this->laneCount;
    }

    public function getNodeCount(): int
    {
        return $this->nodeCount;
    }

    public function getFlowCount(): int
    {
        return $this->flowCount;
    }

    public function getValid(): bool
    {
        return $this->valid;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function getWarningCount(): int
    {
        return $this->warningCount;
    }
}
