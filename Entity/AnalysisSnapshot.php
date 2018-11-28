<?php
/**
 * Created by PhpStorm.
 * User: julius
 * Date: 02.07.18
 * Time: 11:38
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use App\Filter\Annotation\ResultListEqualToFilterAnnotation as EqualToFilter;
use App\Filter\Annotation\ResultListGreaterThanEqualsFilterAnnotation as GreaterThanEqualsFilter;
use App\Filter\Annotation\ResultListGreaterThanFilterAnnotation as GreaterThanFilter;
use App\Filter\Annotation\ResultListLessThanEqualsFilterAnnotation as LessThanEqualsFilter;
use App\Filter\Annotation\ResultListLessThanFilterAnnotation as LessThanFilter;
use App\Filter\Annotation\ResultListLikeFilterAnnotation as LikeFilter;
use App\Filter\Annotation\ResultListNotEqualToFilterAnnotation as NotEqualToFilter;
use App\Filter\Annotation\SearchAnnotation as Searchable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *        "normalization_context"={"groups"={"analysis_read"}},
 *        "denormalization_context"={"groups"={"analysis_write"}},
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
 *              "analysis.name_filter",
 *              "analysis.order_filter",
 *              "analysis.timestamp_range_filter",
 *              "analysis.timestamp_equal_filter",
 *              "analysis.discarded_filter",
 *              "analysis.result_parameter_order_filter",
 *              "analysis.result_parameter_filter"
 *        },
 *        "order"={"creationDate": "DESC"}
 *     },
 *     subresourceOperations = {
 *         "get"={ "method"="GET", "access_control"="(object.getUser() == user) or is_granted('ROLE_ADMIN')" },
 *     },
 *     itemOperations={
 *         "get"={ "method"="GET", "access_control"="is_granted('ROLE_USER')" },
 *         "delete"={ "method"="DELETE", "access_control"="(object.getOriginalAnalysis().getUser() == user) or is_granted('ROLE_ADMIN')" }
 *     },
 *     collectionOperations={}
 * )
 * @Searchable(
 *     {
 *       "name",
 *       "analysis.user.displayName"
 *     }
 * )
 * @LikeFilter(
 *     {
 *       "name",
 *       "analysis.user.displayName"
 *     }
 * )
 * @EqualToFilter(
 *     {
 *       "id",
 *       "name",
 *       "user.displayName",
 *       "creationDate",
 *       "countOfResults"
 *     }
 * )
 * @NotEqualToFilter(
 *     {
 *       "id",
 *       "name",
 *       "user.displayName",
 *       "creationDate",
 *       "countOfResults"
 *     }
 * )
 * @GreaterThanFilter(
 *     {
 *       "creationDate",
 *       "countOfResults"
 *     }
 * )
 * @GreaterThanEqualsFilter(
 *     {
 *       "countOfResults"
 *     }
 * )
 * @LessThanEqualsFilter(
 *     {
 *       "countOfResults"
 *     }
 * )
 * @LessThanFilter(
 *     {
 *       "creationDate",
 *       "countOfResults"
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\AnalysisSnapshotRepository")
 * @ORM\Table()
 */
class AnalysisSnapshot
{

