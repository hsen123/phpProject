<?php
/**
 * Created by PhpStorm.
 * User: aschattney
 * Date: 30.04.18
 * Time: 11:20
 */

namespace App\Repository;


use App\Entity\DeviceEntry;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\LazyCriteriaCollection;
use Gesdinet\JWTRefreshTokenBundle\Doctrine\RefreshTokenManager;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DeviceManagementRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RefreshTokenManager
     */
    private $refreshTokenManager;

    /**
     * @param RegistryInterface $registry
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(RegistryInterface $registry, EntityManagerInterface $entityManager, RefreshTokenManager $refreshTokenManager)
    {
        parent::__construct($registry, DeviceEntry::class);
        $this->entityManager = $entityManager;
        $this->refreshTokenManager = $refreshTokenManager;
    }

    /**
     * @param $user User
     * @param $deviceId string
     * @return DeviceEntry|null
     */
    function entryFor($user, $deviceId)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq("user", $user))
            ->andWhere(Criteria::expr()->eq("deviceId", $deviceId));

        $result = $this->matching($criteria);
        return !$result->isEmpty() ? $result->first() : null;
    }

    /**
     * @param $refreshToken string
     * @return DeviceEntry|null
     */
    function entryByRefreshToken($refreshToken)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq("refreshToken", $refreshToken));
        $result = $this->matching($criteria);
        return !$result->isEmpty() ? $result->first() : null;
    }

    /**
     * @param $user User
     * @param $deviceId string
     * @return Collection
     */
    function allEntriesForUserAndDeviceId($user, $deviceId)
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->where('d.user = :userId')
            ->andWhere('d.deviceId = :deviceId')
            ->setParameter('userId', $user)
            ->setParameter('deviceId', $deviceId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $user User
     * @return Collection
     */
    function allEnabledDevicesForUser(User $user)
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('d.enabled = true')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    /**
     * Disables all entries for user and deviceId. If an entry gets disabled, the corresponding refresh token
     * is erased from table "refresh_token".
     * @param $user User
     * @param $deviceId string
     * @return void
     */
    function revokeAllEntriesFor($user, $deviceId)
    {
        $entries = $this->allEntriesForUserAndDeviceId($user, $deviceId);
        $this->revokeAll($entries->toArray());
    }

    /**
     * @param array $deviceEntries
     * @return void
     */
    private function revokeAll(array $deviceEntries)
    {
        /**
         * @var $deviceEntry DeviceEntry
         */
        foreach ($deviceEntries as $deviceEntry) {
            $deviceEntry->setEnabled(false);
            $this->entityManager->persist($deviceEntry);
            $refreshTokenEntity = $this->refreshTokenManager->get($deviceEntry->getRefreshToken());
            if ($refreshTokenEntity !== null) {
                $this->refreshTokenManager->delete($refreshTokenEntity);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     * @param $deviceId string
     * @param $deviceName
     * @param $refreshToken string
     * @return void
     */
    function save(User $user, $deviceId, $deviceName, $refreshToken)
    {

        $deviceEntry = $this->findOneBy(['user' => $user, 'deviceId' => $deviceId]);
        $deviceEntry = $deviceEntry === null ? new DeviceEntry() : $deviceEntry;

        $deviceEntry->setUser($user)
            ->setDeviceId($deviceId)
            ->setRefreshToken($refreshToken)
            ->setDeviceName($deviceName)
            ->setEnabled(true);

        $this->entityManager->persist($deviceEntry);
        $this->entityManager->flush();
    }

}