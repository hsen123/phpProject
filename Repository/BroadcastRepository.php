<?php

namespace App\Repository;

use App\Entity\Broadcast;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BroadcastRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Broadcast::class);
    }

    public function findAll()
    {
        $broadcasts = $this->createQueryBuilder('b')
            ->select('b.id, b.creationDate, b.sentDate, b.title, b.content, b.image')
            ->where('b.sentDate IS NOT NULL')
            ->orderBy('b.sentDate', 'DESC')
            ->getQuery()->getArrayResult();
        $extendedBroadcasts = [];
        foreach ($broadcasts as $broadcast) {
            $broadcast['type'] = Broadcast::$BROADCAST_TYPE;
            if ($broadcast['image'] !== null) {
                $broadcast['image'] = '/api/broadcast-image/' . $broadcast['id'];
            }
            $extendedBroadcasts[] = $broadcast;
        }
        return $extendedBroadcasts;
    }


    public function isBroadcastReadByUser($broadcastId, $userId)
    {
        $results = $this->createQueryBuilder('b')
            ->leftJoin('b.users', 'u', JOIN::WITH, "u.id = :uId")
            ->select('u.id')
            ->where("b.id = :bId")
            ->setParameter('bId', $broadcastId)
            ->andWhere("u.id = :uId")
            ->setParameter('uId', $userId)
            ->getQuery()->getArrayResult();
        return sizeof($results) === 1;
    }


    /**
     * @param $id
     * @param User $user
     */
    public function setBroadCastAsReadById($id, User $user)
    {
        /** @var Broadcast $broadcast */
        $broadcast = $this->findOneBy(["id" => $id]);
        $broadcast->readBroadcastByUser($user);
        $em = $this->getEntityManager();
        try {
            $em->persist($broadcast);
            $em->flush();
        } catch (ORMException $e) {
        }
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countAllUnsent()
    {
        return $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.sentDate IS NOT NULL')
            ->getQuery()->getSingleScalarResult();
    }

}
