<?php

namespace App\Repository;

use App\Entity\SharedAnalysis;
use App\Entity\SharedResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use PDO;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * SharedAnalysisRepository
 */
class SharedAnalysisRepository extends ServiceEntityRepository
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
        parent::__construct($registry, SharedAnalysis::class);
        $this->emailRepository = $emailRepository;
        $this->router = $router;
    }

    /**
     * @param $shareId
     * @param $resultId
     * @return mixed|null
     */
    public function findOneByShareIdAndResultId($shareId, $resultId)
    {
        $qb = $this->createQueryBuilder('sa')
            ->leftJoin('sa.analysis', 'a')
            ->leftJoin('a.results', 'r')
            ->where('sa.id = :id')
            ->andWhere('r.id = :result')
            ->setParameter('id', $shareId)
            ->setParameter('result', $resultId);
        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function getCombinedDynamicSharesForUserCount($userId)
    {
        try {
            $countSA = $this
                ->createQueryBuilder('sa')
                ->select('count(sa.id)')
                ->join('sa.analysis', 'a', JOIN::WITH, 'a.user = :user')
                ->setParameter('user', $userId)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            $countSA = 0;
        }
        try{
            $countSR = $this
                ->getEntityManager()
                ->getRepository(SharedResult::class)
                ->createQueryBuilder('sr')
                ->select('count(sr.id)')
                ->join('sr.result', 'r', JOIN::WITH, 'r.createdByUser = :user')
                ->setParameter('user', $userId)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            $countSR = 0;
        }

        return $countSA + $countSR;
    }

    public function getCombinedDynamicSharesForUser($userId, $page = 0, $pageSize = 20)
    {
        $offset = $page * $pageSize;
        $queryString = '
            SELECT * FROM 
            (SELECT sr.id, r.measurement_name as name, "result" as type, sr.creation_date as creationDate from shared_result sr 
                JOIN result as r ON r.id = sr.result_id WHERE r.created_by_user_id = :user_id
            ) AS X
            UNION
            (SELECT sa.id, a.name as name, "analysis" as type, sa.creation_date as creationDate from shared_analysis sa 
                JOIN analysis as a ON a.id = sa.analysis_id WHERE a.user_id = :user_id
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
                    $arrayResult[$i]['emails'] = $this->emailRepository->findByDynamicResultId($arrayResult[$i]['id']);
                    $arrayResult[$i]['link'] = $this->router->generate(
                        'dynamicSharedResult',
                        ['shareId' => $arrayResult[$i]['id']],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    break;
                case 'analysis':
                    $arrayResult[$i]['emails'] = $this->emailRepository->findByDynamicAnalysisId($arrayResult[$i]['id']);
                    $arrayResult[$i]['link'] = $this->router->generate(
                        'dynamicSharedAnalysis',
                        ['shareId' => $arrayResult[$i]['id']],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    break;
            }
        }
        return $arrayResult;
    }

}
