<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enums\Countries;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    public static $ADMIN_USER_REF = "admin-user";
    public static $NORMAL_USER_REF = "normal-user";
    public static $EXPIRED_USER_REF = "expired-user";

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * UserFixtures constructor.
     *
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $adminUser = new User();
        $adminUser->setUsername('admin');
        $adminUser->setEmail('admin@incloud.de');
        $adminUser->setRole('ROLE_ADMIN');
        $adminUser->setEnabled(true);
        $adminUser->setPassword($this->passwordEncoder->encodePassword($adminUser, 'developer'));
        $adminUser->setResultListmetaData('{}');
        $adminUser->setCompany('Incloud Engineering GmbH');
        $adminUser->setDisplayName('Admin User');
        $adminUser->setFirstName('Admin');
        $adminUser->setLastName('Adminson');
        $adminUser->setSegment(1);
        $adminUser->setCompanyAdress('Dolivostraße 17');
        $adminUser->setCompanyCity('Darmstadt');
        $adminUser->setCompanyCountry(Countries::COUNTRY_CODE_de);
        $adminUser->setCompanyPostalCode('64293');
        $adminUser->setSegmentDepartment('Developer');
        $adminUser->setSegmentPosition(User::COMPANY_POSITION_NOT_DEFINED);
        $adminUser->setSegmentWorkgroup('Merck');
        $adminUser->setCompanyPhone('+0049 69 123456');

        $normalUser = new User();
        $normalUser->setUsername('user1');
        $normalUser->setEmail('dev@incloud.de');
        $normalUser->setRole('ROLE_USER');
        $normalUser->setEnabled(true);
        $normalUser->setPassword($this->passwordEncoder->encodePassword($normalUser, 'developer'));
        $normalUser->setResultListmetaData('{}');
        $normalUser->setCompany('Incloud GmbH');
        $normalUser->setDisplayName('Normal User');
        $normalUser->setFirstName('Normal');
        $normalUser->setLastName('Normalson');
        $normalUser->setSegment(2);
        $normalUser->setCompanyAdress('Dolivostraße 17');
        $normalUser->setCompanyCity('Darmstadt');
        $normalUser->setCompanyCountry(Countries::COUNTRY_CODE_de);
        $normalUser->setCompanyPostalCode('64293');
        $normalUser->setSegmentDepartment('Marketing');
        $normalUser->setSegmentPosition(User::COMPANY_POSITION_GROUP_LEADER);
        $normalUser->setSegmentWorkgroup('Internal');
        $normalUser->setCompanyPhone('+0049 69 654321');

        $expiredUser = new User();
        $expiredUser->setUsername('user2');
        $expiredUser->setEmail('expired@incloud.de');
        $expiredUser->setRole('ROLE_USER');
        $expiredUser->setEnabled(true);
        $expiredUser->setPassword($this->passwordEncoder->encodePassword($normalUser, 'developer'));
        $expiredUser->setResultListmetaData('{}');
        $expiredUser->setCompany('Incloud GmbH');
        $expiredUser->setDisplayName('Normal User 2');
        $expiredUser->setFirstName('Normal');
        $expiredUser->setLastName('Normalson');
        $expiredUser->setSegment(2);
        $expiredUser->setCompanyAdress('Dolivostraße 17');
        $expiredUser->setCompanyCity('Darmstadt');
        $expiredUser->setCompanyCountry(Countries::COUNTRY_CODE_gb);
        $expiredUser->setCompanyPostalCode('64293');
        $expiredUser->setSegmentDepartment('Marketing');
        $expiredUser->setSegmentPosition(User::COMPANY_POSITION_STUDENT);
        $expiredUser->setSegmentWorkgroup('Internal');
        $expiredUser->setCompanyPhone('+0049 69 654321');
        $expiredUser->setProfileImage(md5('Normal User'));
        $expiredDate = new \DateTime();
        $expiredDate->modify('- 3 days');
        $expiredUser->setCreated($expiredDate);
        $expiredUser->setConfirmationToken('testtoken');

        $this->setReference(UserFixtures::$ADMIN_USER_REF, $adminUser);
        $this->setReference(UserFixtures::$NORMAL_USER_REF, $normalUser);
        $this->setReference(UserFixtures::$EXPIRED_USER_REF, $expiredUser);

        $manager->persist($adminUser);
        $manager->persist($normalUser);
        $manager->persist($expiredUser);
        $manager->flush();
    }
}
