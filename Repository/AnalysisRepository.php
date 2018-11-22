<?php
/**
 * Created by PhpStorm.
 * User: aschattney
 * Date: 03.05.18
 * Time: 14:06
 */

namespace App\Repository;


use App\Entity\Analysis;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AnalysisRepository extends ServiceEntityRepository
{

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Analysis::class);
    }


    public function findByUserAndId(User $user, $id)
    {
        $qb = $this->createQueryBuilder('a');

        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            $qb
                ->andWhere('a.user = :user')
                ->andWhere('a.discarded = false')
                ->setParameter('user', $user);
        }

        $qb
            ->andWhere('a.id = :id')
            ->setParameter('id', $id);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function getCountOfAnalyses()
    {
        $qb = $this->createQueryBuilder('a')
            ->select('count(a.id)');

        return $qb->getQuery()->getScalarResult();
    }
}