<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 14.03.2018
 * Time: 11:37.
 */

namespace App\Service;

use App\Entity\Result;
use App\Entity\TestStripPackage;
use App\Entity\User;
use App\Exception\TestStripEmptyException;
use App\Repository\AchievementRepository;
use App\Repository\TestStripPackageRepository;

class SynchronizationService
{
    private $notificationService;
    private $achievementRepository;
    /**
     * @var TestStripPackageRepositoryheade
     */
    private $testStripPackageRepository;

    public function __construct(NotificationService $notificationService, AchievementRepository $achievementRepository, TestStripPackageRepository $testStripPackageRepository)
    {
        $this->notificationService = $notificationService;
        $this->achievementRepository = $achievementRepository;
        $this->testStripPackageRepository = $testStripPackageRepository;
    }

    /**
     * Adds data to an attribute from the synchronization input.
     *
     * @param Result $result
     * @param User $user
     * @param $obj
     * @param string|null $imageName
     */
    public function setResult(Result $result, User $user, $obj, $imageName = null)
    {
        $result->setLocationLng($obj->locationLng);
        $result->setLocationLat($obj->locationLat);
        $result->setMeasurementName($obj->measurementName);
        $result->setMeasurementUnit($obj->measurementUnit);
        $result->setSampleCreationDate($obj->sampleCreationDate);
        $result->setMeasurementValue($obj->measurementValue);
        $result->setCardCatalogNumber($obj->cardCatalogNumber);
        $result->setCitationForm($obj->citationForm);
        $result->setCardLotNumber($obj->cardLotNumber);
        $result->setUpdatedAt($obj->updatedAt);
        $result->setDiscardedResult($obj->discardedResult);
        $result->setMeasurementValueMin($obj->measurementValueMin);
        $result->setMeasurementValueMax($obj->measurementValueMax);
        $result->setVisibleMeasurementId($obj->visibleMeasurementId);
        $result->setInvalidSampleMessageCode($obj->invalidSampleMessageCode);
        $result->setPhoneCamera($obj->phoneCamera);
        $result->setPhoneOperatingSystem($obj->phoneOperatingSystem);
        $result->setPhoneName($obj->phoneName);
        $result->setComment($obj->comment);
        $result->setTestStripCatalogNumber($obj->testStripCatalogNumber);
        $result->setTestStripLotNumber($obj->testStripLotNumber);
        $result->setCreatedByUser($user);

        if (null !== $imageName) {
            $result->setSampleImage($imageName);
        }
    }

    /**
     * Gets the active TestStripPackage for a user and the received citationForm and decrements the package counter.
     * Creates a new package if no previous one was found or is empty.
     *
     * @param User $user
     * @param int $citationForm
     * @return TestStripPackage
     */
    public function handleTestStripPackage(User $user, int $citationForm)
    {
        /** @var TestStripPackage $testStripPackage */
        $testStripPackage = $this->testStripPackageRepository->findForUserAndCitation($user, $citationForm);

        if ($testStripPackage) {
            try {
                $testStripPackage->decrementAmountOfTestStripsLeft();
                $threshold = TestStripPackage::NOTIFICATION_THRESHOLD;
                if ($testStripPackage->getAmountOfTestStripsLeft() ===  $threshold) {
                    $citationForm = $testStripPackage->getCitationForm();
                    $this->notificationService->createPackageCounterNotification($user, $citationForm, $threshold);
                }
            } catch (TestStripEmptyException $e) {
                $testStripPackage = TestStripPackage::fromPrevious($testStripPackage);
            }
        } else {
            $testStripPackage = new TestStripPackage();
            $testStripPackage->setCitationForm($citationForm);
            $testStripPackage->setUser($user);

            try {
                $testStripPackage->decrementAmountOfTestStripsLeft();
            } catch (TestStripEmptyException $e) {
                // ignore decrement exception; It is a new TestPackage with amount >= 1
            }
        }

        return $testStripPackage;
    }

    /**
     * Checks if the input has the right format.
     *
     * @param $obj
     *
     * @return bool
     */
    public function checkInput($obj)
    {
        if (!isset($obj->id) && !key_exists('id', $obj)) {
            return false;
        }

        if (!isset($obj->locationLng) && !key_exists('locationLng', $obj)) {
            return false;
        }

        if (!isset($obj->locationLat) && !key_exists('locationLat', $obj)) {
            return false;
        }

        if (!isset($obj->measurementName) && !key_exists('measurementName', $obj)) {
            return false;
        }

        if (!isset($obj->measurementUnit) && !key_exists('measurementUnit', $obj)) {
            return false;
        }

        if (!isset($obj->sampleCreationDate) && !key_exists('sampleCreationDate', $obj)) {
            return false;
        }

        if (!isset($obj->measurementValue) && !key_exists('measurementValue', $obj)) {
            return false;
        }

        if (!isset($obj->sampleImage) && !key_exists('sampleImage', $obj)) {
            return false;
        }

        if (!isset($obj->cardCatalogNumber) && !key_exists('cardCatalogNumber', $obj)) {
            return false;
        }

        if (!isset($obj->citationForm) && !key_exists('citationForm', $obj)) {
            return false;
        }

        if (!isset($obj->cardLotNumber) && !key_exists('cardLotNumber', $obj)) {
            return false;
        }

        if (!isset($obj->updatedAt) && !key_exists('updatedAt', $obj)) {
            return false;
        }

        if (!isset($obj->testStripCatalogNumber) && !key_exists('testStripCatalogNumber', $obj)) {
            return false;
        }

        if (!isset($obj->testStripLotNumber) && !key_exists('testStripLotNumber', $obj)) {
            return false;
        }

        if (!isset($obj->discardedResult) && !key_exists('discardedResult', $obj)) {
            return false;
        }

        if (!isset($obj->measurementValueMin) && !key_exists('measurementValueMin', $obj)) {
            return false;
        }

        if (!isset($obj->measurementValueMax) && !key_exists('measurementValueMax', $obj)) {
            return false;
        }

        if (!isset($obj->visibleMeasurementId) && !key_exists('visibleMeasurementId', $obj)) {
            return false;
        }

        if (!isset($obj->invalidSampleMessageCode) && !key_exists('invalidSampleMessageCode', $obj)) {
            return false;
        }

        if (!isset($obj->phoneCamera) && !key_exists('phoneCamera', $obj)) {
            return false;
        }

        if (!isset($obj->phoneOperatingSystem) && !key_exists('phoneOperatingSystem', $obj)) {
            return false;
        }

        if (!isset($obj->phoneName) && !key_exists('phoneName', $obj)) {
            return false;
        }

        if (!isset($obj->comment) && !key_exists('comment', $obj)) {
            return false;
        }

        return true;
    }
}
