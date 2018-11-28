<?php

namespace App\DataFixtures;

use App\Entity\Result;
use App\Entity\TestStripPackage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TestStripPackageFixtures extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 3; ++$i) {
            $card = new TestStripPackage();
            $card->setUser($this->getReference(UserFixtures::$NORMAL_USER_REF));
            $card->setCitationForm(Result::CITATION_NO3);
            $this->setReference('test-strip-package-no3-' . $i, $card);
            $manager->persist($card);
        }

        for ($i = 1; $i <= 3; ++$i) {
            $card = new TestStripPackage();
            $card->setUser($this->getReference(UserFixtures::$NORMAL_USER_REF));
            $card->setCitationForm(Result::CITATION_PH);
            $this->setReference('test-strip-package-ph-' . $i, $card);
            $manager->persist($card);
        }

        $manager->flush();
    }
}
