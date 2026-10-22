<?php

namespace Wexample\SymfonyBpmn\Tests\Fixtures\App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Wexample\SymfonyBpmn\Entity\Traits\HasBpmnStateTrait;
use Wexample\SymfonyBpmn\Interface\BpmnGroupSubjectInterface;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * Whatever a process runs on — a lease, a request, a letter, an article —,
 * belonging to a team.
 */
#[ORM\Entity]
class Folder extends AbstractEntity implements BpmnGroupSubjectInterface
{
    use HasBpmnStateTrait;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $team = null;

    public function setTeam(?string $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getBpmnGroup(): ?object
    {
        return $this->team === null ? null : new Team($this->team);
    }
}
