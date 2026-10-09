<?php

namespace Wexample\SymfonyBpmn\Interface;

/**
 * A subject belonging to a group — the team of an article, the agency of a
 * lease —: a lane naming a `grant` is done by whoever holds that grant on
 * this group, asked as `is_granted(grant, group)` — symfony-user's
 * memberships answer it.
 */
interface BpmnGroupSubjectInterface extends BpmnSubjectInterface
{
    public function getBpmnGroup(): ?object;
}
