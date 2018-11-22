<?php

namespace App\Repository;

use App\Entity\AutomatedNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AutomatedNotificationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AutomatedNotification::class);
    }

    public function findByUserId($userId)
    {
        $extendedAns = [];
        $ans = $this
            ->createQueryBuilder("an")
            ->select('an.id, an.creationDate, an.isRead, an.image, an.title, an.content')
            ->where("an.user = :userId")
            ->setParameter('userId', $userId)
            ->orderBy("an.creationDate", "DESC")
            ->getQuery()->getArrayResult();
        foreach ($ans as $an) {
            $an['type'] = AutomatedNotification::$AUTOMATED_NOTIFICATION_TYPE;
            $extendedAns[] = $an;
        }
        return $extendedAns;
    }

    public function setNotificationAsReadById($id)
    {
        return $this->createQueryBuilder('an')
            ->update()
            ->set('an.isRead', true)
            ->where("an.id = :id")
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    /**
     * @param $userId
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function unreadCountByUserId($userId)
    {
        return $this
            ->createQueryBuilder("an")
            ->select('COUNT(an.id)')
            ->where("an.user = :userId")
            ->andWhere("an.isRead = false")
            ->setParameter('userId', $userId)
            ->getQuery()->getSingleScalarResult();
    }

}
