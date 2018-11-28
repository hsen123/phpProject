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
 * Class SharedAnalysis
 * @package App\Entity
 *
 * Dynamically share a result
 *
 * @ORM\Entity()
 * @ApiResource(
 *     attributes={},
 *     collectionOperations={},
 *     itemOperations={
 *         "get"={ "method"="GET"},
 *         "delete"={ "method"="DELETE", "access_control"="(object.getResult().getCreatedByUser() == user) or is_granted('ROLE_ADMIN')" }
 *     }
 * )
 */
class SharedResult
{
    public function __construct()
    {
        $this->creationDate = (new \DateTime())->getTimestamp();
        $this->emails = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Result", inversedBy="dynamicShares", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="result_id", referencedColumnName="id")
     */
    private $result;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Entity\Email", mappedBy="dynamicResult", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private $emails;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $creationDate;


    public static function createSharedResult(Result $result, array $emails)
    {
        $share = new SharedResult();
        $emailAC = new ArrayCollection();
        foreach ($emails as $e) {
            $email = new Email();
            $email->setEmailAddress($e)->setDynamicResult($share);
            $emailAC->add($email);
        }
        $share->setEmails($emailAC);
        $share->setResult($result);
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
     * @return SharedResult
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     * @return SharedResult
     */
    public function setResult($result)
    {
        $this->result = $result;
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
     * @return SharedResult
     */
    public function setEmails(Collection $emails): SharedResult
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
     * @return SharedResult
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        return $this;
    }

}
