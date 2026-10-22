<?php

namespace Wexample\SymfonyBpmn\Tests\Integration;

use Wexample\SymfonyActivity\Class\ActivitySubject;
use Wexample\SymfonyActivity\Repository\ActivityRepository;
use Wexample\SymfonyBpmn\Tests\Fixtures\App\Entity\Folder;
use Wexample\SymfonyBpmn\Tests\Fixtures\App\Entity\Member;
use Wexample\SymfonyBpmn\Tests\Fixtures\App\IntegratedAppKernel;
use Wexample\SymfonyNotification\Repository\NotificationRepository;

/**
 * With symfony-activity and symfony-notification installed: each step kept
 * in the history of the subject, each message naming a notification sent to
 * whom the application says.
 */
class IntegrationTest extends AbstractBpmnTestCase
{
    protected static function getKernelClass(): string
    {
        return IntegratedAppKernel::class;
    }

    public function testStepsAreKeptAndMessagesNotified(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $watcher = new Member('watcher@example.com');
        $article = $this->folder('editorial');
        $entityManager->persist($watcher);
        $entityManager->persist($article);
        $entityManager->flush();

        $this->signIn('TEAM_editorial_contributor');
        $this->runner()->start($article, 'article_review');
        $this->signIn('TEAM_editorial_administrator');
        $this->runner()->complete($article, 'review', ['approved' => true]);
        $entityManager->flush();

        $notifications = self::getContainer()->get(NotificationRepository::class);
        $this->assertSame(1, $notifications->countUnread(Member::class, (string) $watcher->getId()));

        $history = self::getContainer()->get(ActivityRepository::class)->paginate(new ActivitySubject(Folder::class, (string) $article->getId()), 'bpmn', 1, 20);
        $types = array_map(static fn ($entry) => $entry->getType(), $history['entries']);
        sort($types);
        $this->assertSame(['bpmn.start', 'bpmn.task'], $types);
    }
}
