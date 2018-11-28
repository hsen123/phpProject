<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeviceManagementRepository")
 */
class DeviceEntry
{

    const HEADER_KEY_DEVICE_ID = "X-DEVICE-ID";
    const HEADER_KEY_DEVICE_NAME = "X-DEVICE-NAME";

    const PAYLOAD_KEY_USERNAME = "username";
    const PAYLOAD_KEY_DEVICE_ID = "deviceId";
    const PAYLOAD_KEY_DEVICE_NAME = "deviceName";
    const PAYLOAD_KEY_REFRESH_TOKEN = "refresh_token";
    const PAYLOAD_KEY_ACCESS_TOKEN = "token";

    public function __construct()
    {
        $this->creationDate = (new \DateTime())->getTimestamp();
    }

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="loggedInDevices")
     */
    protected $user;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Id
     * @ORM\Column(type="string", nullable=false)
     */
    protected $deviceId;

    /**
     * @Assert\NotNull()
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $deviceName;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $refreshToken;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $creationDate;

    /**
     * @Assert\NotBlank()
     * @Assert\NotNull()
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default" = 0})
     */
    protected $enabled;

    /**
     * @return mixed
     */
    public function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * @param mixed $deviceId
     *
     * @return DeviceEntry
     */
    public function setDeviceId($deviceId)
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param mixed $title
     *
     * @return DeviceEntry
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

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
     *
     * @return DeviceEntry
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     *
     * @return DeviceEntry
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeviceName()
    {
        return $this->deviceName;
    }

    /**
     * @param $deviceName string
     * @return DeviceEntry
     */
    public function setDeviceName($deviceName)
    {
        $this->deviceName = $deviceName;

        return $this;
    }

    /**
     * @param mixed $user
     * @return DeviceEntry
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

}
