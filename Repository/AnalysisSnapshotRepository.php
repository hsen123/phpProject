<?php

namespace App\Repository;

use App\Entity\AnalysisSnapshot;
use App\Entity\ResultSnapshot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use PDO;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class AnalysisSnapshotRepository extends ServiceEntityRepository
{
    /**
     * @var EmailRepository
     */
    private $emailRepository;
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RegistryInterface $registry, EmailRepository $emailRepository, RouterInterface $router)
    {
        parent::__construct($registry, AnalysisSnapshot::class);
        $this->emailRepository = $emailRepository;
        $this->router = $router;
    }

    public function findById($id)
    {
        $now = time();
        $qb = $this->createQueryBuilder('a')
            ->where('a.id = :id')
            ->andWhere('a.validUntil > :validUntil')
            ->setParameter('id', $id)
            ->setParameter('validUntil', $now);
        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function getCombinedSnapshotSharesForUserCount($userId)
    {
        try {
            $countSA = $this
                ->createQueryBuilder('ass')
                ->select('count(a.id)')
                ->join('ass.originalAnalysis', 'a', JOIN::WITH, 'a.user = :user')
                ->setParameter('user', $userId)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            $countSA = 0;
        }
        try{
            $countSR = $this
                ->getEntityManager()
                ->getRepository(ResultSnapshot::class)
                ->createQueryBuilder('rs')
                ->select('count(rs.id)')
                ->join('rs.originalResult', 'r', JOIN::WITH, 'r.createdByUser = :user')
                ->setParameter('user', $userId)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            $countSR = 0;
        }

        return $countSA + $countSR;
    }

    public function getCombinedSnapshotSharesForUser($userId, $page = 0, $pageSize = 20)
    {
        $offset = $page * $pageSize;
        $queryString = '
            SELECT * FROM 
            (SELECT rs.id, rs.measurement_name as name, "result" as type, rs.snapshot_creation_date as creationDate, rs.valid_until as validUntil from result_snapshot rs 
                JOIN result as r ON r.id = rs.original_result_id WHERE r.created_by_user_id = :user_id
            ) AS X
            UNION
            (SELECT s.id, s.name as name, "analysis" as type, s.snapshot_creation_date as creationDate, s.valid_until as validUntil from analysis_snapshot s 
                JOIN analysis as a ON a.id = s.original_analysis_id WHERE a.user_id = :user_id
            ) ORDER BY creationDate DESC LIMIT :limit OFFSET :offset';

        $arrayResult = $this->getEntityManager()->getConnection()->fetchAll($queryString, [
            'user_id' => $userId,
            'limit' => $pageSize,
            'offset' => $offset,
        ], [
            'limit' => PDO::PARAM_INT,
            'offset' => PDO::PARAM_INT,
        ]);

        foreach ($arrayResult as $i => $item) {
            switch ($arrayResult[$i]['type']) {
                case 'result':
                    $arrayResult[$i]['emails'] = $this->emailRepository->findBySnapshotResultId($arrayResult[$i]['id']);
                    $arrayResult[$i]['link'] = $this->router->generate(
                        'snapshotSharedResult',
                        ['shareId' => $arrayResult[$i]['id']],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    break;
                case 'analysis':
                    $arrayResult[$i]['emails'] = $this->emailRepository->findBySnapshotAnalysisId($arrayResult[$i]['id']);
                    $arrayResult[$i]['link'] = $this->router->generate(
                        'snapshotSharedAnalysis',
                        ['shareId' => $arrayResult[$i]['id']],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    break;
            }
        }
        return $arrayResult;
    }
}
