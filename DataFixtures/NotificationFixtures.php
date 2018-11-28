<?php

namespace App\DataFixtures;

use App\Entity\AutomatedNotification;
use App\Entity\Broadcast;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class NotificationFixtures extends Fixture
{
    public static $AUTOMATED_NOTIFICATION_REF = "automated-notification";
    public static $BROADCAST_NOTIFICATION_REF = "broadcast-notification";

    const ONE_DAY_IN_SECONDS = 24 * 60 * 60;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $broadcast = new Broadcast();
        $broadcast->setTitle('Welcome to StripScan');
        $timestamp = (date_create()->getTimestamp() - self::ONE_DAY_IN_SECONDS);
        $broadcast->setCreationDate($timestamp);
        $broadcast->setImage(null);
        $broadcast->setContent('Lorem <span style=\"color: red;\">ipsum</span> dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.');
        $broadcast->setOwner($this->getReference(UserFixtures::$ADMIN_USER_REF));
        $broadcast->setSentDate(time());
        $manager->persist($broadcast);
        $this->setReference(NotificationFixtures::$BROADCAST_NOTIFICATION_REF, $broadcast);

        $automatedNotification = new AutomatedNotification();
        $automatedNotification->setTitle('First Login!');
        $timestamp = (date_create()->getTimestamp() - self::ONE_DAY_IN_SECONDS * 2);
        $automatedNotification->setCreationDate($timestamp);
        $automatedNotification->setContent('Lorem <span style=\"color: red;\">ipsum</span> dolor sit amet, consetetur sadipscing elitr.');
        $automatedNotification->setUser($this->getReference(UserFixtures::$NORMAL_USER_REF));
        $manager->persist($automatedNotification);
        $this->setReference(NotificationFixtures::$AUTOMATED_NOTIFICATION_REF, $automatedNotification);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}

