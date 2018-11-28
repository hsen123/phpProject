<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Enums\Countries;
use App\Filter\Annotation\ResultListEqualToFilterAnnotation as EqualToFilter;
use App\Filter\Annotation\ResultListGreaterThanEqualsFilterAnnotation as GreaterThanEqualsFilter;
use App\Filter\Annotation\ResultListGreaterThanFilterAnnotation as GreaterThanFilter;
use App\Filter\Annotation\ResultListLessThanEqualsFilterAnnotation as LessThanEqualsFilter;
use App\Filter\Annotation\ResultListLessThanFilterAnnotation as LessThanFilter;
use App\Filter\Annotation\ResultListLikeFilterAnnotation as LikeFilter;
use App\Filter\Annotation\ResultListNotEqualToFilterAnnotation as NotEqualToFilter;
use App\Filter\Annotation\SearchAnnotation as Searchable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *        "normalization_context"={"groups"={"user_read"}},
 *        "denormalization_context"={"groups"={"user_write"}},
 *        "filters"={
 *              "search",
 *              "id_filter",
 *              "like_filter",
 *              "equal_to_filter",
 *              "not_equal_to_filter",
 *              "greater_than_filter",
 *              "greater_than_equals_filter",
 *              "less_than_filter",
 *              "less_than_equals_filter",
 *              "user.order_filter"
 *          },
 *        "order"={"id": "DESC"}
 *     },
 *     itemOperations={
 *       "get"={"method"="GET", "access_control"="object == user || is_granted('ROLE_ADMIN')" },
 *       "put"={"method"="PUT", "access_control"="object == user"},
 *       "delete"={"method"="DELETE", "access_control"="object == user || is_granted('ROLE_ADMIN')" },
 *     },
 *     collectionOperations={
 *       "get"={ "method"="GET", "access_control"="is_granted('ROLE_ADMIN')" },
 *       "post"={"method"="POST", "validation_groups"={"registrationMq"}},
 *     }
 * )
 * @Searchable(
 *     {
 *       "displayName",
 *       "email",
 *       "company",
 *       "segmentWorkgroup"
 *     }
 * )
 * @LikeFilter(
 *     {
 *       "displayName",
 *       "email",
 *       "company",
 *       "companyAdress",
 *       "companyCity",
 *       "segmentWorkgroup"
 *     }
 * )
 * @EqualToFilter(
 *     {
 *       "id",
 *       "email",
 *       "displayName",
 *       "company",
 *       "companyAdress",
 *       "companyCity",
 *       "companyPostalCode",
 *       "companyCountry",
 *       "segment",
 *       "segmentDepartment",
 *       "segmentWorkgroup",
 *       "countOfNO3",
 *       "countOfPh",
 *       "countOfMeasurements"
 *     }
 * )
 * @NotEqualToFilter(
 *     {
 *       "id",
 *       "email",
 *       "displayName",
 *       "company",
 *       "companyAdress",
 *       "companyCity",
 *       "companyPostalCode",
 *       "companyCountry",
 *       "segment",
 *       "segmentDepartment",
 *       "segmentWorkgroup",
 *       "countOfNO3",
 *       "countOfPh",
 *       "countOfMeasurements"
 *     }
 * )
 * @GreaterThanFilter(
 *     {
 *       "segment",
 *       "countOfNO3",
 *       "countOfPh",
 *       "countOfMeasurements"
 *     }
 * )
 * @GreaterThanEqualsFilter(
 *     {
 *       "segment",
 *       "countOfNO3",
 *       "countOfPh",
 *       "countOfMeasurements"
 *     }
 * )
 * @LessThanEqualsFilter(
 *     {
 *       "segment",
 *       "countOfNO3",
 *       "countOfPh",
 *       "countOfMeasurements"
 *     }
 * )
 * @LessThanFilter(
 *     {
 *       "segment",
 *       "countOfNO3",
 *       "countOfPh",
 *       "countOfMeasurements"
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="`user`")
 * @UniqueEntity("email", groups={"registrationMq"}, message="fos_user.email.already_used")
 * @UniqueEntity("displayName", groups={"registrationMq"}, message="fos_user.display_name.already_used")
 * @UniqueEntity("newsletterToken")
 */
class User extends BaseUser
{
    const RANK_COUNT = 4;

    const COMPANY_POSITION_NOT_DEFINED = 0;
    const COMPANY_POSITION_PRIVATE_USE = 1;
    const COMPANY_POSITION_INTERN_TEMP = 2;
    const COMPANY_POSITION_STUDENT = 3;
    const COMPANY_POSITION_PHD_CANDIDATE = 4;
    const COMPANY_POSITION_POSTDOC = 5;
    const COMPANY_POSITION_TECHNICAL_STAFF = 6;
    const COMPANY_POSITION_GROUP_LEADER = 7;
    const COMPANY_POSITION_PROFESSOR = 8;
    const COMPANY_POSITION_DIRECTOR_CEO = 9;
    const COMPANY_POSITION_OTHER = 10;

    const MEASUREMENTS_FOR_LEVEL_2 = 20;
    const MEASUREMENTS_FOR_LEVEL_3 = 40;
    const MEASUREMENTS_FOR_LEVEL_4 = 75;
    const MEASUREMENTS_FOR_LEVEL_5 = 100;
    const MEASUREMENTS_FOR_LEVEL_6 = 150;
    const MEASUREMENTS_FOR_LEVEL_7 = 200;
    const MEASUREMENTS_FOR_LEVEL_8 = 250;
    const MEASUREMENTS_FOR_LEVEL_9 = 300;
    const MEASUREMENTS_FOR_LEVEL_10 = 400;
    const MEASUREMENTS_FOR_LEVEL_11 = 401;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Groups({"user_read", "analysis_read", "analysis_read_admin"})
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\Email(
     *     message = "fos_user.email.invalid",
     *     groups={"registrationMq"}
     * )
     *
     * @Assert\NotBlank(groups={"registrationMq"})
     *
     * @Groups({"user_write", "user_read"})
     */
    protected $email;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     *
     * @Assert\Length(
     *      min = 8,
     *      max = 64,
     *      minMessage = "fos_user.password.min",
     *      maxMessage = "fos_user.password.max",
     *      groups={"registrationMq", "ResetPassword"}
     * )
     * @Assert\Regex(
     *     pattern="/^[A-Za-z0-9!§$%&\(\)=?*+#_\-\.:,;<>\{\}\[\]]{8,64}$/", htmlPattern="^[A-Za-z0-9!§$%&\(\)=?*+#_\-\.:,;<>\{\}\[\]]{8,64}$", groups={"registrationMq", "ResetPassword"}
     * )
     * @Assert\NotBlank(groups={"registrationMq", "ResetPassword"})
     *
     * @Groups({"user_write"})
     */
    protected $plainPassword;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 128,
     *      minMessage = "fos_user.display_name.min",
     *      maxMessage = "fos_user.display_name.max",
     *      groups={"registrationMq"}
     * )
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     *
     * @Groups({"user_write", "user_read", "read_child", "analysis_read", "analysis_read_admin"})
     */
    private $displayName;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 128,
     *      minMessage = "fos_user.first_name.min",
     *      maxMessage = "fos_user.first_name.max",
     *      groups={"registrationMq", "profileUpdate"}
     * )
     * @Assert\Regex(pattern="/\d/", match=false, message="Please use only alphabetic characters for first name", groups={"registrationMq", "profileUpdate"})
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     * @Groups({"user_write", "user_read"})
     */
    private $firstName;

    /**
     * @ORM\OneToMany(targetEntity="AutomatedNotification", mappedBy="user", fetch="EXTRA_LAZY", cascade={"remove"})
     * @ApiProperty(attributes={"fetchEager": false})
     */
    private $automatedNotifications;

    /**
     * Many Users can read Many Broadcasts.
     * @ORM\ManyToMany(targetEntity="Broadcast", inversedBy="users", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="users_read_broadcasts")
     * @ApiProperty(attributes={"fetchEager": false})
     */
    private $broadcastsRead;


    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 2,
     *      max = 128,
     *      minMessage = "fos_user.last_name.min",
     *      maxMessage = "fos_user.last_name.max",
     *      groups={"registrationMq", "profileUpdate"}
     * )
     *
     * @Assert\Regex(pattern="/\d/", match=false, message="Please use only alphabetic characters for last name", groups={"registrationMq", "profileUpdate"})
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     * @Groups({"user_write", "user_read"})
     */
    private $lastName;

    /**
     * @var string
     *
     * @Assert\Length(
     *      min = 3,
     *      max = 512,
     *      minMessage = "fos_user.company.min",
     *      maxMessage = "fos_user.company.max",
     *     groups={"registrationMq"},
     * )
     *
     * @ORM\Column(type="string", length=40, nullable=true)
     *
     * @Groups({"user_write", "user_read", "read_child"})
     */
    private $company;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"user_write", "user_read", "read_child"})
     */
    private $segment;

    /**
     * @var string The user's role
     *
     * @ORM\Column(type="string", length=10)
     */
    private $role;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read", "user_read"})
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastConfirmationMail;

    /**
     * @var JSON Metadata Object that stores all information of the fronend-result-list view
     *
     * @ORM\Column(type="text")
     */
    private $resultListmetaData = '{}';

    /**
     * @var JSON Metadata Object that stores all information of the fronend-admin-analysis view
     *
     * @ORM\Column(type="text")
     */
    private $analysisListMetaData = '{}';

    /**
     * @var JSON Metadata Object that stores all information of the admin-fronend-user-list view
     *
     * @ORM\Column(type="text")
     */
    private $userListMetaData = '{}';


    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"user_write", "user_read"})
     */
    private $companyAdress;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"user_write", "user_read"})
     */
    private $companyCity;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"user_write", "user_read"})
     */
    private $companyPostalCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2, nullable=true)
     * @Groups({"user_write", "user_read"})
     */
    private $companyCountry;

    /**
     * @var string
     *
     * @Assert\Regex(
     *     pattern="/^[0-9,\+,\(,\),\-,\/ ]+$/", match=true, message="Please use only numbers for phone number"
     * )
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"user_write", "user_read"})
     */
    private $companyPhone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"user_write", "user_read"})
     */
    private $segmentDepartment;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"user_write", "user_read"})
     */
    private $segmentWorkgroup;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"user_write", "user_read"})
     */
    private $segmentPosition;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deleteDate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"user_write", "user_read"})
     */
    private $profileImage;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $newsletterToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $newsletterTokenTime;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @Groups({"user_write", "user_read"})
     */
    private $newsletterActive;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read", "user_read"})
     */
    private $newsletterSubscriptionDate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $unsubscribeToken;

    /**
     * @ORM\ManyToMany(targetEntity="Achievement")
     * @ORM\JoinTable(name="user_achievements",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="achievement_id", referencedColumnName="id")}
     *      )
     */
    private $achievements;


    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Analysis", mappedBy="user", fetch="EXTRA_LAZY", cascade={"remove"})
     */
    private $analyses;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Broadcast", mappedBy="owner", fetch="EXTRA_LAZY")
     */
    private $broadcastsCreated;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Result", mappedBy="createdByUser", fetch="EXTRA_LAZY", cascade={"remove"})
     */
    private $results;

    /**
     * @ORM\OneToMany(targetEntity="DeviceEntry", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $loggedInDevices;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"user_read"})
     */
    private $countOfMeasurements = 0;

    /**
     * @var int
     * @Assert\NotNull
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"user_read"})
     */
    protected $countOfPh = 0;

    /**
     * @var int
     * @Assert\NotNull
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"user_read"})
     */
    protected $countOfNO3 = 0;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Entity\TestStripPackage", mappedBy="user", cascade={"remove"})
     */
    protected $activeTestStripPackages;


    public function __construct()
    {
        parent::__construct();

        $this->newsletterActive = false;
        $this->role = 'ROLE_USER';
        $this->created = new \DateTime();
        $this->achievements = new ArrayCollection();
        $this->automatedNotifications = new ArrayCollection();
        $this->broadcastsRead = new ArrayCollection();
        $this->broadcastsCreated = new ArrayCollection();
        $this->analyses = new ArrayCollection();
        $this->loggedInDevices = new ArrayCollection();
        $this->activeTestStripPackages = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getProfileImage(): ?string
    {
        return $this->profileImage;
    }

    /**
     * @param string $profileImage
     *
     * @return User
     */
    public function setProfileImage(string $profileImage): self
    {
        $this->profileImage = $profileImage;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompanyPhone(): ?string
    {
        return $this->companyPhone;
    }

    /**
     * @param string $companyPhone
     *
     * @return User
     */
    public function setCompanyPhone($companyPhone): self
    {
        $this->companyPhone = $companyPhone;

        return $this;
    }

    /**
     * @return string
     */
    public function getAnalysisListMetaData(): string
    {
        return $this->analysisListMetaData;
    }

    /**
     * @param string $analysisListMetaData
     * @return User
     */
    public function setAnalysisListMetaData(string $analysisListMetaData): self
    {
        $this->analysisListMetaData = $analysisListMetaData;

        return $this;
    }

    /**
     * @return string
     */
    public function getResultListmetaData(): string
    {
        return $this->resultListmetaData;
    }

    /**
     * @return \DateTime|null
     */
    public function getNewsletterSubscriptionDate(): ?\DateTime
    {
        return $this->newsletterSubscriptionDate;
    }

    /**
     * @param \DateTime $newsletterSubscriptionDate
     * @return User
     */
    public function setNewsletterSubscriptionDate(?\DateTime $newsletterSubscriptionDate): User
    {
        $this->newsletterSubscriptionDate = $newsletterSubscriptionDate;
        return $this;
    }

    /**
     * @param string $resultListmetaData
     *
     * @return User
     */
    public function setResultListmetaData(string $resultListmetaData): self
    {
        $this->resultListmetaData = $resultListmetaData;

        return $this;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return string[] The user roles
     */
    public function getRoles()
    {
        return [$this->role];
    }

    /**
     * @param string $role
     *
     * @return User
     */
    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->setUsername($email);
    }

    /**
     * @return string
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName): void
    {
        $this->displayName = $displayName;
    }

    /**
     * @return string
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany($company): void
    {
        $this->company = $company;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return Collection
     */
    public function getAnalyses()
    {
        return $this->analyses;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return int
     */
    public function getSegment(): ?int
    {
        return $this->segment;
    }

    /**
     * @param string $segment
     */
    public function setSegment($segment): void
    {
        $this->segment = $segment;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created): void
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getLastConfirmationMail(): ?\DateTime
    {
        return $this->lastConfirmationMail;
    }

    /**
     * @param \DateTime $lastConfirmationMail
     */
    public function setLastConfirmationMail(\DateTime $lastConfirmationMail): void
    {
        $this->lastConfirmationMail = $lastConfirmationMail;
    }

    public function canSendConfirmationMail()
    {
        if (null === $this->getConfirmationToken()) {
            return false;
        }

        if (null === $this->getLastConfirmationMail()) {
            return true;
        }

        return getenv('CONFIRMATION_RESEND_TIME_SECONDS') < (time() - $this->getLastConfirmationMail()->getTimestamp());
    }

    /**
     * @return string
     */
    public function getCompanyAdress(): ?string
    {
        return $this->companyAdress;
    }

    /**
     * @param string $companyAdress
     */
    public function setCompanyAdress($companyAdress): void
    {
        $this->companyAdress = $companyAdress;
    }

    /**
     * @return string
     */
    public function getCompanyCity(): ?string
    {
        return $this->companyCity;
    }

    /**
     * @param string $companyCity
     */
    public function setCompanyCity($companyCity): void
    {
        $this->companyCity = $companyCity;
    }

    /**
     * @return int
     */
    public function getCompanyPostalCode(): ?string
    {
        return $this->companyPostalCode;
    }

    /**
     * @param int $companyPostalCode
     */
    public function setCompanyPostalCode($companyPostalCode): void
    {
        $this->companyPostalCode = $companyPostalCode;
    }

    /**
     * @return string
     */
    public function getCompanyCountry(): ?string
    {
        return $this->companyCountry;
    }

    /**
     * @param string $companyCountry
     */
    public function setCompanyCountry($companyCountry): void
    {
        if (!in_array($companyCountry, Countries::getAllCountryCodes()) && null !== $companyCountry) {
            throw new \InvalidArgumentException('Invalid Position Form');
        }
        $this->companyCountry = $companyCountry;
    }

    /**
     * @return string
     */
    public function getSegmentDepartment(): ?string
    {
        return $this->segmentDepartment;
    }

    /**
     * @param string $segmentDepartment
     */
    public function setSegmentDepartment($segmentDepartment): void
    {
        $this->segmentDepartment = $segmentDepartment;
    }

    /**
     * @return string
     */
    public function getSegmentWorkgroup(): ?string
    {
        return $this->segmentWorkgroup;
    }

    /**
     * @param string $segmentWorkgroup
     */
    public function setSegmentWorkgroup($segmentWorkgroup): void
    {
        $this->segmentWorkgroup = $segmentWorkgroup;
    }

    /**
     * @return int
     */
    public function getSegmentPosition(): ?int
    {
        return $this->segmentPosition;
    }

    /**
     * @param int $segmentPosition
     */
    public function setSegmentPosition($segmentPosition)
    {
        if (!in_array($segmentPosition, [
            self::COMPANY_POSITION_NOT_DEFINED,
            self::COMPANY_POSITION_PRIVATE_USE,
            self::COMPANY_POSITION_INTERN_TEMP,
            self::COMPANY_POSITION_STUDENT,
            self::COMPANY_POSITION_PHD_CANDIDATE,
            self::COMPANY_POSITION_POSTDOC,
            self::COMPANY_POSITION_TECHNICAL_STAFF,
            self::COMPANY_POSITION_GROUP_LEADER,
            self::COMPANY_POSITION_PROFESSOR,
            self::COMPANY_POSITION_DIRECTOR_CEO,
            self::COMPANY_POSITION_OTHER,
        ])) {
            throw new \InvalidArgumentException('Invalid Position Form');
        }
        $this->segmentPosition = $segmentPosition;
    }

    /**
     * @return \DateTime
     */
    public function getDeleteDate(): ?\DateTime
    {
        return $this->deleteDate;
    }

    /**
     * @param \DateTime $deleteDate
     */
    public function setDeleteDate(\DateTime $deleteDate): void
    {
        $this->deleteDate = $deleteDate;
    }

    /**
     * @return string
     */
    public function getNewsletterToken(): ?string
    {
        return $this->newsletterToken;
    }

    /**
     * @param string $newsletterToken
     */
    public function setNewsletterToken(?string $newsletterToken)
    {
        $this->newsletterToken = $newsletterToken;
    }

    /**
     * @return \DateTime
     */
    public function getNewsletterTokenTime(): ?\DateTime
    {
        return $this->newsletterTokenTime;
    }

    /**
     * @param \DateTime $newsletterTokenTime
     */
    public function setNewsletterTokenTime(?\DateTime $newsletterTokenTime)
    {
        $this->newsletterTokenTime = $newsletterTokenTime;
    }

    /**
     * @return bool
     */
    public function isNewsletterActive(): ?bool
    {
        return $this->newsletterActive;
    }

    /**
     * @param bool $newsletterActive
     */
    public function setNewsletterActive(bool $newsletterActive): void
    {
        $this->newsletterActive = $newsletterActive;
    }

    /**
     * @return string
     */
    public function getUnsubscribeToken(): ?string
    {
        return $this->unsubscribeToken;
    }

    /**
     * @param string $unsubscribeToken
     */
    public function setUnsubscribeToken(string $unsubscribeToken)
    {
        $this->unsubscribeToken = $unsubscribeToken;
    }

    /**
     * @return int
     */
    public function getCountOfMeasurements(): ?int
    {
        return $this->countOfMeasurements;
    }

    public function incrementResultCount(Result $result)
    {
        $this->countOfMeasurements++;
        if ($result->getCitationForm() === Result::CITATION_NO3) {
            $this->countOfNO3++;
        } else {
            $this->countOfPh++;
        }
    }

    public function decrementResultCount(Result $result)
    {
        $this->countOfMeasurements--;
        if ($result->getCitationForm() === Result::CITATION_NO3) {
            $this->countOfNO3--;
        } else {
            $this->countOfPh--;
        }
    }

    /**
     * @return int
     */
    public function getCountOfPh(): int
    {
        return $this->countOfPh;
    }

    /**
     * @return int
     */
    public function getCountOfNO3(): int
    {
        return $this->countOfNO3;
    }

    /**
     * @return Collection
     */
    public function getResults(): Collection
    {
        return $this->results;
    }

    /**
     * @Groups({"user_read"})
     */
    public function getMeasurementsTillNextLevel(): int
    {
        if ($this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_2) {
            return self::MEASUREMENTS_FOR_LEVEL_2 - $this->countOfMeasurements;
        } elseif ($this->countOfMeasurements >= self::MEASUREMENTS_FOR_LEVEL_2 && $this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_3) {
            return self::MEASUREMENTS_FOR_LEVEL_3 - $this->countOfMeasurements;
        } elseif ($this->countOfMeasurements >= self::MEASUREMENTS_FOR_LEVEL_3 && $this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_4) {
            return self::MEASUREMENTS_FOR_LEVEL_4 - $this->countOfMeasurements;
        } elseif ($this->countOfMeasurements >= self::MEASUREMENTS_FOR_LEVEL_4 && $this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_5) {
            return self::MEASUREMENTS_FOR_LEVEL_5 - $this->countOfMeasurements;
        } elseif ($this->countOfMeasurements >= self::MEASUREMENTS_FOR_LEVEL_5 && $this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_6) {
            return self::MEASUREMENTS_FOR_LEVEL_6 - $this->countOfMeasurements;
        } elseif ($this->countOfMeasurements >= self::MEASUREMENTS_FOR_LEVEL_6 && $this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_7) {
            return self::MEASUREMENTS_FOR_LEVEL_7 - $this->countOfMeasurements;
        } elseif ($this->countOfMeasurements >= self::MEASUREMENTS_FOR_LEVEL_7 && $this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_8) {
            return self::MEASUREMENTS_FOR_LEVEL_8 - $this->countOfMeasurements;
        } elseif ($this->countOfMeasurements >= self::MEASUREMENTS_FOR_LEVEL_8 && $this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_9) {
            return self::MEASUREMENTS_FOR_LEVEL_9 - $this->countOfMeasurements;
        } elseif ($this->countOfMeasurements >= self::MEASUREMENTS_FOR_LEVEL_9 && $this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_10) {
            return self::MEASUREMENTS_FOR_LEVEL_10 - $this->countOfMeasurements;
        }

        return 0;
    }

    /**
     * @Groups({"user_read"})
     */
    public function getActualMeasurementLevel(): int
    {
        if ($this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_2) {
            return 1;
        } elseif ($this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_3) {
            return 2;
        } elseif ($this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_4) {
            return 3;
        } elseif ($this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_5) {
            return 4;
        } elseif ($this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_6) {
            return 5;
        } elseif ($this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_7) {
            return 6;
        } elseif ($this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_8) {
            return 7;
        } elseif ($this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_9) {
            return 8;
        } elseif ($this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_10) {
            return 9;
        } elseif ($this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_11) {
            return 10;
        } elseif ($this->countOfMeasurements >= self::MEASUREMENTS_FOR_LEVEL_11) {
            return 11;
        }

        return 0;
    }

    /**
     * 0 = Empty, 1 = Bronze, 2 = Silver, 3 = Gold.
     *
     * @Groups({"user_read"})
     */
    public function getActualMeasurementLevelRank(): int
    {
        if ($this->countOfMeasurements >= self::MEASUREMENTS_FOR_LEVEL_2 && $this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_5) {
            return 1;
        } elseif ($this->countOfMeasurements >= self::MEASUREMENTS_FOR_LEVEL_5 && $this->countOfMeasurements < self::MEASUREMENTS_FOR_LEVEL_10) {
            return 2;
        } elseif ($this->countOfMeasurements >= self::MEASUREMENTS_FOR_LEVEL_10) {
            return 3;
        }

        return 0;
    }

    /**
     * @return mixed
     */
    public function getAchievements()
    {
        return $this->achievements;
    }

    /**
     * @param $achievement
     * @return mixed
     */
    public function addAchievement($achievement)
    {
        return $this->achievements->add($achievement);
    }

    /**
     * @param Collection $loggedInDevices
     * @return User
     */
    public function setLoggedInDevices(Collection $loggedInDevices): User
    {
        $this->loggedInDevices = $loggedInDevices;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getLoggedInDevices(): Collection
    {
        return $this->loggedInDevices;
    }

    public function isAccountUnconfirmedAndConfirmationTimeFrameExpired()
    {
        return null !== $this->getConfirmationToken() && time() - $this->getCreated()->getTimestamp() > 86400;
    }

    /**
     * @return ArrayCollection
     */
    public function getAutomatedNotifications()
    {
        return $this->automatedNotifications;
    }

    /**
     * @param mixed $automatedNotifications
     * @return User
     */
    public function setAutomatedNotifications($automatedNotifications)
    {
        $this->automatedNotifications = $automatedNotifications;
        return $this;
    }

    public function addAutomatedNotification(AutomatedNotification $automatedNotification)
    {
        $automatedNotification->setUser($this);
        $this->automatedNotifications->add($automatedNotification);
    }

    /**
     * @return ArrayCollection
     */
    public function getBroadcastsRead()
    {
        return $this->broadcastsRead;
    }

    /**
     * @param mixed $broadcastsRead
     * @return User
     */
    public function addBroadcast($broadcastsRead)
    {
        $this->broadcastsRead->add($broadcastsRead);
        return $this;
    }

    /**
     * @return Collection
     */
    public function getBroadcastsCreated(): Collection
    {
        return $this->broadcastsCreated;
    }

    /**
     * @param Collection $broadcastsCreated
     * @return string
     */
    public function setBroadcastsCreated(Collection $broadcastsCreated): string
    {
        $this->broadcastsCreated = $broadcastsCreated;
        return $this;
    }

    /**
     * @param Collection $activeTestStripPackages
     * @return User
     */
    public function setActiveTestStripPackages(Collection $activeTestStripPackages): User
    {
        $this->activeTestStripPackages = $activeTestStripPackages;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getActiveTestStripPackages(): Collection
    {
        return $this->activeTestStripPackages;
    }

    /**
     * @return string
     */
    public function getUserListMetaData(): string
    {
        return $this->userListMetaData;
    }

    /**
     * @param string $userListMetaData
     * @return User
     */
    public function setUserListMetaData(string $userListMetaData): self
    {
        $this->userListMetaData = $userListMetaData;
        return $this;
    }
}
