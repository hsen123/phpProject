<?php

namespace App\Repository;

use App\Entity\Result;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMapping;
use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ResultRepository extends ServiceEntityRepository
{
    private $em;

    public function __construct(RegistryInterface $registry, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

        parent::__construct($registry, Result::class);
    }

    public function findByLastSyncTime(User $user, $lastSyncTime)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('CONCAT(r.id, \'\', \'\') as id, r.locationLng, r.locationLat, r.measurementName, r.measurementUnit, 
                            r.sampleCreationDate, r.measurementValue, r.sampleImage, r.cardCatalogNumber, r.testStripCatalogNumber, r.testStripLotNumber, r.citationForm,
                            r.cardLotNumber, r.updatedAt, r.discardedResult, r.measurementValueMin, r.measurementValueMax,
                            r.visibleMeasurementId, r.invalidSampleMessageCode, r.phoneCamera, r.phoneName, 
                            r.phoneOperatingSystem, IDENTITY(r.createdByUser) as createdByUser, r.comment')
            ->where('r.updatedAt >= :updatedAt')
            ->andWhere('r.createdByUser = :user')
            ->orderBy('r.updatedAt', 'ASC')
            ->setParameters(['updatedAt' => $lastSyncTime, 'user' => $user]);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param string $clientId
     * @return mixed
     */
    public function findByClientId(string $clientId, User $user) {
        return $this->findOneBy(["idFromClient" => $clientId, "createdByUser" => $user]);
    }

    public function findDiscardedResultsolderThanXDays($olderThanDays)
    {
        $date = new \DateTime();
        $date = $date->modify('-'.$olderThanDays.' days');

        $qb = $this->createQueryBuilder('r')
            ->where('r.discardedResult = 1')
            ->andWhere('r.updated_at < :threshholdDate')
            ->setParameter('threshholdDate', $date)
            ->getQuery();

        return $qb->getResult();
    }

    private function removeFieldsFromResultSet($results)
    {
        foreach ($results as $key=>$result) {
            unset($results[$key]['id']);
            unset($results[$key]['sampleImage']);
            unset($results[$key]['discardedResult']);
            unset($results[$key]['invalidSampleMessageCode']);
        }

        return $results;
    }

    public function findAllWithoutIdImgNotDiscarded(User $user)
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.createdByUser = :user')
            ->andWhere('r.discardedResult = 0')
            ->setParameters(['user' => $user]);

        $results = $qb->getQuery()->getArrayResult();
        $preparedResults = $this->removeFieldsFromResultSet($results);

        return $preparedResults;
    }

    public function findByIdArray(User $user, $ids)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where($qb->expr()->in('r.id', ':ids'))
            ->join('r.createdByUser', 'u')
            ->andWhere('r.createdByUser = :user')
            ->andWhere('r.discardedResult = 0')
            ->setParameters(['ids' => $ids, 'user' => $user])
            ->getQuery();

        $results = $qb->getQuery()->getArrayResult();
        $preparedResults = $this->removeFieldsFromResultSet($results);

        return $preparedResults;
    }

    public function findAllByIdArray($ids)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->where($qb->expr()->in('r.id', ':ids'))
            ->setParameters(['ids' => $ids])
            ->getQuery();

        $results = $qb->getQuery()->getArrayResult();
        $preparedResults = $this->removeFieldsFromResultSet($results);

        return $preparedResults;
    }

    public function checkIfUserMadeTenMeasurementsOfTypeConsecutive(User $user, $timestamp)
    {
        $datePast = new \DateTime();
        $datePast->setTimestamp($timestamp);

        // Get all timestamps of user
        $qb = $this->createQueryBuilder('r')
            ->select('r.sampleCreationDate, r.citationForm')
            ->where('r.sampleCreationDate >= :from')
            ->andWhere('r.createdByUser = :user')
            ->orderBy('r.sampleCreationDate', 'DESC')
            ->setParameters(['from' => $datePast->getTimestamp(), 'user' => $user]);

        $foundConsecutiveMeasurement = $this->findConsecutiveMeasurement($qb->getQuery()->getArrayResult());

        return $foundConsecutiveMeasurement;
    }

    public function checkIfUserMadeTenMeasurementsEach(User $user)
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('citation_form', 'citationForm');
        $rsm->addScalarResult('count', 'count');

        $query = $this->em->createNativeQuery('
            select sub.citation_form, count(sub.citation_form) as count 
            from (
                select * from result 
                where created_by_user_id = :userId 
                order by sample_creation_date desc 
            ) sub 
            group by sub.citation_form', $rsm);
        $query->setParameter('userId', $user->getId());

        $result = $query->getResult();

        if ($result && $result[0]['count'] >= 10 && $result[1]['count'] >= 10) {
            return true;
        }

        return false;
    }

    public function checkConsecutiveMeasurements(User $user, Int $dayStreak, Int $timestamp)
    {
        $datePast = new \DateTime();
        $datePast->setTimestamp($timestamp);
        $datePast->setTime(0, 0, 0, 0);
        $datePast->modify('-'.$dayStreak.' days');

        // Get all timestamps of user
        $qb = $this->createQueryBuilder('r')
            ->select('r.sampleCreationDate')
            ->where('r.sampleCreationDate >= :from')
            ->andWhere('r.createdByUser = :user')
            ->orderBy('r.sampleCreationDate', 'DESC')
            ->setParameters(['from' => $datePast->getTimestamp(), 'user' => $user]);

        $groupedDates = $this->serializeDates($qb->getQuery()->getArrayResult());
        $foundStreak = $this->findDayStreaks($groupedDates, $dayStreak);

        return $foundStreak;
    }

    public function checkIfMeasurementBeforeSevenAM(User $user, Int $timestamp)
    {
        $datePast = new \DateTime();
        $datePast->setTimestamp($timestamp);

        // Get all timestamps of user
        $qb = $this->createQueryBuilder('r')
            ->select('r.sampleCreationDate')
            ->where('r.sampleCreationDate  >= :from')
            ->andWhere('r.createdByUser = :user')
            ->orderBy('r.sampleCreationDate', 'DESC')
            ->setParameters(['from' => $datePast->getTimestamp(), 'user' => $user]);

        $foundSevenAM = $this->findMeasurementBeforeSevenAM($qb->getQuery()->getArrayResult());

        return $foundSevenAM;
    }

    public function checkIfFiveConsecutiveMeasurementsOfOneType(User $user, Int $timestamp)
    {
        $datePast = new \DateTime();
        $datePast->setTimestamp($timestamp);

        $qb = $this->createQueryBuilder('r')
            ->select('r.sampleCreationDate, r.citationForm, r.measurementValue')
            ->where('r.sampleCreationDate >= :from')
            ->andWhere('r.createdByUser = :user')
            ->orderBy('r.sampleCreationDate', 'DESC')
            ->setParameters(['from' => $datePast->getTimestamp(), 'user' => $user]);

        $foundFiveEqualMeasurements = $this->findFiveMeasurementsEqual($qb->getQuery()->getArrayResult());

        return $foundFiveEqualMeasurements;
    }

    public function getMeasurementCountsWithout(User $user)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.discardedResult = 0')
            ->andWhere('r.createdByUser = :user')
            ->setParameters(['user' => $user]);

        return $qb->getQuery()->getResult();
    }

    private function findConsecutiveMeasurement($results)
    {
        $streakCounter = 1;
        $prevElement = null;

        foreach ($results as $result) {
            if (!$prevElement) {
                $prevElement = $result;
            } elseif ($prevElement['citationForm'] === $result['citationForm']) {
                $streakCounter = $streakCounter + 1;

                if (10 === $streakCounter) {
                    return true;
                }
            } else {
                $streakCounter = 1;
            }

            $prevElement = $result;
        }

        return false;
    }

    private function serializeDates($resultArray)
    {
        $groupedDates = [];

        // Serialize dates and group them
        foreach ($resultArray as $result) {
            $newDate = new \DateTime();
            $newDate->setTimestamp($result['sampleCreationDate']);
            $newDate->setTime(0, 0, 0, 0);

            if (!in_array($newDate, $groupedDates)) {
                array_push($groupedDates, $newDate);
            }
        }

        return $groupedDates;
    }

    private function findDayStreaks($groupedDates, $dayStreak)
    {
        $streakCoutner = 1;
        $prevElement = null;

        foreach ($groupedDates as $groupedDate) {
            if (!$prevElement) {
                $prevElement = $groupedDate;
            } elseif (1 === date_diff($prevElement, $groupedDate)->days) {
                $streakCoutner = $streakCoutner + 1;

                if ($streakCoutner === $dayStreak) {
                    return true;
                }
            } else {
                $streakCoutner = 1;
            }

            $prevElement = $groupedDate;
        }

        return false;
    }

    private function findMeasurementBeforeSevenAM($results)
    {
        foreach ($results as $result) {
            $actualDate = new \DateTime();
            $actualDate->setTimestamp($result['sampleCreationDate']);

            if (intval($actualDate->format('H')) < 7) {
                return true;
            }
        }

        return false;
    }

    private function findFiveMeasurementsEqual($results)
    {
        $streakCounter = 1;
        $prevElement = null;

        foreach ($results as $result) {
            if (!$prevElement) {
                $prevElement = $result;
            } elseif ($prevElement['citationForm'] === $result['citationForm'] &&
                $prevElement['measurementValue'] === $result['measurementValue']) {
                $streakCounter = $streakCounter + 1;

                if (5 === $streakCounter) {
                    return true;
                }
            } else {
                $streakCounter = 1;
            }

            $prevElement = $result;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getCountOfDiscardedResults()
    {
        $qb = $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->andWhere('r.discardedResult = 1');

        return $qb->getQuery()->getScalarResult();
    }

    /**
     * @return array
     */
    public function getCountOfSuccessfulResults() {
        $qb = $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->andWhere('r.discardedResult = 0');

        return $qb->getQuery()->getScalarResult();
    }

    /**
     * @param $user
     * @return array
     */
    public function checkForCitationForms($user)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.citationForm')
            ->andWhere('r.createdByUser = :user')
            ->groupBy('r.citationForm')
            ->setParameters(['user' => $user]);

        $result = $qb->getQuery()->getArrayResult();
        return $result;
    }

    /**
     * @param RouterInterface $router
     * @param int $timestamp
     * @param int $timeZoneOffset
     * @param User $user
     * @return array
     */
    public function getGroupedResultCountForWeek(RouterInterface $router, int $timestamp, int $timeZoneOffset, User $user) {

        // range start: Begin of monday
        // range end: Begin of monday next week
        // so the sql query includes the start range but excludes the end range
        $range = $this->getWeekRange($timestamp);
        $begin = $range["begin"] - $timeZoneOffset;
        $end = $range["end"] - $timeZoneOffset;

        $result["chart"] = $this->getCountOfResultsBetween($begin, $end, $user);
        $result["pie"] = $this->getCountOfResultsPercentage($begin, $end, $user);

        // begin of monday in previous week calculated by (24 hours * 3600 seconds * 7 days)
        $prev = $range["begin"] - (86400 * 7);
        // begin of monday in next week
        $next = $range["end"];

        $result["prev"] = $router->generate("dashboard_activity", ["in" => $prev, "tzo" => $timeZoneOffset / 60]);
        $result["next"] = $router->generate("dashboard_activity", ["in" => $next, "tzo" => $timeZoneOffset / 60]);
        $result["prev_ts"] = $prev;
        $result["next_ts"] = $next;

        return $result;
    }

    /**
     * @param int $begin
     * @param int $end
     * @param User $user
     * @return array
     */
    private function getCountOfResultsPercentage(int $begin, int $end, User $user) {

        $nitrate = Result::CITATION_NO3;
        $ph = Result::CITATION_PH;

        $resultSet = $this->createQueryBuilder("r")
            ->select("r.citationForm as citationForm")
            ->addSelect("COUNT(r) as amount")
            ->where("r.createdByUser = :user")
            ->andWhere("r.discardedResult = false")
            ->andWhere("r.sampleCreationDate >= :begin")
            ->andWhere("r.sampleCreationDate < :end")
            ->groupBy("r.citationForm")
            ->setParameter("user", $user)
            ->setParameter("begin", $begin)
            ->setParameter("end", $end)
            ->getQuery()
            ->getArrayResult();

        $sum = 0;
        foreach ($resultSet as $result) {
            $sum += intval($result["amount"]);
        }
        if ($sum === 0) {
            // avoid division by zero
            return [
                ["citationForm" => $nitrate, "percentage" => 0],
                ["citationForm" => $ph, "percentage" => 0]
            ];
        }

        $sum = doubleval($sum);
        $normalized = [];

        $citationForms = array_map(function($result) {
            return $result["citationForm"];
        }, $resultSet);

        if (false === array_search(Result::CITATION_NO3, $citationForms, true)) {
            $resultSet[] = [ "citationForm" => Result::CITATION_NO3, "amount" => 0 ];
        }

        if (false === array_search(Result::CITATION_PH, $citationForms, true)) {
            $resultSet[] = [ "citationForm" => Result::CITATION_PH, "amount" => 0 ];
        }

        foreach ($resultSet as $result) {
            $citationForm = $result["citationForm"];
            $normalized[] = [ "citationForm" => $citationForm, "percentage" => intval($result["amount"]) / $sum ];
        }

        return $normalized;
    }


    /**
     * @param int $begin timestamp
     * @param int $end timestamp
     * @param User $user
     * @return array
     */
    private function getCountOfResultsBetween(int $begin, int $end, User $user) {

        $qb = $this->createQueryBuilder('r')
            ->select("r.citationForm as citationForm")
            ->addSelect("r.sampleCreationDate as sampleCreationDate")
            ->where("r.createdByUser = :user")
            ->andWhere("r.discardedResult = false")
            ->andWhere("r.sampleCreationDate >= :begin")
            ->andWhere("r.sampleCreationDate < :end")
            ->orderBy("r.sampleCreationDate")
            ->setParameter("user", $user)
            ->setParameter("begin", $begin)
            ->setParameter("end", $end);

        $resultSet = $qb->getQuery()->getArrayResult();
        $normalized = [];

        foreach ($resultSet as $result) {
            $citationForm = intval($result["citationForm"]);
            $normalized[] = ["citationForm" => $citationForm, "sampleCreationDate" => $result["sampleCreationDate"]];
        }

        return $normalized;
    }

    /**
     * @param int $fromTimestamp
     * @return array
     */
    private function getWeekRange(int $fromTimestamp)
    {
        if (date('w', $fromTimestamp) == 1)
            $beginOfWeek = strtotime('Today', $fromTimestamp);
        else
            $beginOfWeek = strtotime('last Monday', $fromTimestamp);

        if (date('w', $fromTimestamp) == 7)
            $endOfWeek = strtotime('Today', $fromTimestamp) + 86400;
        else
            $endOfWeek = strtotime('next Sunday', $fromTimestamp) + 86400;

        return ["begin" => $beginOfWeek, "end" => $endOfWeek];

    }

    /**
     * @param int $resultId
     * @return bool
     */
    public function doesResultBelongToAnyAnalysis(int $resultId)
    {
        $qb = $this->createQueryBuilder("r")->select('count(a.id)')->where("r.id = :resultId")->innerJoin(
            'r.analyses',
            'a',
            'WITH',
            'a.discarded = false'
        )->groupBy("r.id")->setParameter("resultId", $resultId);
        try {
            $a = $qb->getQuery()->getSingleScalarResult();

            return $a !== 0;
        } catch (\Exception $e) {
            return false;
        }
    }

}
