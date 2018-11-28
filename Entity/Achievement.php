<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AchievementRepository")
 */
class Achievement
{
    const FIVE_IN_A_ROW = 'five_in_a_row';
    const DECUPLICATE = 'decuplicate';
    const DOUBLE_THREAT = 'double_threat';
    const FIVE_DAY_STREAK = 'five_day_streak';
    const TEN_DAY_STREAK = 'ten_day_streak';
    const FIFTEEN_DAY_STREAK = 'fifteen_day_streak';
    const TRAVELER = 'traveler';
    const TRIPLE_DIGITS = 'triple_digits';
    const EARLY_BIRD = 'early_bird';
    const WANDERER = 'wanderer';
    const COLLECTOR = 'collector';
    const CONSISTENCY = 'consistency';

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", unique=false)
     */
    private $description;

    /**
     * @ORM\Column(type="string", unique=false)
     */
    private $name;

    /**
     * @ORM\Column(type="text", unique=false)
     */
    private $eventDescription;

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
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
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
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getEventDescription()
    {
        return $this->eventDescription;
    }

    /**
     * @param mixed $eventDescription
     */
    public function setEventDescription($eventDescription): void
    {
        $this->eventDescription = $eventDescription;
    }
}
