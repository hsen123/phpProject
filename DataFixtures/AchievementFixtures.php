<?php

namespace App\DataFixtures;

use App\Entity\Achievement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AchievementFixtures extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $achievement1 = new Achievement();
        $achievement1->setId(Achievement::FIVE_IN_A_ROW);
        $achievement1->setName('achievement.five_in_a_row.title');
        $achievement1->setDescription('achievement.five_in_a_row.description');
        $achievement1->setEventDescription('achievement.five_in_a_row.event_description');
        $this->setReference('achievement1', $achievement1);
        $manager->persist($achievement1);

        $achievement2 = new Achievement();
        $achievement2->setId(Achievement::DECUPLICATE);
        $achievement2->setName('achievement.decuplicate.title');
        $achievement2->setDescription('achievement.decuplicate.description');
        $achievement2->setEventDescription('achievement.decuplicate.event_description');
        $this->setReference('achievement2', $achievement2);
        $manager->persist($achievement2);

        $achievement3 = new Achievement();
        $achievement3->setId(Achievement::DOUBLE_THREAT);
        $achievement3->setName('achievement.double_threat.title');
        $achievement3->setDescription('achievement.double_threat.description');
        $achievement3->setEventDescription('achievement.double_threat.event_description');
        $this->setReference('achievement3', $achievement3);
        $manager->persist($achievement3);

        $achievement4 = new Achievement();
        $achievement4->setId(Achievement::FIVE_DAY_STREAK);
        $achievement4->setName('achievement.five_day_streak.title');
        $achievement4->setDescription('achievement.five_day_streak.description');
        $achievement4->setEventDescription('achievement.five_day_streak.event_description');
        $this->setReference('achievement4', $achievement4);
        $manager->persist($achievement4);

        $achievement5 = new Achievement();
        $achievement5->setId(Achievement::TEN_DAY_STREAK);
        $achievement5->setName('achievement.ten_day_streak.title');
        $achievement5->setDescription('achievement.ten_day_streak.description');
        $achievement5->setEventDescription('achievement.ten_day_streak.event_description');
        $this->setReference('achievement5', $achievement5);
        $manager->persist($achievement5);

        $achievement6 = new Achievement();
        $achievement6->setId(Achievement::FIFTEEN_DAY_STREAK);
        $achievement6->setName('achievement.fifteen_day_streak.title');
        $achievement6->setDescription('achievement.fifteen_day_streak.description');
        $achievement6->setEventDescription('achievement.fifteen_day_streak.event_description');
        $this->setReference('achievement6', $achievement6);
        $manager->persist($achievement6);

        $achievement8 = new Achievement();
        $achievement8->setId(Achievement::TRIPLE_DIGITS);
        $achievement8->setName('achievement.triple_digits.title');
        $achievement8->setDescription('achievement.triple_digits.description');
        $achievement8->setEventDescription('achievement.triple_digits.event_description');
        $this->setReference('achievement8', $achievement8);
        $manager->persist($achievement8);

        $achievement9 = new Achievement();
        $achievement9->setId(Achievement::EARLY_BIRD);
        $achievement9->setName('achievement.early_bird.title');
        $achievement9->setDescription('achievement.early_bird.description');
        $achievement9->setEventDescription('achievement.early_bird.event_description');
        $this->setReference('achievement9', $achievement9);
        $manager->persist($achievement9);

        $achievement11 = new Achievement();
        $achievement11->setId(Achievement::COLLECTOR);
        $achievement11->setName('achievement.collector.title');
        $achievement11->setDescription('achievement.collector.description');
        $achievement11->setEventDescription('achievement.collector.event_description');
        $this->setReference('achievement11', $achievement11);
        $manager->persist($achievement11);

        $achievement12 = new Achievement();
        $achievement12->setId(Achievement::CONSISTENCY);
        $achievement12->setName('achievement.consistency.title');
        $achievement12->setDescription('achievement.consistency.description');
        $achievement12->setEventDescription('achievement.consistency.event_description');
        $this->setReference('achievement12', $achievement12);
        $manager->persist($achievement12);

        $manager->flush();
    }
}
