<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.02.2018
 * Time: 10:52.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="sessions")
 */
class Session
{
    /**
     * @ORM\Id
     * @ORM\Column(name="sess_id", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=1,max=128)
     */
    protected $id;

    /**
     * @ORM\Column(name="sess_data", type="blob")
     */
    protected $data;

    /**
     * @ORM\Column(name="sess_time", type="integer", length=11)
     */
    protected $time;

    /**
     * @ORM\Column(name="sess_lifetime", type="integer", length=11, nullable=false)
     */
    protected $lifetime;

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
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time): void
    {
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * @param mixed $lifetime
     */
    public function setLifetime($lifetime): void
    {
        $this->lifetime = $lifetime;
    }
}
