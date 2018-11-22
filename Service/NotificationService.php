<?php

namespace App\Service;

use App\Entity\Broadcast;
use App\Entity\User;
use App\Entity\AutomatedNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class NotificationService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function createWelcomeNotification(User $user)
    {
        $name = '' === $user->getDisplayName() || null === $user->getDisplayName() ? $user->getEmail() : $user->getDisplayName();

        $this->createAutomatedNotification(
            $user,
            $this->translator->trans('notification.welcome.title'),
            $this->translator->trans('notification.welcome.content', ['%displayname%' => $name]));
    }

    public function createLevelUpNotification(User $user)
    {
        $this->createAutomatedNotification(
            $user,
            $this->translator->trans('notification.level.title'),
            $this->translator->trans('notification.level.content', ['%level%' => $user->getActualMeasurementLevel()]));
    }

    public function createPackageCounterNotification(User $user, int $citationForm, int $count)
    {
        $this->createAutomatedNotification(
            $user,
            $this->translator->trans('notification.package.title'),
            $this->translator->trans("notification.package.content.$citationForm", ['%number%' => $count]));
    }

    public function createAchievementNotification(User $user, $message, $parameter = [])
    {
        $this->createAutomatedNotification(
            $user,
            $this->translator->trans("achievement.$message.title"),
            $this->translator->trans("achievement.$message.description", $parameter));
    }

    /**
     * Creates a notification for a user.
     *
     * @param User $user
     * @param $title
     * @param $content
     * @param null $image
     */
    private function createAutomatedNotification(User $user, $title, $content, $image = null)
    {
        $notification = new AutomatedNotification();
        $notification->setTitle($title)->setContent($content)->setImage($image)->setUser($user);
        $user->addAutomatedNotification($notification);
        $this->entityManager->persist($notification);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
