<?php

namespace Wexample\SymfonyBpmn\Tests\Fixtures\App\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Wexample\SymfonyBpmn\Event\BpmnMessageEvent;
use Wexample\SymfonyBpmn\Interface\BpmnRecipientResolverInterface;
use Wexample\SymfonyBpmn\Tests\Fixtures\App\Entity\Member;

/**
 * Every member watches every article.
 */
class WatcherResolver implements BpmnRecipientResolverInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getRecipients(BpmnMessageEvent $event): iterable
    {
        return $this->entityManager->getRepository(Member::class)->findAll();
    }
}
