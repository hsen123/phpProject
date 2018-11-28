<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AutomatedNotificationRepository")
 */
class AutomatedNotification
{
    public static $AUTOMATED_NOTIFICATION_TYPE = 'automated';

    public function __construct()
    {
        $this->creationDate = time();
        $this->isRead = false;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read", "write", "user_write", "user_read", "read_child"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="automatedNotifications", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @ApiProperty(attributes={"fetchEager": false})
     * @Groups({"read", "write", "user_write", "user_read"})
     */
    protected $user;

    /**
     * @Assert\NotNull()
     *
     * @ORM\Column(type="boolean",unique=false)
     * @Groups({"read", "write", "user_write", "user_read", "read_child"})
     */
    protected $isRead;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"read", "user_read"})
     */
    private $creationDate;
    /**
     * @Assert\Length(max="255", min="1")
     *
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     * @Groups({"read"})
     */
    protected $title;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Length(max="255")
     *
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     * @Groups({"read"})
     */

    protected $image;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="text", unique=false)
     */
    protected $content;


    /*
     * This is for clients to understand that this entity is an automated notification.
     */
    protected $type = 'automated';

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return AutomatedNotification
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return AutomatedNotification
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isRead()
    {
        return $this->isRead;
    }


    /**
     * @param mixed $isRead
     * @return AutomatedNotification
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreationDate(): int
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     * @return AutomatedNotification
     */
    public function setCreationDate(int $creationDate): AutomatedNotification
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
     * @return AutomatedNotification
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
     * @return AutomatedNotification
     */
    public function setImage($image)
    {
        $this->image = $image;
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
     * @return AutomatedNotification
     */
    public function setContent($content)
    {
        $this->content = $content;
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
