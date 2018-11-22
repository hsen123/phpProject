<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param $token
     *
     * @return User|null
     */
    public function findByToken($token)
    {
        $yesterday = new \DateTime();
        $yesterday->modify('- 1 Day');

        $user = null;

        try {
            $user = $this->createQueryBuilder('u')
                ->where('u.newsletterToken = :token')
                ->andWhere('u.newsletterTokenTime >= :time')
                ->setParameters([
                    'token' => $token,
                    'time' => $yesterday,
                ])
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (\Exception $e) {
        }

        return $user;
    }

    public function getCountOfUsers()
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)');

        return $qb->getQuery()->getScalarResult();
    }
}
