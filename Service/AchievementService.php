<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 27.04.2018
 * Time: 11:36.
 */

namespace App\Service;

use App\Entity\Achievement;
use App\Entity\User;
use App\Repository\AchievementRepository;
use App\Repository\ResultRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AchievementService
{
    const AMOUNT_CITATION_FORMS = 2;

    /**
     * @var AchievementRepository
     */
    private $achievementRepository;
    /**
     * @var ResultRepository
     */
    private $resultRepository;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var NotificationService
     */
    private $notificationService;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(AchievementRepository $achievementRepository, ResultRepository $resultRepository,
                                EntityManagerInterface $entityManager, NotificationService $notificationService,
                                TranslatorInterface $translator)
    {
        $this->achievementRepository = $achievementRepository;
        $this->resultRepository = $resultRepository;
        $this->em = $entityManager;
        $this->notificationService = $notificationService;
        $this->translator = $translator;
    }

    public function setAchievement(User $user, $lastSyncTime)
    {
        $this->setMeasurementCountAchievements($user);
        $this->setMeasurementStreakAchievements($user, $lastSyncTime);
        $this->setEarlyBirdAchievement($user, $lastSyncTime);
        $this->setCollectorAchievement($user);
        $this->em->flush();
    }

    /**
     * @param User $user
     * @param $id
     */
    public function addAchievementToUser(User $user, $id)
    {
        $achievement = $this->achievementRepository->find($id);

        if (null !== $achievement) {
            $user->addAchievement($achievement);
            $this->notificationService->createAchievementNotification($user, $this->translator->trans($achievement->getId()));
        }
    }

    private function setMeasurementCountAchievements(User $user)
    {
        $countOfMeasurements = $user->getCountOfMeasurements();

        // First 5 measurements
        if ($countOfMeasurements >= 5 && !$this->checkIfUserHoldsAchievement($user, Achievement::FIVE_IN_A_ROW)) {
            $this->addAchievementToUser($user, Achievement::FIVE_IN_A_ROW);
        }

        // After 100 measurements
        if ($countOfMeasurements >= 100 && !$this->checkIfUserHoldsAchievement($user, Achievement::TRIPLE_DIGITS)) {
            $this->addAchievementToUser($user, Achievement::TRIPLE_DIGITS);
        }

        // After user made 10 measurements of pH and No3
        if (!$this->checkIfUserHoldsAchievement($user, Achievement::DOUBLE_THREAT) &&
            $this->resultRepository->checkIfUserMadeTenMeasurementsEach($user)) {
            $this->addAchievementToUser($user, Achievement::DOUBLE_THREAT);
        }
    }

    private function setMeasurementStreakAchievements(User $user, $lastSyncTime)
    {
        if (!$this->checkIfUserHoldsAchievement($user, Achievement::FIVE_DAY_STREAK) &&
            $this->resultRepository->checkConsecutiveMeasurements($user, 5, $lastSyncTime)) {
            $this->addAchievementToUser($user, Achievement::FIVE_DAY_STREAK);
        }

        if (!$this->checkIfUserHoldsAchievement($user, Achievement::TEN_DAY_STREAK) &&
            $this->resultRepository->checkConsecutiveMeasurements($user, 10, $lastSyncTime)) {
            $this->addAchievementToUser($user, Achievement::TEN_DAY_STREAK);
        }

        if (!$this->checkIfUserHoldsAchievement($user, Achievement::FIFTEEN_DAY_STREAK) &&
            $this->resultRepository->checkConsecutiveMeasurements($user, 15, $lastSyncTime)) {
            $this->addAchievementToUser($user, Achievement::FIFTEEN_DAY_STREAK);
        }

        if (!$this->checkIfUserHoldsAchievement($user, Achievement::CONSISTENCY) &&
            $this->resultRepository->checkIfFiveConsecutiveMeasurementsOfOneType($user, $lastSyncTime)) {
            $this->addAchievementToUser($user, Achievement::CONSISTENCY);
        }

        // After 10 consecutive measurements
        if (!$this->checkIfUserHoldsAchievement($user, Achievement::DECUPLICATE) &&
            $this->resultRepository->checkIfUserMadeTenMeasurementsOfTypeConsecutive($user, $lastSyncTime)) {
            $this->addAchievementToUser($user, Achievement::DECUPLICATE);
        }
    }

    private function setEarlyBirdAchievement(User $user, Int $lastSyncTime)
    {
        if (!$this->checkIfUserHoldsAchievement($user, Achievement::EARLY_BIRD) &&
            $this->resultRepository->checkIfMeasurementBeforeSevenAM($user, $lastSyncTime)) {
            $this->addAchievementToUser($user, Achievement::EARLY_BIRD);
        }
    }

    public function setCollectorAchievement(User $user)
    {
        if (!$this->checkIfUserHoldsAchievement($user, Achievement::COLLECTOR) &&
            count($this->resultRepository->checkForCitationForms($user)) == self::AMOUNT_CITATION_FORMS) {
            $this->addAchievementToUser($user, Achievement::COLLECTOR);
        }
    }

    /**
     * Checks if the user already holds the achievement.
     *
     * @param User $user
     * @param $id
     *
     * @return bool
     */
    private function checkIfUserHoldsAchievement(User $user, $id)
    {
        /** @var Achievement $achievement */
        foreach ($user->getAchievements() as $achievement) {
            if ($achievement->getId() === $id) {
                return true;
            }
        }

        return false;
    }
}
