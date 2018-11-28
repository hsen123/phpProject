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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ApiResource(
 *     attributes={
 *        "normalization_context"={"groups"={"read", "read_child"}},
 *        "denormalization_context"={"groups"={"read"}},
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
 *              "broadcast.order_filter",
 *              "broadcast.sent_filter",
 *              "broadcast.sent_order_filter",
 *        },
 *        "order"={"creationDate": "DESC"}
 *     },
 *     collectionOperations={
 *         "get"={ "method"="GET" },
 *     },
 *     itemOperations={
 *         "get"={ "method"="GET", "access_control"="is_granted('ROLE_ADMIN')" },
 *     }
 * )
 * @Searchable(
 *     {
 *       "title",
 *       "owner.displayName"
 *     }
 * )
 * @LikeFilter(
 *     {
 *       "title",
 *       "owner.displayName"
 *     }
 * )
 * @EqualToFilter(
 *     {
 *       "id",
 *       "title",
 *       "views",
 *       "owner.displayName"
 *     }
 * )
 * @NotEqualToFilter(
 *     {
 *       "id",
 *       "title",
 *       "views",
 *       "owner.displayName"
 *     }
 * )
 * @GreaterThanFilter(
 *     {
 *       "views",
 *       "creationDate",
 *       "sentDate"
 *     }
 * )
 * @GreaterThanEqualsFilter(
 *     {
 *       "views",
 *       "creationDate",
 *       "sentDate"
 *     }
 * )
 * @LessThanEqualsFilter(
 *     {
 *       "views",
 *       "creationDate",
 *       "sentDate"
 *     }
 * )
 * @LessThanFilter(
 *     {
 *       "views",
 *       "creationDate",
 *       "sentDate"
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\BroadcastRepository")
 */
class Broadcast
{

    public static $BROADCAST_TYPE = 'broadcast';

    public function __construct()
    {
        $this->creationDate = time();
        $this->users = new ArrayCollection();
        $this->views = 0;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    protected $id;

    /**
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"read"})
     */
    protected $creationDate;

    /**
     * @Assert\Length(max="255", min="1")
     *
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     * @Groups({"read"})
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     * @Groups({"read"})
     */
    protected $image;

    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    protected $sentDate;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="text", unique=false)
     * @Groups({"read"})
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="broadcastsCreated")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     *
     * @Groups({"read_child"})
     */
    private $owner;

    /**
     * Many Broadcasts can be read by Many Users.
     * @ORM\ManyToMany(targetEntity="User", mappedBy="broadcastsRead", cascade={"persist"})
     */
    private $users;

    /**
     * COMPUTED PROPERTY
     *
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"read"})
     */
    protected $views;

    protected $send = false;

    protected $isRead = false;

    /*
     * This is for clients to understand that this entity is a broadcast.
     */
    protected $type = 'broadcast';

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Broadcast
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return Broadcast
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return Broadcast
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     * @return Broadcast
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSentDate()
    {
        return $this->sentDate;
    }

    /**
     * @param mixed $sentDate
     * @return Broadcast
     */
    public function setSentDate($sentDate)
    {
        $this->sentDate = $sentDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     * @return Broadcast
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return int
     */
    public function getViews(): int
    {
        return $this->views;
    }

    /**
     * @return bool
     */
    public function isSend(): bool
    {
        return $this->send;
    }

    /**
     * @param bool $send
     * @return Broadcast
     */
    public function setSend(bool $send): Broadcast
    {
        $this->send = $send;
        return $this;
    }

    public function readBroadcastByUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $user->addBroadcast($this);
            $this->views++;
            $this->users->add($user);
        }
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }


    /**
     * @param User $user
     * @return mixed
     */
    public function hasUserReadBroadcast(User $user)
    {
        return $this->users->contains($user);
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     * @return Broadcast
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->isRead;
    }

    /**
     * @param bool $isRead
     * @return Broadcast
     */
    public function setIsRead(bool $isRead): Broadcast
    {
        $this->isRead = $isRead;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

}
