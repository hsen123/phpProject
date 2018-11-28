<?php
/**
 * Created by PhpStorm.
 * User: julius
 * Date: 02.07.18
 * Time: 11:38
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Class ResultSnapshot
 * @package App\Entity
 * @ORM\Entity(repositoryClass="App\Repository\ResultSnapshotRepository")
 *
 * @ApiResource(
 *     attributes={},
 *     itemOperations={
 *         "delete"={ "method"="DELETE", "access_control"="(object.getOriginalResult().getCreatedByUser() == user) or is_granted('ROLE_ADMIN')" }
 *     }
 * )
 * Each entry is a snapshot of one result that is being shared
 */
class ResultSnapshot
{
    public static $DAYS_VALID = 30;

    public function __construct()
    {
        $this->emails = new ArrayCollection();
        $this->snapshotCreationDate = (new \DateTime())->getTimestamp();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $validUntil;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Entity\Email", mappedBy="resultSnapshot", fetch="EAGER", cascade={"persist", "remove"})
     */
    private $emails;

    /**
     * @var Result
     * @ORM\ManyToOne(targetEntity="App\Entity\Result", inversedBy="snapshotShares")
     * @ORM\JoinColumn(name="original_result_id", referencedColumnName="id")
     */
    private $originalResult;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $snapshotCreationDate;


    // The following member variables are copies from Result entity

    /**
     * @ORM\Column(type="float", unique=false, nullable=false)
     */
    protected $measurementValue;

    /**
     * @ORM\Column(type="integer", unique=false, nullable=false)
     */
    protected $measurementValueMin;

    /**
     * @ORM\Column(type="integer", unique=false, nullable=false)
     */
    protected $measurementValueMax;

    /**
     * @ORM\Column(type="string", length=255, unique=false)
     */
    protected $measurementUnit;

    /**
     * @ORM\Column(type="integer",unique=false)
     */
    protected $citationForm;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $sampleCreationDate;

    /**
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     */
    protected $measurementName;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, unique=false, nullable=true)
     */
    protected $locationLat;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, unique=false, nullable=true)
     */
    protected $locationLng;

    /**
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     */
    protected $comment;

    /**
     * @ORM\Column(type="string", length=8, unique=false)
     */
    protected $cardCatalogNumber;

    /**
     * @ORM\Column(type="string", length=8, unique=false)
     */
    protected $cardLotNumber;

    /**
     * @ORM\Column(type="string", length=12, unique=false, nullable=false)
     */
    protected $testStripCatalogNumber;

    /**
     * @ORM\Column(type="string", length=8, unique=false, nullable=true)
     */
    protected $testStripLotNumber;

    /**
     * @ORM\Column(type="string", length=255, unique=false)
     */
    protected $phoneName;

    /**
     * @ORM\Column(type="string", length=255, unique=false)
     */
    protected $phoneOperatingSystem;

    /**
     * @ORM\Column(type="string", length=255, unique=false)
     */
    protected $phoneCamera;

    /**
     * @ORM\Column(type="integer",unique=false)
     */
    protected $invalidSampleMessageCode;

    /**
     * @ORM\Column(type="boolean",unique=false)
     */
    protected $discardedResult;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="string", length=30, unique=false)
     */
    protected $visibleMeasurementId;

    /**
     * @param Result $result
     * @param array $emails
     * @return ResultSnapshot
     */
    public static function makeSnapshot(Result $result, array $emails)
    {
        $snapshot = new ResultSnapshot();
        $snapshot->measurementValue = $result->getMeasurementValue();
        $snapshot->measurementValueMin = $result->getMeasurementValueMin();
        $snapshot->measurementValueMax = $result->getMeasurementValueMax();
        $snapshot->measurementUnit = $result->getMeasurementUnit();
        $snapshot->citationForm = $result->getCitationForm();
        $snapshot->sampleCreationDate = $result->getSampleCreationDate();
        $snapshot->measurementName = $result->getMeasurementName();
        $snapshot->locationLat = $result->getLocationLat();
        $snapshot->locationLng = $result->getLocationLng();
        $snapshot->comment = $result->getComment();
        $snapshot->cardCatalogNumber = $result->getCardCatalogNumber();
        $snapshot->cardLotNumber = $result->getCardLotNumber();
        $snapshot->testStripCatalogNumber = $result->getTestStripCatalogNumber();
        $snapshot->testStripLotNumber = $result->getTestStripLotNumber();
        $snapshot->phoneName = $result->getPhoneName();
        $snapshot->phoneOperatingSystem = $result->getPhoneOperatingSystem();
        $snapshot->phoneCamera = $result->getPhoneCamera();
        $snapshot->invalidSampleMessageCode = $result->getinvalidSampleMessageCode();
        $snapshot->discardedResult = $result->getDiscardedResult();
        $snapshot->updatedAt = $result->getUpdatedAt();
        $snapshot->visibleMeasurementId = $result->getVisibleMeasurementId();

        $now = new \DateTime('now');
        $now->modify('+' . ResultSnapshot::$DAYS_VALID . ' days');
        $snapshot->setValidUntil($now->getTimestamp());
        $emailAC = new ArrayCollection();
        foreach ($emails as $e) {
            $email = new Email();
            $email->setEmailAddress($e)->setResultSnapshot($snapshot);
            $emailAC->add($email);
        }

        $snapshot->setEmails($emailAC);
        $snapshot->setOriginalResult($result);
        return $snapshot;
    }

    /**
     * @return mixed
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * @param mixed $validUntil
     * @return ResultSnapshot
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getEmails(): Collection
    {
        return $this->emails;
    }

    /**
     * @param Collection $emails
     * @return ResultSnapshot
     */
    public function setEmails(Collection $emails): ResultSnapshot
    {
        $this->emails = $emails;
        return $this;
    }

    /**
     * @return Result
     */
    public function getOriginalResult(): Result
    {
        return $this->originalResult;
    }

    /**
     * @param Result $originalResult
     * @return ResultSnapshot
     */
    public function setOriginalResult(Result $originalResult): ResultSnapshot
    {
        $this->originalResult = $originalResult;
        return $this;
    }


    public function getTitle()
    {
        return !!$this->getMeasurementName() ?
            $this->getMeasurementName() :
            $this->getVisibleMeasurementId();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ResultSnapshot
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMeasurementValue()
    {
        return $this->measurementValue;
    }

    /**
     * @param mixed $measurementValue
     * @return ResultSnapshot
     */
    public function setMeasurementValue($measurementValue)
    {
        $this->measurementValue = $measurementValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMeasurementValueMin()
    {
        return $this->measurementValueMin;
    }

    /**
     * @param mixed $measurementValueMin
     * @return ResultSnapshot
     */
    public function setMeasurementValueMin($measurementValueMin)
    {
        $this->measurementValueMin = $measurementValueMin;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMeasurementValueMax()
    {
        return $this->measurementValueMax;
    }

    /**
     * @param mixed $measurementValueMax
     * @return ResultSnapshot
     */
    public function setMeasurementValueMax($measurementValueMax)
    {
        $this->measurementValueMax = $measurementValueMax;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMeasurementUnit()
    {
        return $this->measurementUnit;
    }

    /**
     * @param mixed $measurementUnit
     * @return ResultSnapshot
     */
    public function setMeasurementUnit($measurementUnit)
    {
        $this->measurementUnit = $measurementUnit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCitationForm()
    {
        return $this->citationForm;
    }

    /**
     * @param mixed $citationForm
     * @return ResultSnapshot
     */
    public function setCitationForm($citationForm)
    {
        $this->citationForm = $citationForm;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSampleCreationDate()
    {
        return $this->sampleCreationDate;
    }

    /**
     * @param mixed $sampleCreationDate
     * @return ResultSnapshot
     */
    public function setSampleCreationDate($sampleCreationDate)
    {
        $this->sampleCreationDate = $sampleCreationDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMeasurementName()
    {
        return $this->measurementName;
    }

    /**
     * @param mixed $measurementName
     * @return ResultSnapshot
     */
    public function setMeasurementName($measurementName)
    {
        $this->measurementName = $measurementName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocationLat()
    {
        return $this->locationLat;
    }

    /**
     * @param mixed $locationLat
     * @return ResultSnapshot
     */
    public function setLocationLat($locationLat)
    {
        $this->locationLat = $locationLat;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocationLng()
    {
        return $this->locationLng;
    }

    /**
     * @param mixed $locationLng
     * @return ResultSnapshot
     */
    public function setLocationLng($locationLng)
    {
        $this->locationLng = $locationLng;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     * @return ResultSnapshot
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCardCatalogNumber()
    {
        return $this->cardCatalogNumber;
    }

    /**
     * @param mixed $cardCatalogNumber
     * @return ResultSnapshot
     */
    public function setCardCatalogNumber($cardCatalogNumber)
    {
        $this->cardCatalogNumber = $cardCatalogNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCardLotNumber()
    {
        return $this->cardLotNumber;
    }

    /**
     * @param mixed $cardLotNumber
     * @return ResultSnapshot
     */
    public function setCardLotNumber($cardLotNumber)
    {
        $this->cardLotNumber = $cardLotNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestStripCatalogNumber()
    {
        return $this->testStripCatalogNumber;
    }

    /**
     * @param mixed $testStripCatalogNumber
     * @return ResultSnapshot
     */
    public function setTestStripCatalogNumber($testStripCatalogNumber)
    {
        $this->testStripCatalogNumber = $testStripCatalogNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestStripLotNumber()
    {
        return $this->testStripLotNumber;
    }

    /**
     * @param mixed $testStripLotNumber
     * @return ResultSnapshot
     */
    public function setTestStripLotNumber($testStripLotNumber)
    {
        $this->testStripLotNumber = $testStripLotNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoneName()
    {
        return $this->phoneName;
    }

    /**
     * @param mixed $phoneName
     * @return ResultSnapshot
     */
    public function setPhoneName($phoneName)
    {
        $this->phoneName = $phoneName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoneOperatingSystem()
    {
        return $this->phoneOperatingSystem;
    }

    /**
     * @param mixed $phoneOperatingSystem
     * @return ResultSnapshot
     */
    public function setPhoneOperatingSystem($phoneOperatingSystem)
    {
        $this->phoneOperatingSystem = $phoneOperatingSystem;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoneCamera()
    {
        return $this->phoneCamera;
    }

    /**
     * @param mixed $phoneCamera
     * @return ResultSnapshot
     */
    public function setPhoneCamera($phoneCamera)
    {
        $this->phoneCamera = $phoneCamera;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvalidSampleMessageCode()
    {
        return $this->invalidSampleMessageCode;
    }

    /**
     * @param mixed $invalidSampleMessageCode
     * @return ResultSnapshot
     */
    public function setInvalidSampleMessageCode($invalidSampleMessageCode)
    {
        $this->invalidSampleMessageCode = $invalidSampleMessageCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscardedResult()
    {
        return $this->discardedResult;
    }

    /**
     * @param mixed $discardedResult
     * @return ResultSnapshot
     */
    public function setDiscardedResult($discardedResult)
    {
        $this->discardedResult = $discardedResult;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return ResultSnapshot
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVisibleMeasurementId()
    {
        return $this->visibleMeasurementId;
    }

    /**
     * @param mixed $visibleMeasurementId
     * @return ResultSnapshot
     */
    public function setVisibleMeasurementId($visibleMeasurementId)
    {
        $this->visibleMeasurementId = $visibleMeasurementId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSnapshotCreationDate()
    {
        return $this->snapshotCreationDate;
    }

    /**
     * @param mixed $snapshotCreationDate
     * @return ResultSnapshot
     */
    public function setSnapshotCreationDate($snapshotCreationDate)
    {
        $this->snapshotCreationDate = $snapshotCreationDate;
        return $this;
    }

}
