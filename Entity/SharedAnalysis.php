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

/**
 * Class SharedAnalysis
 * @package App\Entity
 *
 *
 * @ApiResource(
 *     attributes={},
 *     collectionOperations={},
 *     itemOperations={
 *         "get"={ "method"="GET"},
 *         "delete"={ "method"="DELETE", "access_control"="(object.getAnalysis().getUser() == user) or is_granted('ROLE_ADMIN')" }
 *     }
 * )
 *
 * Dynamically share an analysis
 * @ORM\Entity(repositoryClass="App\Repository\SharedAnalysisRepository")
 */
class SharedAnalysis
{

    public function __construct()
    {
        $this->emails = new ArrayCollection();
        $this->creationDate = (new \DateTime())->getTimestamp();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var Analysis
     * @ORM\ManyToOne(targetEntity="App\Entity\Analysis", inversedBy="dynamicShares", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="analysis_id", referencedColumnName="id")
     * @ApiSubresource
     */
    private $analysis;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Entity\Email", mappedBy="dynamicAnalysis", fetch="EAGER", cascade={"persist", "remove"})
     */
    private $emails;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $creationDate;

    public static function createSharedAnalysis(Analysis $analysis, array $emails)
    {
        $share = new SharedAnalysis();
        $emailAC = new ArrayCollection();
        foreach ($emails as $e) {
            $email = new Email();
            $email->setEmailAddress($e)->setDynamicAnalysis($share);
            $emailAC->add($email);
        }
        $share->setEmails($emailAC);
        $share->setAnalysis($analysis);
        return $share;
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
     * @return SharedAnalysis
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Analysis
     */
    public function getAnalysis(): Analysis
    {
        return $this->analysis;
    }

    /**
     * @param Analysis $analysis
     * @return SharedAnalysis
     */
    public function setAnalysis(Analysis $analysis): SharedAnalysis
    {
        $this->analysis = $analysis;
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
     * @return SharedAnalysis
     */
    public function setEmails(Collection $emails): SharedAnalysis
    {
        $this->emails = $emails;
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
     * @return SharedAnalysis
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        return $this;
    }

}
