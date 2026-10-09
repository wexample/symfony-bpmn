<?php

namespace Wexample\SymfonyBpmn\Entity;

use Symfony\Component\Uid\Uuid;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * What the validator says of a process: an error that keeps it from running,
 * or what is drawn and not run.
 *
 * Read from the `.bpmn` files of the registry, never stored: no `#[ORM\Entity]`.
 * Its id derives from what identifies it, so a list read twice holds the same
 * entities.
 */
#[PseudocodeExport(inherited: true)]
class BpmnIssue extends AbstractEntity
{
    public const string ID_NAMESPACE = 'dd60665a-86fa-575c-9175-6a3987c103ab';

    protected string $processId;

    protected int $position;

    protected string $severity;

    protected string $code;

    protected ?string $elementId;

    protected string $message;

    public function __construct(
        string $processId,
        int $position,
        string $severity,
        string $code,
        ?string $elementId,
        string $message,
    ) {
        parent::__construct();

        $this->processId = $processId;
        $this->position = $position;
        $this->severity = $severity;
        $this->code = $code;
        $this->elementId = $elementId;
        $this->message = $message;

        $this->setId(Uuid::v5(Uuid::fromString(self::ID_NAMESPACE), implode("\0", [(string) $processId, (string) $position])));
    }

    public function getProcessId(): string
    {
        return $this->processId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getElementId(): ?string
    {
        return $this->elementId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
