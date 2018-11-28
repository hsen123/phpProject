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
 *         "path"="/faq/category/{id}/update",
 *         "requirements"={"id"="\d+",
 *         "access_control"="is_granted('ROLE_Admin')"
 *         }
 *       }
 *     },
 *     collectionOperations={}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\FaqCategoryRepository")
 * @ORM\Table(name="faqcategory")
 */
class FaqCategory
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
    protected $name;

    /**
     * @Assert\NotNull()
     *
     * @ORM\Column(type="boolean",unique=false)
     */
    protected $visible;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FaqItem", mappedBy="faqCategory")
     *
     * @Assert\Valid(groups={"web-edit"})
     * @Groups({"read_child"})
     */
    protected $faqItems;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    public function getFaqItems()
    {
        return $this->faqItems;
    }

    /**
     * @param mixed $faqItems
     *
     * @return FaqCategory
     */
    public function setFaqItems($faqItems)
    {
        $this->faqItems = $faqItems;

        return $this;
    }

    public function addFaqItem($faqItem)
    {
        if ($faqItem instanceof FaqItem) {
            array_push($this->faqItems, $faqItem);
        }

        return $this->faqItems;
    }
}
