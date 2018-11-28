<?php
/**
 * Created by PhpStorm.
 * User: julius
 * Date: 02.07.18
 * Time: 11:38
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ResultAnalysisSnapshot
 * @package App\Entity
 * @ORM\Entity()
 * @ApiResource(
 *     itemOperations={
 *         "get"={ "method"="GET", "access_control"="is_granted('ROLE_USER') or is_granted('ROLE_ADMIN')" },
 *     },
 *     collectionOperations={}
 * )
 * This Table contains snapshots of results that belong to a snapshot analysis.
 */
class ResultAnalysisSnapshot
{

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var AnalysisSnapshot
     * @ORM\ManyToOne(targetEntity="App\Entity\AnalysisSnapshot", inversedBy="resultSnapshots")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $analysisSnapshot;

    /**
     * @var Result
     * @ORM\ManyToOne(targetEntity="App\Entity\Result")
     * @ORM\JoinColumn(name="original_result_id", referencedColumnName="id")
     */
    private $originalResult;

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
     * @param AnalysisSnapshot $analysisSnapshot
     * @param Result $result
     * @return ResultAnalysisSnapshot
     */
    public static function fromResult(AnalysisSnapshot $analysisSnapshot, Result $result)
    {
        $resultSnapshot = new ResultAnalysisSnapshot();
        $resultSnapshot->measurementValue = $result->getMeasurementValue();
        $resultSnapshot->measurementValueMin = $result->getMeasurementValueMin();
        $resultSnapshot->measurementValueMax = $result->getMeasurementValueMax();
        $resultSnapshot->measurementUnit = $result->getMeasurementUnit();
        $resultSnapshot->citationForm = $result->getCitationForm();
        $resultSnapshot->sampleCreationDate = $result->getSampleCreationDate();
        $resultSnapshot->measurementName = $result->getMeasurementName();
        $resultSnapshot->locationLat = $result->getLocationLat();
        $resultSnapshot->locationLng = $result->getLocationLng();
        $resultSnapshot->comment = $result->getComment();
        $resultSnapshot->cardCatalogNumber = $result->getCardCatalogNumber();
        $resultSnapshot->cardLotNumber = $result->getCardLotNumber();
        $resultSnapshot->testStripCatalogNumber = $result->getTestStripCatalogNumber();
        $resultSnapshot->testStripLotNumber = $result->getTestStripLotNumber();
        $resultSnapshot->phoneName = $result->getPhoneName();
        $resultSnapshot->phoneOperatingSystem = $result->getPhoneOperatingSystem();
        $resultSnapshot->phoneCamera = $result->getPhoneCamera();
        $resultSnapshot->invalidSampleMessageCode = $result->getinvalidSampleMessageCode();
        $resultSnapshot->discardedResult = $result->getDiscardedResult();
        $resultSnapshot->updatedAt = $result->getUpdatedAt();
        $resultSnapshot->visibleMeasurementId = $result->getVisibleMeasurementId();

        $resultSnapshot
            ->setAnalysisSnapshot($analysisSnapshot)
            ->setOriginalResult($result);
        return $resultSnapshot;
    }

    /**
     * @return AnalysisSnapshot
     */
    public function getAnalysisSnapshot(): AnalysisSnapshot
    {
        return $this->analysisSnapshot;
    }

    /**
     * @param AnalysisSnapshot $analysisSnapshot
     * @return ResultAnalysisSnapshot
     */
    public function setAnalysisSnapshot(AnalysisSnapshot $analysisSnapshot): ResultAnalysisSnapshot
    {
        $this->analysisSnapshot = $analysisSnapshot;
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
     * @return ResultAnalysisSnapshot
     */
    public function setOriginalResult(Result $originalResult): ResultAnalysisSnapshot
    {
        $this->originalResult = $originalResult;
        return $this;
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
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
     * @return ResultAnalysisSnapshot
     */
    public function setVisibleMeasurementId($visibleMeasurementId)
    {
        $this->visibleMeasurementId = $visibleMeasurementId;
        return $this;
    }


    public function getTitle()
    {
        return !!$this->getMeasurementName() ?
            $this->getMeasurementName() :
            $this->getVisibleMeasurementId();
    }

}
