<?php

namespace App\DataFixtures;

use App\Entity\Result;
use App\Entity\TestStripPackage;
use Aws\S3\S3Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ResultFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var string
     */
    private $measurementBucketName;

    /**
     * @var string
     */
    private $measurementImagePath;

    /**
     * @var string
     */
    private $userProfileBucketName;

    /**
     * @var string
     */
    private $userProfileImagePath;

    /**
     * @var S3Client
     */
    private $s3Client;

    public function __construct(S3Client $s3Client)
    {
        $this->measurementBucketName = getenv('AWS_S3_MEASUREMENT_IMAGE_BUCKET'); //TODO: Fake s3 needs a space at the end of a bucket name
        $this->measurementImagePath = getenv('AWS_S3_MEASUREMENT_IMAGE_PATH');
        $this->userProfileBucketName = getenv('AWS_S3_USER_PROFILE_IMAGE_BUCKET'); //TODO: Fake s3 needs a space at the end of a bucket name
        $this->userProfileImagePath = getenv('AWS_S3_USER_PROFILE_IMAGE_PATH');
        $this->s3Client = $s3Client;

        //TODO: since fakes3 mistrieats bucket names we have to check if we are in a dev environment and adjust bucket names accordingly
        if ('dev' === getenv('APP_ENV')) {
            $this->measurementBucketName = $this->measurementBucketName . ' ';
            $this->userProfileBucketName = $this->userProfileBucketName . ' ';
        }
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     * @throws \App\Exception\TestStripEmptyException
     */
    public function load(ObjectManager $manager)
    {
        $baseLongitude = 8.66;
        $baseLatitude = 49.87;

        $resultName = ['', '', '', 'Soil Sample 1', 'Spil Sample 2', 'Soil Sample 3', 'Lake Sample 4', 'Lake Sample 5', 'Lake Sample 6'];
        $comments = ['Sample was taken while it was raining', 'Sunny day', 'Sample taken by Phillip', 'This is an ordinary but rather long comment which could lead to ugly line-breaks in the webview.'];
        $phones = ['iPhone 5', 'iPhone 6', 'iPhone 6S', 'iPhone X', 'Nokia 3210', 'Samsung Galaxy S4', 'Nexus 5X', 'Nexus 6P'];
        $oss = ['iOS 10', 'iOS 11.2', 'Android 7', 'Anroid 8', 'Android 4.4'];
        $cameras = ['back-camera', 'front-camera'];

        // Get sample-test iamge and encode it to base64
        $path = 'public/images/test_measure.jpg';
        $data = file_get_contents($path);
        $base64Image = base64_encode($data);

        // Set Image name
        $imageName = md5(uniqid(rand(), true));
        // Push sample-test image to fake-s3
        $this->s3Client->putObject(['Bucket' => $this->measurementBucketName, 'Key' => $this->measurementImagePath . $imageName, 'Body' => base64_decode($base64Image)]);

        //NO3 Samples
        for ($i = 1; $i <= 100; ++$i) {
            $result = new Result();
            $result->setDiscardedResult((0 === $i % 10));
            $measurementValue = mt_rand(1, 700);
            $result->setMeasurementValueMax(500);
            if (0 === $i % 30) {
                $result->setMeasurementValue(501);
            } else {
                $result->setMeasurementValue($measurementValue);
            }
            $result->setMeasurementValueMin(0);
            $result->setMeasurementUnit('mg/L');
            $result->setMeasurementName($resultName[mt_rand(0, 8)]);
            $result->setCitationForm(Result::CITATION_NO3);
            $actualDate = new \DateTime('now');
            $subtractSeconds = mt_rand(0, 300*24*60*60);
            $actualDate->modify('-' . $subtractSeconds . ' seconds');
            $result->setSampleCreationDate($actualDate->getTimestamp());
            $randomUserID = mt_rand(1,3);
            switch($randomUserID) {
                case 1: $result->setCreatedByUser($this->getReference('normal-user')); break;
                case 2: $result->setCreatedByUser($this->getReference('admin-user')); break;
                case 3: $result->setCreatedByUser($this->getReference('expired-user')); break;
                default; break;
            }


            //every 25th result we do not have a location
            if (0 !== $i % 25) {
                $variableLongitude = mt_rand(0, 999) / 100000;
                $variableLatitude = mt_rand(0, 999) / 100000;
                $result->setLocationLng($baseLongitude + $variableLongitude);
                $result->setLocationLat($baseLatitude + $variableLatitude);
            } else {
                $result->setLocationLng(null);
                $result->setLocationLat(null);
            }

            $result->setComment($comments[mt_rand(0, 3)]);
            $result->setVisibleMeasurementId('20180222-0302347-001');
            // test-strips can be empty

            if (mt_rand(0, 100) > 20) {
                /** @var TestStripPackage $randomNO3TestStripPackage */
                $randomNO3TestStripPackage = $this->getReference('test-strip-package-no3-' . mt_rand(1, 3));
                $result->setTestStripPackage($randomNO3TestStripPackage);
                $result->setTestStripLotNumber('BB' . mt_rand(103320, 103330));
                $randomNO3TestStripPackage->decrementAmountOfTestStripsLeft();
            }
            $result->setCardCatalogNumber(Result::CARD_CATALOG_NO3);
            $result->setCardLotNumber(mt_rand(110022, 110032));
            $result->setTestStripCatalogNumber(Result::TEST_STRIP_CATALOG_NO3);
            $result->setPhoneName($phones[mt_rand(0, 7)]);
            $result->setPhoneOperatingSystem($oss[mt_rand(0, 4)]);
            $result->setPhoneCamera($cameras[mt_rand(0, 1)]);
            if ($measurementValue <= 500) {
                $result->setInvalidSampleMessageCode(0);
            } else {
                $result->setinvalidSampleMessageCode(300);
            }
            $result->setSampleImage($imageName);
            $result->setUpdatedAt($actualDate->getTimestamp());
            $manager->persist($result);
            if ($i % 500 === 0) {
                $manager->flush();
                $count = $manager->getRepository(Result::class)->findAll();
            }
        }
        $manager->flush();

        //pH Samples
        for ($i = 1; $i <= 100; ++$i) {
            $result = new Result();
            $result->setDiscardedResult((0 === $i % 10));
            $result->setMeasurementValue(mt_rand(1, 14));
            $result->setMeasurementValueMax(14);
            $result->setMeasurementValueMin(0);
            $result->setMeasurementUnit('');
            $result->setMeasurementName($resultName[mt_rand(0, 8)]);
            $result->setCitationForm(Result::CITATION_PH);
            $actualDate = new \DateTime('now');
            $subtractSeconds = mt_rand(0, 300*24*60*60);
            $actualDate->modify('-' . $subtractSeconds . ' seconds');
            $result->setSampleCreationDate($actualDate->getTimestamp());
            $result->setCreatedByUser($this->getReference('normal-user'));

            //every 25th result we do not have a location
            if (0 !== $i % 25) {
                $variableLongitude = mt_rand(0, 999) / 100000;
                $variableLatitude = mt_rand(0, 999) / 100000;
                $result->setLocationLng($baseLongitude + $variableLongitude);
                $result->setLocationLat($baseLatitude + $variableLatitude);
            } else {
                $result->setLocationLng(null);
                $result->setLocationLat(null);
            }
            $result->setComment($comments[mt_rand(0, 3)]);
            $result->setVisibleMeasurementId('20180222-0302347-001');
            // test-strips can be empty
            if (mt_rand(0, 100) > 20) {
                /** @var TestStripPackage $randomPHTestStripPackage */
                $randomPHTestStripPackage = $this->getReference('test-strip-package-ph-' . mt_rand(1, 3));
                $result->setTestStripPackage($randomPHTestStripPackage);
                $result->setTestStripLotNumber('AA' . mt_rand(103300, 103310));
                $randomPHTestStripPackage->decrementAmountOfTestStripsLeft();
            }
            $result->setCardCatalogNumber(Result::CARD_CATALOG_PH);
            $result->setCardLotNumber(mt_rand(110002, 110012));
            $result->setTestStripCatalogNumber(Result::TEST_STRIP_CATALOG_PH);
            $result->setPhoneName($phones[mt_rand(0, 7)]);
            $result->setPhoneOperatingSystem($oss[mt_rand(0, 4)]);
            $result->setPhoneCamera($cameras[mt_rand(0, 1)]);
            if ($measurementValue > 14) {
                $result->setInvalidSampleMessageCode(0);
            } else {
                $result->setInvalidSampleMessageCode(300);
            }
            $result->setSampleImage($imageName);
            $result->setUpdatedAt($actualDate->getTimestamp());
            $manager->persist($result);
            if ($i % 500 === 0) {
                $manager->flush();
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            TestStripPackageFixtures::class,
        ];
    }
}