    public function __construct()
    {
        $this->emails = new ArrayCollection();
        $this->resultSnapshots = new ArrayCollection();
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
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Entity\Email", mappedBy="analysisSnapshot", fetch="EAGER", cascade={"persist", "remove"})
     */
    private $emails;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $validUntil;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Analysis", inversedBy="snapshotShares", fetch="EAGER")
     * @ORM\JoinColumn(name="original_analysis_id", referencedColumnName="id")
     */
    private $originalAnalysis;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ResultAnalysisSnapshot", mappedBy="analysisSnapshot", cascade={"persist", "remove"})
     * @ApiSubresource
     */
    private $resultSnapshots;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $snapshotCreationDate;


    // The following member variables are copies from Analysis entity

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $creationDate;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $discarded;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $countOfResults;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $countOfResultsWithDiscarded;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $countOfPh;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $countOfPhWithDiscarded;


    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $countOfNO3;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $countOfNO3WithDiscarded;


    /**
     * @param Analysis $analysis
     * @param array $emails
     * @return AnalysisSnapshot
     */
    public static function makeSnapshot(Analysis $analysis, array $emails)
    {
        $snapshot = new AnalysisSnapshot();
        $snapshot->name = $analysis->getName();
        $snapshot->creationDate = $analysis->getCreationDate();
        $snapshot->discarded = $analysis->getDiscarded();
        $snapshot->countOfResults = $analysis->getCountOfResults();
        $snapshot->countOfResultsWithDiscarded = $analysis->getCountOfResultsWithDiscarded();
        $snapshot->countOfPh = $analysis->getCountOfPh();
        $snapshot->countOfPhWithDiscarded = $analysis->getCountOfPhWithDiscarded();
        $snapshot->countOfNO3 = $analysis->getCountOfNO3();
        $snapshot->countOfNO3WithDiscarded = $analysis->getCountOfNO3();
        $now = new \DateTime('now');
        $now->modify('+'.ResultSnapshot::$DAYS_VALID.' days');
        $snapshot->setValidUntil($now->getTimestamp());
        $emailAC = new ArrayCollection();
        foreach ($emails as $e) {
            $email = new Email();
            $email->setEmailAddress($e)->setAnalysisSnapshot($snapshot);
            $emailAC->add($email);
        }
        $snapshot->setEmails($emailAC);
        $snapshot->setOriginalAnalysis($analysis);
        // also snapshot results, so they don't get modified.
        $resultAC = new ArrayCollection();
        $iterator = $analysis->getResults()->getIterator();
        /** @var Result $result */
        $result = $iterator->current();
        while ($result) {
            if (!$result->getDiscardedResult()) {
                $resultAC->add(ResultAnalysisSnapshot::fromResult($snapshot, $result));
            }
            $iterator->next();
            $result = $iterator->current();
        }
        $snapshot->setResultSnapshots($resultAC);

        return $snapshot;
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
     * @return AnalysisSnapshot
     */
    public function setEmails(Collection $emails): AnalysisSnapshot
    {
        $this->emails = $emails;

        return $this;
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
     * @return AnalysisSnapshot
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalAnalysis()
    {
        return $this->originalAnalysis;
    }

    /**
     * @param mixed $originalAnalysis
     * @return AnalysisSnapshot
     */
    public function setOriginalAnalysis($originalAnalysis)
    {
        $this->originalAnalysis = $originalAnalysis;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getResultSnapshots(): Collection
    {
        return $this->resultSnapshots;
    }

    /**
     * @param Collection $resultSnapshots
     * @return AnalysisSnapshot
     */
    public function setResultSnapshots(Collection $resultSnapshots): AnalysisSnapshot
    {
        $this->resultSnapshots = $resultSnapshots;

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
     * @return AnalysisSnapshot
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return AnalysisSnapshot
     */
    public function setName(string $name): AnalysisSnapshot
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param mixed $creationDate
     * @return AnalysisSnapshot
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscarded()
    {
        return $this->discarded;
    }

    /**
     * @param mixed $discarded
     * @return AnalysisSnapshot
     */
    public function setDiscarded($discarded)
    {
        $this->discarded = $discarded;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountOfResults()
    {
        return $this->countOfResults;
    }

    /**
     * @param mixed $countOfResults
     * @return AnalysisSnapshot
     */
    public function setCountOfResults($countOfResults)
    {
        $this->countOfResults = $countOfResults;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountOfResultsWithDiscarded()
    {
        return $this->countOfResultsWithDiscarded;
    }

    /**
     * @param mixed $countOfResultsWithDiscarded
     * @return AnalysisSnapshot
     */
    public function setCountOfResultsWithDiscarded($countOfResultsWithDiscarded)
    {
        $this->countOfResultsWithDiscarded = $countOfResultsWithDiscarded;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountOfPh()
    {
        return $this->countOfPh;
    }

    /**
     * @param mixed $countOfPh
     * @return AnalysisSnapshot
     */
    public function setCountOfPh($countOfPh)
    {
        $this->countOfPh = $countOfPh;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountOfPhWithDiscarded()
    {
        return $this->countOfPhWithDiscarded;
    }

    /**
     * @param mixed $countOfPhWithDiscarded
     * @return AnalysisSnapshot
     */
    public function setCountOfPhWithDiscarded($countOfPhWithDiscarded)
    {
        $this->countOfPhWithDiscarded = $countOfPhWithDiscarded;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountOfNO3()
    {
        return $this->countOfNO3;
    }

    /**
     * @param mixed $countOfNO3
     * @return AnalysisSnapshot
     */
    public function setCountOfNO3($countOfNO3)
    {
        $this->countOfNO3 = $countOfNO3;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountOfNO3WithDiscarded()
    {
        return $this->countOfNO3WithDiscarded;
    }

    /**
     * @param mixed $countOfNO3WithDiscarded
     * @return AnalysisSnapshot
     */
    public function setCountOfNO3WithDiscarded($countOfNO3WithDiscarded)
    {
        $this->countOfNO3WithDiscarded = $countOfNO3WithDiscarded;

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
     * @return AnalysisSnapshot
     */
    public function setSnapshotCreationDate($snapshotCreationDate)
    {
        $this->snapshotCreationDate = $snapshotCreationDate;

        return $this;
    }
}
