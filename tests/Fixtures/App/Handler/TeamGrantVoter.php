<?php

namespace Wexample\SymfonyBpmn\Tests\Fixtures\App\Handler;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Wexample\SymfonyBpmn\Tests\Fixtures\App\Entity\Team;

/**
 * What symfony-user's memberships answer, reduced: the grants of a user on a
 * team, written as its roles — `TEAM_editorial_administrator`.
 */
class TeamGrantVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Team;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return in_array('TEAM_' . $subject->name . '_' . $attribute, $token->getRoleNames(), true);
    }
}
