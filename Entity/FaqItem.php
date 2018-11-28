<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     itemOperations={
 *       "get"={"method"="GET"},
 *       "put"={
 *         "method"="PUT",
 *         "path"="/faq/faqitem/{id}/update",
 *         "requirements"={"id"="\d+",
 *         "access_control"="is_granted('ROLE_Admin')"
 *         }
 *       }
 *     },
 *     collectionOperations={}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\FaqItemRepository")
 * @ORM\Table(name="faqitem")
 */
class FaqItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="string", length=255, unique=false)
     */
    protected $question;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="string", length=4095, unique=false)
     */
    protected $answer;

    /**
     * @Assert\NotNull()
     *
     * @ORM\Column(type="boolean",unique=false)
     */
    protected $visible;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FaqCategory", inversedBy="faqItems")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Assert\Valid(groups={"web-edit"})
     * @Groups({"read_child"})
     */
    protected $faqCategory;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param mixed $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * @return mixed
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param mixed $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }

    /**
     * @return mixed
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @param mixed $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * @return mixed
     */
    public function getFaqCategory()
    {
        return $this->faqCategory;
    }

    /**
     * @param mixed $faqCategory
     */
    public function setFaqCategory($faqCategory)
    {
        $this->faqCategory = $faqCategory;
    }
}
