<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
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
 *     collectionOperations={
 *         "get"={ "method"="GET", "access_control"="is_granted('ROLE_USER') or is_granted('ROLE_ADMIN')" },
 *         "post"={
 *              "method"="POST",
 *              "access_control"="is_granted('ROLE_USER') or is_granted('ROLE_ADMIN')",
 *              "swagger_context" = {
 *                 "parameters" = {
 *                     {
 *                          "name" = "name",
 *                          "required" = true,
 *                          "type" = "string",
 *                          "in" = "body",
 *                          "description" = "The Analysis name"
 *                     },
 *                     {
 *                          "name" = "addResultIds",
 *                          "required" = true,
 *                          "type" = "array",
 *                          "in" = "body",
 *                          "description" = "List of integer ids which will be added to the analysis"
 *                     }
 *                 },
 *                 "responses" = {
 *                      "400" = {
 *                          "description" = "result with id :id does not exist <br> Author of result with id :id is not the same author of the analysis <br> Key 'addResultIds' is missing"
 *                      }
 *                 }
 *              }
 *          }
 *     },
 *     itemOperations={
 *         "get"={ "method"="GET", "access_control"="(object.getUser() == user) or is_granted('ROLE_ADMIN')" },
 *         "delete"={ "method"="DELETE", "access_control"="(object.getUser() == user) or is_granted('ROLE_ADMIN')" },
 *         "put"={
 *              "method"="PUT",
 *              "access_control"="(object.getUser() == user) or is_granted('ROLE_ADMIN')",
 *              "swagger_context" = {
 *                 "parameters" = {
 *                     {
 *                          "name" = "name",
 *                          "required" = true,
 *                          "type" = "string",
 *                          "in" = "body",
 *                          "description" = "The Analysis name"
 *                     },
 *                     {
 *                          "name" = "addResultIds",
 *                          "required" = false,
 *                          "type" = "array",
 *                          "in" = "body",
 *                          "description" = "List of integer ids which will be added to the analysis"
 *                     },
 *                     {
 *                          "name" = "removeResultIds",
 *                          "required" = false,
 *                          "type" = "array",
 *                          "in" = "body",
 *                          "description" = "List of integer ids which will be removed from the analysis"
 *                     },
 *                     {
 *                          "name" = "discarded",
 *                          "required" = false,
 *                          "type" = "bool",
 *                          "in" = "body",
 *                          "description" = "if true, discards the analysis, can not be reactivated by a user"
 *                     }
 *                 },
 *                 "responses" = {
 *                      "400" = {
 *                          "description" = "result with id :id does not exist <br> Author of result with id :id is not the same author of the analysis <br> A discarded analysis can not be reactivated"
 *                      }
 *                 }
 *              }
 *         }
 *     }
 * )
 * @Searchable(
 *     {
 *       "name",
 *       "user.displayName"
 *     }
 * )
 * @LikeFilter(
 *     {
 *       "name",
 *       "user.displayName"
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
 * @ORM\Entity(repositoryClass="App\Repository\AnalysisRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="analysis")
 *
 */
class Analysis
{

    public function __construct()
    {
        $this->results = new ArrayCollection();
        $this->discarded = false;
        $this->creationDate = (new \DateTime())->getTimestamp();
        $this->countOfResults = 0;
        $this->countOfResultsWithDiscarded = 0;
        $this->countOfPh = 0;
        $this->countOfPhWithDiscarded = 0;
        $this->countOfNO3 = 0;
        $this->countOfNO3WithDiscarded = 0;
    }

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"analysis_read", "analysis_read_admin"})
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotNull
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Groups({"analysis_read", "analysis_read_admin", "analysis_write", "analysis_write_admin"})
     */
    protected $name;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="analyses")
     * @Groups({"analysis_read", "analysis_read_admin"})
     */
    protected $user;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Result", inversedBy="analyses", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"sampleCreationDate" = "DESC"})
     * @ApiSubresource
     */
    protected $results;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $creationDate;

    /**
     * @var bool
     * @Assert\NotNull
     * @ORM\Column(type="boolean", nullable=false)
     * @Groups({"analysis_write_admin", "analysis_read_admin"})
     */
    protected $discarded;

    /**
     * @var int
     * @Assert\NotNull
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"analysis_read", "analysis_read_admin"})
     */
    protected $countOfResults;

    /**
     * @var int
     * @Assert\NotNull
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"analysis_read_admin"})
     */
    protected $countOfResultsWithDiscarded;

    /**
     * @var int
     * @Assert\NotNull
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"analysis_read", "analysis_read_admin"})
     */
    protected $countOfPh;

    /**
     * @var int
     * @Assert\NotNull
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"analysis_read_admin"})
     */
    protected $countOfPhWithDiscarded;


    /**
     * @var int
     * @Assert\NotNull
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"analysis_read", "analysis_read_admin"})
     */
    protected $countOfNO3;

    /**
     * @var int
     * @Assert\NotNull
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"analysis_read_admin"})
     */
    protected $countOfNO3WithDiscarded;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Entity\SharedAnalysis", mappedBy="analysis", fetch="EXTRA_LAZY", cascade={"remove"})
     */
    protected $dynamicShares;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Entity\AnalysisSnapshot", mappedBy="originalAnalysis", fetch="EXTRA_LAZY", cascade={"remove"})
     */
    protected $snapshotShares;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return Analysis
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return Analysis
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     *
     * @return Analysis
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param mixed $discarded
     *
     * @return Analysis
     */
    public function setDiscarded($discarded)
    {
        $this->discarded = $discarded;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDiscarded()
    {
        return $this->discarded;
    }

    /**
     * !!WARNING!!
     *
     * NEVER MANIPULATE THE RESULTS COLLECTION DIRECTLY.
     * USE addResult(s)/removeResult(s). OTHERWISE COMPUTED PROPERTIES CANNOT BE TRACKED.
     *
     * @Groups({"analysis_read", "analysis_read_admin"})
     * @return ArrayCollection
     */
    public function getResults()
    {
        $results = $this->results;

        unset($results->add);
        unset($results->remove);
        unset($results->clear);
        unset($results->removeElement);
        unset($results->slice);

        return $results;
    }

    /**
     * @param $ts
     * @return Analysis
     */
    public function setCreationDate($ts)
    {
        $this->creationDate = $ts;
        return $this;
    }

    /**
     *
     * @param Collection $results
     * @return Analysis
     */
    public function setResults(Collection $results)
    {
        $this->results = $results;
        return $this;
    }

    /**
     * @Groups({"analysis_read", "analysis_read_admin"})
     * @return int
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @return int
     */
    public function getCountOfResults(): int
    {
        return $this->countOfResults;
    }

    /**
     * @return int
     */
    public function getCountOfResultsWithDiscarded(): int
    {
        return $this->countOfResultsWithDiscarded;
    }

    /**
     * @return array
     * @ApiProperty
     * @Groups({"analysis_read", "analysis_read_admin"})
     */
    public function getParameters(): array
    {
        $params = [];
        if ($this->countOfNO3 > 0) {
            $params[] = 0;
        }
        if ($this->countOfPh > 0) {
            $params[] = 1;
        }
        return $params;
    }

    /**
     * @return array
     * @ApiProperty
     * @Groups({"analysis_read_admin"})
     */
    public function getParametersWithDiscarded(): array
    {
        $params = [];
        if ($this->countOfPhWithDiscarded > 0) {
            $params[] = 0;
        }
        if ($this->countOfPhWithDiscarded > 0) {
            $params[] = 1;
        }
        return $params;
    }


    public function discardResult(Result $result)
    {
        $this->countOfResults--;

        switch ($result->getCitationForm()) {
            case 0:
                $this->countOfNO3--;
                break;
            case 1:
                $this->countOfPh--;
        }
    }

    public function addResult(Result $result)
    {
        $isDiscarded = $result->getDiscardedResult();
        $this->countOfResultsWithDiscarded++;
        if (!$isDiscarded) {
            $this->countOfResults++;
        }
        switch ($result->getCitationForm()) {
            case 0:
                $this->countOfNO3WithDiscarded++;
                if (!$isDiscarded) {
                    $this->countOfNO3++;
                }
                break;
            case 1:
                $this->countOfPhWithDiscarded++;
                if (!$isDiscarded) {
                    $this->countOfPh++;
                }
        }

        $this->results->add($result);
    }

    public function addResults(array $results)
    {
        foreach ($results as $result) {
            $this->addResult($result);
        }
    }

    public function removeResult(Result $result)
    {
        $isDiscarded = $result->getDiscardedResult();
        $this->countOfResultsWithDiscarded--;
        if (!$isDiscarded) {
            $this->countOfResults--;
        }
        switch ($result->getCitationForm()) {
            case 0:
                $this->countOfNO3WithDiscarded--;
                if (!$isDiscarded) {
                    $this->countOfNO3--;
                }
                break;
            case 1:
                $this->countOfPhWithDiscarded--;
                if (!$isDiscarded) {
                    $this->countOfPh--;
                }
        }

        $this->results->removeElement($result);
    }

    public function removeResults(array $results)
    {
        foreach ($results as $result) {
            $this->removeResult($result);
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
     * @param int $countOfPh
     * @return Analysis
     */
    public function setCountOfPh(int $countOfPh): Analysis
    {
        $this->countOfPh = $countOfPh;
        return $this;
    }

    /**
     * @return int
     */
    public function getCountOfPhWithDiscarded(): int
    {
        return $this->countOfPhWithDiscarded;
    }

    /**
     * @param int $countOfPhWithDiscarded
     * @return Analysis
     */
    public function setCountOfPhWithDiscarded(int $countOfPhWithDiscarded): Analysis
    {
        $this->countOfPhWithDiscarded = $countOfPhWithDiscarded;
        return $this;
    }

    /**
     * @return int
     */
    public function getCountOfNO3(): int
    {
        return $this->countOfNO3;
    }

    /**
     * @param int $countOfNO3
     * @return Analysis
     */
    public function setCountOfNO3(int $countOfNO3): Analysis
    {
        $this->countOfNO3 = $countOfNO3;
        return $this;
    }

    /**
     * @return int
     */
    public function getCountOfNO3WithDiscarded(): int
    {
        return $this->countOfNO3WithDiscarded;
    }

    /**
     * @param int $countOfNO3WithDiscarded
     * @return Analysis
     */
    public function setCountOfNO3WithDiscarded(int $countOfNO3WithDiscarded): Analysis
    {
        $this->countOfNO3WithDiscarded = $countOfNO3WithDiscarded;
        return $this;
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
     * @return Analysis
     */
    public function setDynamicShares(Collection $dynamicShares): Analysis
    {
        $this->dynamicShares = $dynamicShares;
        return $this;
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
     * @return Analysis
     */
    public function setSnapshotShares(Collection $snapshotShares): Analysis
    {
        $this->snapshotShares = $snapshotShares;
        return $this;
    }

}
