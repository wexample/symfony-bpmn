<?php

namespace Wexample\SymfonyBpmn\Tests\Fixtures\App\Entity;

/**
 * The group of a folder, asked about by TeamGrantVoter.
 */
final readonly class Team
{
    public function __construct(public string $name)
    {
    }
}
