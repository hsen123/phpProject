<?php

namespace App\DataFixtures;


use App\Entity\Analysis;
use App\Entity\Result;
use App\Entity\User;
use App\Repository\ResultRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class AnalysisFixtures extends Fixture implements DependentFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {

        $ts = new \DateTime();
        /** @var User $user */
        $user = $manager->getRepository(User::class)->findOneBy(["username" => "dev@incloud.de"]);

        /** @var ResultRepository $repository */
        $repository = $manager->getRepository(Result::class);
        $results = $repository->createQueryBuilder("r")
            ->where("r.createdByUser = :user")
            ->andWhere("r.discardedResult = false")
            ->setParameter("user", $user)
            ->getQuery()
            ->getResult();

        for ($i = 1; $i <= 10; $i++) {
            $analysis = new Analysis();
            /** @var User $user */
            $analysis->setUser($user);
            $analysis->setName("Testanalysis $i");
            $analysis->setCreationDate($ts->modify("+$i day")->getTimestamp());

            $max = sizeof($results);
            $resultCountForAnalysis = rand(12, 50);
            if ($i % 2 === 0) {
                $resultCountForAnalysis = 1;
            } else if ($i === 5) {
                $resultCountForAnalysis = 0;
            }
            for ($j = 1; $j <= $resultCountForAnalysis; $j++) {
                $randIndex = rand(0, $max-1);
                /** @var Result $result */
                $result = $results[$randIndex];
                if ($analysis->getResults()->contains($result)) {
                    $j--;
                } else {
                    $analysis->addResult($result);
                }

            }
            $manager->persist($analysis);
        }


        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class, ResultFixtures::class
        ];
    }
}
