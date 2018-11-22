<?php

namespace App\Repository;

use App\Entity\ResultSnapshot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ResultSnapshotRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ResultSnapshot::class);
    }

    public function findById($id)
    {
        $now = time();
        $qb = $this->createQueryBuilder('rs')
            ->where('rs.id = :id')
            ->andWhere('rs.validUntil > :validUntil')
            ->setParameter('id', $id)
            ->setParameter('validUntil', $now);
        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
