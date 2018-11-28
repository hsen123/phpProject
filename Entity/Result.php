<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Filter\Annotation\ResultListEqualToFilterAnnotation as EqualToFilter;
use App\Filter\Annotation\ResultListGreaterThanEqualsFilterAnnotation as GreaterThanEqualsFilter;
use App\Filter\Annotation\ResultListGreaterThanFilterAnnotation as GreaterThanFilter;
use App\Filter\Annotation\ResultListLessThanEqualsFilterAnnotation as LessThanEqualsFilter;
use App\Filter\Annotation\ResultListLessThanFilterAnnotation as LessThanFilter;
use App\Filter\Annotation\ResultListLikeFilterAnnotation as LikeFilter;
use App\Filter\Annotation\ResultListNotEqualToFilterAnnotation as NotEqualToFilter;
use App\Filter\Annotation\SearchAnnotation as Searchable;
use App\Repository\ResultRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PostLoad;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\HasLifecycleCallbacks()
 * @ApiResource(
 *     attributes={
 *       "filters"={
 *         "id_filter",
 *         "result.search_filter",
 *         "result.order_filter",
 *         "search",
 *         "like_filter",
 *         "equal_to_filter",
 *         "not_equal_to_filter",
 *         "greater_than_filter",
 *         "greater_than_equals_filter",
 *         "less_than_filter",
 *         "less_than_equals_filter",
 *         "result.discarded_filter",
 *         "result.aggregate_filter"
 *       },
 *       "normalization_context"={"groups"={"read", "read_child"}},
 *       "order"={"sampleCreationDate":"DESC"},
 *     },
 *     itemOperations={
 *       "get"={ "method"="GET", "access_control"="(object.getCreatedByUser() == user) or is_granted('ROLE_ADMIN')" },
 *       "put"={ "method"="PUT", "access_control"="(object.getCreatedByUser() == user) or is_granted('ROLE_ADMIN')" },
 *       "delete"={
 *        "method"="DELETE",
 *        "access_control"="(object.getCreatedByUser() == user) or is_granted('ROLE_ADMIN')",
 *        "route_name"="delete_result"
 *       }
 *     },
 *     collectionOperations={
 *       "delete"={
 *         "method"="DELETE",
 *         "route_name"="delete_results"
 *       },
 *       "get"={"method"="GET"}
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ResultRepository")
 * @ORM\Table(name="result",indexes={@ORM\Index(name="search_citation", columns={"citation_form"})})
 * @Searchable(
 *     {
 *       "measurementName",
 *       "measurementValue",
 *       "measurementUnit",
 *       "measurementValueMin",
 *       "measurementValueMax",
 *       "testStripCatalogNumber",
 *       "testStripLotNumber",
 *       "cardCatalogNumber",
 *       "cardLotNumber",
 *       "createdByUser.displayName",
 *       "createdByUser.company",
 *       "phoneCamera",
 *       "phoneName",
 *       "phoneOperatingSystem",
 *       "comment",
 *     }
 *   )
 * @LikeFilter(
 *     {
 *       "measurementName",
 *       "measurementUnit",
 *       "testStripCatalogNumber",
 *       "testStripLotNumber",
 *       "cardCatalogNumber",
 *       "cardLotNumber",
 *       "createdByUser.displayName",
 *       "createdByUser.company",
 *       "phoneName",
 *       "phoneOperatingSystem",
 *       "phoneCamera",
 *       "comment",
 *     }
 * )
 * @EqualToFilter(
 *     {
 *       "measurementName",
 *       "measurementValue",
 *       "measurementUnit",
 *       "measurementValueMin",
 *       "measurementValueMax",
 *       "citationForm",
 *       "testStripCatalogNumber",
 *       "testStripLotNumber",
 *       "createdByUser.displayName",
 *       "createdByUser.company",
 *       "createdByUser.segment",
 *       "cardCatalogNumber",
 *       "cardLotNumber",
 *       "phoneName",
 *       "phoneOperatingSystem",
 *       "phoneCamera",
 *       "comment",
 *     }
 * )
 * @NotEqualToFilter(
 *     {
 *       "measurementName",
 *       "measurementValue",
 *       "measurementUnit",
 *       "measurementValueMin",
 *       "measurementValueMax",
 *       "citationForm",
 *       "testStripCatalogNumber",
 *       "testStripLotNumber",
 *       "cardCatalogNumber",
 *       "cardLotNumber",
 *       "createdByUser.displayName",
 *       "createdByUser.segment",
 *       "createdByUser.company",
 *       "phoneName",
 *       "phoneOperatingSystem",
 *       "phoneCamera",
 *       "comment",
 *     }
 * )
 *
 * @GreaterThanFilter(
 *     {
 *       "measurementValue",
 *       "measurementValueMin",
 *       "measurementValueMax",
 *       "sampleCreationDate"
 *     }
 * )
 *
 * @GreaterThanEqualsFilter(
 *     {
 *       "measurementValue",
 *       "measurementValueMin",
 *       "measurementValueMax"
 *     }
 * )
 *
 * @LessThanEqualsFilter(
 *     {
 *       "measurementValue",
 *       "measurementValueMin",
 *       "measurementValueMax"
 *     }
 * )
 *
 * @LessThanFilter(
 *     {
 *       "measurementValue",
 *       "measurementValueMin",
 *       "measurementValueMax",
 *       "sampleCreationDate"
 *     }
 * )
 */
class Result
{
    const CITATION_NO3 = 0;
    const CITATION_PH = 1;

    const TEST_STRIP_CATALOG_NO3 = '110020';
    const TEST_STRIP_CATALOG_PH = '109535';

    const CARD_CATALOG_NO3 = '103733';
    const CARD_CATALOG_PH = '103736';

    const INVALID_SAMPLE_MSG_0 = 0;
    const INVALID_SAMPLE_MSG_1 = 300;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read", "read_child"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true, length=50)
     */
    protected $idFromClient;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="float", unique=false, nullable=false)
     * @Groups({"read"})
     */
    protected $measurementValue;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="integer", unique=false, nullable=false)
     * @Groups({"read"})
     */
    protected $measurementValueMin;
    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="integer", unique=false, nullable=false)
     * @Groups({"read"})
     */
    protected $measurementValueMax;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="string", length=255, unique=false)
     * @Groups({"read"})
     */
    protected $measurementUnit;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="integer",unique=false)
     * @Groups({"read"})
     */
    protected $citationForm;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"read", "read_child"})
     */
    protected $sampleCreationDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="results")
     * @ORM\JoinColumn(referencedColumnName="id")
     * @Groups({"read_child"})
     */
    protected $createdByUser;

    /**
     * @Assert\Length(max="50", min="3", groups={"web-edit"})
     *
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     * @Groups({"read"})
     */
    protected $measurementName;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, unique=false, nullable=true)
     * @Groups({"read"})
     */
    protected $locationLat;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, unique=false, nullable=true)
     * @Groups({"read"})
     */
    protected $locationLng;

    /**
     * @Assert\Length(max="100", min="3", groups={"web-edit"})
     *
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     * @Groups({"read"})
     */
    protected $comment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TestStripPackage", inversedBy="results", cascade={"persist", "remove"})
     * @ORM\JoinColumn(referencedColumnName="id")
     *
     * @Assert\Valid(groups={"web-edit"})
     */
    protected $testStripPackage;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max="8")
     *
     * @ORM\Column(type="string", length=8, unique=false)
     * @Groups({"read"})
     */
    protected $cardCatalogNumber;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max="8")
     *
     * @ORM\Column(type="string", length=8, unique=false)
     * @Groups({"read"})
     */
    protected $cardLotNumber;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max="12")
     *
     * @ORM\Column(type="string", length=12, unique=false, nullable=false)
     * @Groups({"read"})
     */
    protected $testStripCatalogNumber;

    /**
     * @Assert\Length(max="8", min="8", groups={"web-edit"})
     * @Assert\Regex(
     *     pattern="/^[A-Za-z]{2}[0-9]{6}$/", htmlPattern="^[A-Za-z]{2}[0-9]{6}$", groups={"web-edit"}
     * )
     *
     * @ORM\Column(type="string", length=8, unique=false, nullable=true)
     * @Groups({"read"})
     */
    protected $testStripLotNumber;

    /**
     * @Assert\Length(max="255")
     *
     * @ORM\Column(type="string", length=255, unique=false)
     * @Groups({"read"})
     */
    protected $phoneName;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max="10")
     *
     * @ORM\Column(type="string", length=255, unique=false)
     * @Groups({"read"})
     */
    protected $phoneOperatingSystem;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max="10")
     *
     * @ORM\Column(type="string", length=255, unique=false)
     * @Groups({"read"})
     */
    protected $phoneCamera;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="integer",unique=false)
     * @Groups({"read"})
     */
    protected $invalidSampleMessageCode;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="boolean",unique=false)
     */
    protected $discardedResult;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max="10")
     *
     * @ORM\Column(type="string", length=255, unique=false)
     * @Groups({"read"})
     */
    protected $sampleImage; // maybe just the id with a constant path to all sample images?

    /**
     * @Assert\NotNull()
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"read"})
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="string", length=30, unique=false)
     * @Groups({"read"})
     */
    protected $visibleMeasurementId;


    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="Analysis", mappedBy="results", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="analysis_measurement")
     */
    protected $analyses;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Entity\SharedResult", mappedBy="result", fetch="EXTRA_LAZY", cascade={"remove"})
     */
    protected $dynamicShares;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Entity\ResultSnapshot", mappedBy="originalResult", fetch="EXTRA_LAZY", cascade={"remove"})
     */
    protected $snapshotShares;

    /**
     * @return mixed
     */
    public function getMeasurementValueMin()
    {
        return $this->measurementValueMin;
    }

    /**
     * @param mixed $measurementValueMin
     *
     * @return Result
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
     *
     * @return Result
     */
    public function setMeasurementValueMax($measurementValueMax)
    {
        $this->measurementValueMax = $measurementValueMax;

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
     *
     * @return Result
     */
    public function setCardCatalogNumber($cardCatalogNumber)
    {
        if (!in_array($cardCatalogNumber, [self::CARD_CATALOG_NO3, self::CARD_CATALOG_PH])) {
            throw new \InvalidArgumentException('Invalid Test Strip Category');
        }
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
     *
     * @return Result
     */
    public function setCardLotNumber($cardLotNumber)
    {
        $this->cardLotNumber = $cardLotNumber;

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
     *
     * @return Result
     */
    public function setDiscardedResult($discardedResult)
    {
        $this->discardedResult = $discardedResult;
        $analysis = $this->getAnalyses();
        if ($analysis) {
            /** @var Analysis $a */
            foreach ($analysis->getIterator() as $i => $a) {
                $a->discardResult($this);
            }
        }

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
     *
     * @return Result
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestStripPackage()
    {
        return $this->testStripPackage;
    }

    /**
     * @param mixed $testStripPackage
     */
    public function setTestStripPackage($testStripPackage)
    {
        $this->testStripPackage = $testStripPackage;
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
     *
     * @return Result
     */
    public function setTestStripCatalogNumber($testStripCatalogNumber)
    {
        if (!in_array($testStripCatalogNumber, [self::TEST_STRIP_CATALOG_PH, self::TEST_STRIP_CATALOG_NO3])) {
            throw new \InvalidArgumentException('Invalid Test Strip Category');
        }
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
     *
     * @return Result
     */
    public function setTestStripLotNumber($testStripLotNumber)
    {
        $this->testStripLotNumber = $testStripLotNumber;

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
     * @return float
     */
    public function getMeasurementValue()
    {
        return $this->measurementValue;
    }

    /**
     * @param float $measurementValue
     *
     * @return Result
     */
    public function setMeasurementValue($measurementValue)
    {
        $this->measurementValue = $measurementValue;

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
     *
     * @return Result
     */
    public function setMeasurementUnit($measurementUnit)
    {
        $this->measurementUnit = $measurementUnit;

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
     *
     * @return Result
     */
    public function setSampleCreationDate($sampleCreationDate)
    {
        $this->sampleCreationDate = $sampleCreationDate;

        return $this;
    }

    /**
     * @return User
     */
    public function getCreatedByUser()
    {
        return $this->createdByUser;
    }

    /**
     * @param User $createdByUser
     *
     * @return Result
     */
    public function setCreatedByUser(User $createdByUser)
    {
        $this->createdByUser = $createdByUser;

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
     *
     * @return Result
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
     *
     * @return Result
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
     *
     * @return Result
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
     *
     * @return Result
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

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
     *
     * @return Result
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
     *
     * @return Result
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
     *
     * @return Result
     */
    public function setPhoneCamera($phoneCamera)
    {
        $this->phoneCamera = $phoneCamera;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSampleImage()
    {
        return $this->sampleImage;
    }

    /**
     * @param mixed $sampleImage
     *
     * @return Result
     */
    public function setSampleImage($sampleImage)
    {
        $this->sampleImage = $sampleImage;

        return $this;
    }

    public function getCitationForm()
    {
        return $this->citationForm;
    }

    public function getinvalidSampleMessageCode()
    {
        return $this->invalidSampleMessageCode;
    }

    public function setCitationForm($citationForm)
    {
        if (!in_array($citationForm, [self::CITATION_NO3, self::CITATION_PH])) {
            throw new \InvalidArgumentException('Invalid Citation Form');
        }
        $this->citationForm = $citationForm;
    }

    public function setInvalidSampleMessageCode($invalidSampleMessageCode)
    {
        if (!in_array(
            $invalidSampleMessageCode,
            [
                self::INVALID_SAMPLE_MSG_0,
                self::INVALID_SAMPLE_MSG_1,
            ]
        )) {
            throw new \InvalidArgumentException('Invalid invalid Sample Message');
        }
        $this->invalidSampleMessageCode = $invalidSampleMessageCode;
    }

    /**
     * @return mixed
     */
    public function getVisibleMeasurementId()
    {
        return $this->visibleMeasurementId;
    }

    /**
     * @return Collection
     */
    public function getAnalyses()
    {
        return $this->analyses;
    }

    /**
     * @param mixed $visibleMeasurementId
     */
    public function setVisibleMeasurementId($visibleMeasurementId)
    {
        $this->visibleMeasurementId = $visibleMeasurementId;
    }


    /**
     * @ORM\PostPersist
     * @ORM\PostUpdate
     * @param LifecycleEventArgs $args
     */
    public function setDefaultMeasurementName(LifecycleEventArgs $args)
    {
        try {
            if ('' === $this->getMeasurementName()) {
                if ('' !== $this->getVisibleMeasurementId()) {
                    $this->setMeasurementName($this->getVisibleMeasurementId());
                } else {
                    $this->setMeasurementName($this->getId());
                }
                $this->setUpdatedAt(date_create()->getTimestamp());
                $args->getEntityManager()->persist($this);
                $args->getEntityManager()->flush();
            }
        } catch (\Exception $e) {
        }
    }

    public function getTitle()
    {
        return !!$this->getMeasurementName() ? $this->getMeasurementName() : $this->getVisibleMeasurementId();
    }

    /**
     * @var boolean
     * @Groups({"read"})
     */
    protected $belongsToAnalysis;

    /**
     * @PostLoad()
     * @param LifecycleEventArgs $eventArgs
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        /** @var ResultRepository $resultRepository */
        $resultRepository = $eventArgs->getEntityManager()->getRepository(Result::class);
        $belongsToAnyAnalysis = $resultRepository->doesResultBelongToAnyAnalysis($this->getId());
        $this->setBelongsToAnalysis($belongsToAnyAnalysis);
    }

    /**
     * @return bool
     */
    public function isBelongsToAnalysis(): bool
    {
        return $this->belongsToAnalysis;
    }

    /**
     * @param bool $belongsToAnalysis
     * @return Result
     */
    public function setBelongsToAnalysis(bool $belongsToAnalysis): Result
    {
        $this->belongsToAnalysis = $belongsToAnalysis;

        return $this;
    }


    /**
     * Never use this method except for snapshot creation
     *
     * @return array
     */
    public function getAsArray()
    {
        return get_object_vars($this);
    }

    /**
     * @return Collection
     */
    public function getDynamicShares(): Collection
    {
        return $this->dynamicShares;
    }

    /**
     * @param Collection $dynamicShares
     */
    public function setDynamicShares(Collection $dynamicShares)
    {
        $this->dynamicShares = $dynamicShares;
    }

    /**
     * @return Collection
     */
    public function getSnapshotShares(): Collection
    {
        return $this->snapshotShares;
    }

    /**
     * @param Collection $snapshotShares
     */
    public function setSnapshotShares(Collection $snapshotShares)
    {
        $this->snapshotShares = $snapshotShares;
    }


    public function setClientId(string $clientId)
    {
        $this->idFromClient = $clientId;
    }

}
