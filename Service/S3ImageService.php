<?php
/**
 * Created by PhpStorm.
 * User: aschattney
 * Date: 15.06.18
 * Time: 21:40
 */

namespace App\Service;


use App\Entity\Result;
use App\Repository\ResultRepository;
use Aws\S3\S3Client;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class S3ImageService
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

    private $defaultImageDirectoryPath;
    /**
     * @var S3Client
     */
    private $s3Client;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;
    /**
     * @var ResultRepository
     */
    private $resultRepository;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(S3Client $s3Client, ResultRepository $resultRepository, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker)
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
        $this->authorizationChecker = $authorizationChecker;
        $this->resultRepository = $resultRepository;
        $this->tokenStorage = $tokenStorage;
    }


    public function getResultImageWithoutAuthorization(int $resultId)
    {
        /** @var Result $result */
        $result = $this->resultRepository->find($resultId);

        if (null === $result) {
            throw new NotFoundHttpException();
        }

        $userImageExists = $this->s3Client->doesObjectExist($this->measurementBucketName, $this->measurementImagePath . $result->getSampleImage());

        if (!$userImageExists) {
            throw new NotFoundHttpException();
        }

        $userImageObject = $this->s3Client->getObject(['Bucket' => $this->measurementBucketName, 'Key' => $this->measurementImagePath . $result->getSampleImage()]);
        $body = $userImageObject->get('Body');
        $body->rewind();
        $content = $body->read($userImageObject['ContentLength']);
        return $content;
    }

    /**
     * @param int $resultId
     * @throws NotFoundHttpException
     * @return mixed
     */
    public function getResultImage(int $resultId)
    {
        /** @var Result $result */
        $result = $this->resultRepository->find($resultId);

        if (null === $result) {
            throw new NotFoundHttpException();
        }

        $userImageExists = $this->s3Client->doesObjectExist($this->measurementBucketName, $this->measurementImagePath . $result->getSampleImage());

        if (!$userImageExists) {
            throw new NotFoundHttpException();
        }

        $userImageObject = $this->s3Client->getObject(['Bucket' => $this->measurementBucketName, 'Key' => $this->measurementImagePath . $result->getSampleImage()]);
        $body = $userImageObject->get('Body');
        $body->rewind();
        $content = $body->read($userImageObject['ContentLength']);
        return $content;

    }

}
