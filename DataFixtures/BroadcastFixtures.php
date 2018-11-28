<?php

namespace App\DataFixtures;

use App\Entity\Broadcast;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class BroadcastFixtures extends Fixture implements DependentFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = $this->getReference(UserFixtures::$ADMIN_USER_REF);
        for($i=0;$i<35;$i++){
            $bc = new Broadcast();
            $num = $i+1;
            $bc
                ->setContent("test")
                ->setSend(true)
                ->setSentDate($bc->getCreationDate())
                ->setOwner($user)
                ->setTitle("Test ($num)");
            $manager->persist($bc);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
