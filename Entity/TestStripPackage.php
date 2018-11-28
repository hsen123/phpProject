<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Exception\TestStripEmptyException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *        "access_control"="is_granted('ROLE_USER') or is_granted('ROLE_ADMIN')",
 *        "normalization_context"={"groups"={"read", "read_child"}},
 *        "order"={"creationDate": "DESC"},
 *     },
 *     collectionOperations={
 *         "get"={ "method"="GET" },
 *         "post"={ "method"="POST", "access_control"="is_granted('ROLE_USER') or is_granted('ROLE_ADMIN')" }
 *     },
 *     itemOperations={
 *         "get"={ "method"="GET", "access_control"="(object.getUser() == user) or is_granted('ROLE_ADMIN')" },
 *     },
 * )
 * @ORM\Entity(repositoryClass="App\Repository\TestStripPackageRepository")
 */
class TestStripPackage
{
    const NOTIFICATION_THRESHOLD = 50;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Range(
     *      min = 0,
     *      max = 250,
     * )
     *
     * @ORM\Column(type="integer", unique=false, nullable=false)
     * @Groups({"read_child"})
     */
    private $amountOfTestStripsLeft = 100;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Range(
     *      min = 0,
     *      max = 250,
     * )
     *
     * @ORM\Column(type="integer", unique=false, nullable=false)
     * @Groups({"read_child"})
     */
    private $startAmount = 100;

    /**
     *
     * @ORM\Column(type="text", unique=false, nullable=true)
     * @Groups({"read_child"})
     */
    private $batchNumber;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="integer",unique=false)
     * @Groups({"read"})
     */
    private $citationForm;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="activeTestStripPackages")
     */
    private $user;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Entity\Result", mappedBy="testStripPackage", fetch="EXTRA_LAZY", cascade={"remove"})
     * @ORM\OrderBy({"sampleCreationDate" = "ASC"})
     * @Groups({"read"})
     */
    private $results;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private $creationDate;

    public function __construct()
    {
        $this->creationDate = time();
        $this->results = new ArrayCollection();
        $this->setAmountOfTestStripsLeft($this->getStartAmount());
    }

    public static function fromPrevious(TestStripPackage $prevPackage) {
        $testStripPackage = new TestStripPackage();
        $testStripPackage->setUser($prevPackage->getUser());
        $testStripPackage->setBatchNumber($prevPackage->getBatchNumber());
        $testStripPackage->setCitationForm($prevPackage->getCitationForm());
        return $testStripPackage;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAmountOfTestStripsLeft()
    {
        return $this->amountOfTestStripsLeft;
    }

    /**
     * @param mixed $amountOfTestStripsLeft
     *
     * @return TestStripPackage
     */
    public function setAmountOfTestStripsLeft($amountOfTestStripsLeft)
    {
        $this->amountOfTestStripsLeft = $amountOfTestStripsLeft;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBatchNumber()
    {
        return $this->batchNumber;
    }

    /**
     * @param string|null $batchNumber
     * @return TestStripPackage
     */
    public function setBatchNumber($batchNumber)
    {
        $this->batchNumber = $batchNumber;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    /**
     * @param int $citationForm
     * @return TestStripPackage
     */
    public function setCitationForm($citationForm)
    {
        $this->citationForm = $citationForm;
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
     * @param Collection $results
     * @return TestStripPackage
     */
    public function setResults(Collection $results): TestStripPackage
    {
        $this->results = $results;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getResults(): Collection
    {
        return $this->results;
    }

    /**
     * @throws TestStripEmptyException
     */
    public function decrementAmountOfTestStripsLeft() {
        $amountOfTestStripsLeft = $this->getAmountOfTestStripsLeft() - 1;
        if ($amountOfTestStripsLeft < 0) {
            throw new TestStripEmptyException();
        }
        $this->setAmountOfTestStripsLeft($amountOfTestStripsLeft);
    }

    /**
     * @return mixed
     */
    public function getStartAmount()
    {
        return $this->startAmount;
    }

    /**
     * @param int $startAmount
     * @return TestStripPackage
     */
    public function setStartAmount(int $startAmount): TestStripPackage
    {
        $this->startAmount = $startAmount;

        return $this;
    }
}
