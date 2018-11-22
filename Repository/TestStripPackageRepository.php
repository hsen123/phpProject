<?php

namespace App\Repository;

use App\Entity\Result;
use App\Entity\TestStripPackage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TestStripPackageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TestStripPackage::class);
    }

    /**
     * @param User $user
     * @param int $citationForm
     * @return TestStripPackage|null
     */
    public function findForUserAndCitation(User $user, int $citationForm)
    {
        if (!in_array($citationForm, [Result::CITATION_NO3, Result::CITATION_PH])) {
            throw new \InvalidArgumentException('Unknown citation form given.');
        }

        $qb = $this->createQueryBuilder('ts');
        $qb
            ->where('ts.user = :user')
            ->andWhere('ts.citationForm = :citationForm')
            ->setParameter('citationForm', $citationForm)
            ->setParameter('user', $user)
            ->orderBy('ts.creationDate', 'DESC')
            ->setMaxResults(1);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
