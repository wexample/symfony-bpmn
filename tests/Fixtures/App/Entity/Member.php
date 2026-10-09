<?php

namespace Wexample\SymfonyBpmn\Tests\Fixtures\App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyMail\Interface\MailRecipientInterface;

/**
 * Someone a message of a process is for.
 */
#[ORM\Entity]
class Member extends AbstractEntity implements MailRecipientInterface
{
    public function __construct(
        #[ORM\Column(type: Types::STRING)]
        private string $email,
    ) {
        parent::__construct();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
