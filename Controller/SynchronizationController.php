<?php

namespace App\Controller;

use App\Entity\Result;
use App\Entity\User;
use App\Repository\ResultRepository;
use App\Repository\UserRepository;
use App\Service\AchievementService;
use App\Service\S3ImageService;
use App\Service\NotificationService;
use App\Service\SynchronizationService;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SynchronizationController extends Controller
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
     * @var S3ImageService
     */
    private $imageService;
    /**
     * @var ResultRepository
     */
    private $resultRepository;

    public function __construct(S3ImageService $imageService, S3Client $s3Client, AuthorizationCheckerInterface $authorizationChecker, ResultRepository $resultRepository)
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
        $this->imageService = $imageService;
        $this->resultRepository = $resultRepository;
    }

    /**
     * @Route("/api/image/{resultId}", name="resultImage")
     *
     * @param $resultId
     *
     * @return JsonResponse|Response
     */
    public function resultImage($resultId)
    {
        /** @var ResultRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Result::class);
        /** @var Result $result */
        $result = $repo->findOneBy(['id' => $resultId]);

        if (!$result) {
            throw $this->createNotFoundException();
        }

        /** @var User $user */
        $user = $this->getUser();
        if ($result->getCreatedByUser()->getId() !== $user->getId() && !$user->hasRole('ROLE_ADMIN')) {
            throw $this->createNotFoundException();
        }

        return self::sendImage(
            $resultId,
            $this->resultRepository,
            $this->imageService,
            $this->s3Client,
            $this->measurementBucketName,
            $this->measurementImagePath
        );
    }

    /**
     * @param $resultId
     * @param ResultRepository $resultRepository
     * @param S3ImageService $imageService
     * @param S3Client $s3Client
     * @param string $measurementBucketName
     * @param string $measurementImagePath
     * @return JsonResponse|Response
     */
    static function sendImage($resultId, ResultRepository $resultRepository, S3ImageService $imageService, S3Client $s3Client, string $measurementBucketName, string $measurementImagePath)
    {
        try {
            /** @var Result $result */
            $result = $resultRepository->find($resultId);
            $userImageExists = $s3Client->doesObjectExist($measurementBucketName, $measurementImagePath . $result->getSampleImage());

            if (!$userImageExists) {
                return new JsonResponse(['error' => 'Image not found.'], 404);
            }

            $userImageObject = $s3Client->getObject(['Bucket' => $measurementBucketName, 'Key' => $measurementImagePath . $result->getSampleImage()]);
            $body = $userImageObject->get('Body');
            $body->rewind();

            $image = $imageService->getResultImage($resultId);
            return new Response($image, 200, ['Content-Type' => 'image/jpeg']);
        } catch (\Exception $e) {
            if ($e instanceof NotFoundHttpException) {
                return new JsonResponse(null, 404);
            } else {
                return new JsonResponse(['error' => 'Unexpected error.'], 500);
            }
        }
    }

    /**
     * @Route("/api/image/user/{userId}", name="getProfileImage")
     * @Method({"GET"})
     *
     * @param UserRepository $userRepository
     * @param $userId
     *
     * @return JsonResponse|Response
     */
    public function getProfileImage(UserRepository $userRepository, $userId)
    {
        $this->defaultImageDirectoryPath = $this->container->get('kernel')->getProjectDir() . '/public/images/defaultProfileImages/';

        /** @var User $user */
        $user = $userRepository->find($userId);
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if (
            null === $user ||
            ($currentUser->getId() !== intval($userId) && !$currentUser->hasRole('ROLE_ADMIN'))
        ) {
            return new JsonResponse(null, 404);
        }

        try {
            if (null !== $user->getProfileImage()) {
                $profileImageExists = $this->s3Client->doesObjectExist(
                    $this->userProfileBucketName,
                    $this->userProfileImagePath . $user->getProfileImage()
                );

                if ($profileImageExists) {
                    $profileImageObject = $this->s3Client->getObject(
                        ['Bucket' => $this->userProfileBucketName, 'Key' => $this->userProfileImagePath . $user->getProfileImage()]
                    );
                    $body = $profileImageObject->get('Body');
                    $body->rewind();
                    $content = $body->read($profileImageObject['ContentLength']);

                    return new Response(
                        $content, 200, [
                            'Content-Type' => 'image/jpeg',
                        ]
                    );
                }


            }
            $defaultImageFileName = ($user->getId() % 13) . '.png';
            $defaultImagePath = $this->defaultImageDirectoryPath . $defaultImageFileName;
            $image = file_get_contents($defaultImagePath);

            return new Response(
                $image, 200, [
                    'Content-Type' => 'image/jpeg',
                ]
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unexpected error.'], 500);
        }
    }

    /**
     * @Route("/api/image/user/{userId}", name="postProfileImage")
     * @Method({"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param $userId
     * @param EntityManagerInterface $manager
     * @param TokenStorageInterface $tokenStorage
     * @param TranslatorInterface $translator
     * @return JsonResponse
     */
    public function postProfileImage(Request $request, UserRepository $userRepository, $userId, EntityManagerInterface $manager, TokenStorageInterface $tokenStorage, TranslatorInterface $translator)
    {
        $obj = json_decode($request->getContent());

        if (!isset($obj->profileImage)) {
            return new JsonResponse(null, 400);
        }

        try {
            // open a temporary file handled in memory
            $tmp_handle = fopen('php://memory', 'r+');
            // write file to memory
            fwrite($tmp_handle, base64_decode($obj->profileImage));
            // set pointer to first position
            rewind($tmp_handle);
            // get mime type of the file
            $mimeType = mime_content_type($tmp_handle);
            // get the actual sitze of the file in byte
            $fileSize = fstat($tmp_handle)['size'];
            // clean up your temporary storage handle
            fclose($tmp_handle);

            // calculate size auf of the file from byte -> megabyte (1048576 = 1MB)
            $fileSize = number_format($fileSize / 1048576, 2);

            /** @var User $user */
            $user = $userRepository->find($userId);

            // check mime type
            if (false === strpos($mimeType, 'image/')) {
                $this->addFlash('danger', $translator->trans('pages.profile.flashmessages.wrong_mime_type'));

                return new JsonResponse([
                    null, 400,
                    'id' => (string)$user->getId(),
                ]);
            }

            // check file suize
            if ($fileSize > 5) {
                $this->addFlash('danger', $translator->trans('pages.profile.flashmessages.image_too_large'));

                return new JsonResponse([
                    null, 400,
                    'id' => (string)$user->getId(),
                ]);
            }

            if (null === $user || $tokenStorage->getToken()->getUser()->getId() !== intval($userId)) {
                return new JsonResponse(null, 404);
            }

            //initial creation of image
            if (null === $user->getProfileImage()) {
                $imageName = md5(uniqid(rand(), true));
            } else { //update profile image
                $imageName = $user->getProfileImage();
            }

            $this->addFlash('success', $translator->trans('pages.profile.flashmessages.updated_profile'));
            $this->s3Client->putObject(['Bucket' => $this->userProfileBucketName, 'Key' => $this->userProfileImagePath . $imageName, 'Body' => base64_decode($obj->profileImage)]);

            $user->setProfileImage($imageName);
            $manager->persist($user);
            $manager->flush();

            return new JsonResponse([
                'id' => (string)$user->getId(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unexpected error.'], 500);
        }
    }

    /**
     * @Route("/api/measurement/upload", name="synchronizationUpload")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SynchronizationService $synchronizationService
     *
     * @param NotificationService $notificationService
     * @return JsonResponse
     */
    public function uploadAction(Request $request, EntityManagerInterface $manager, SynchronizationService $synchronizationService, NotificationService $notificationService)
    {
        $obj = json_decode($request->getContent());

        /** @var User $user */
        $user = $this->getUser();

        if (!$synchronizationService->checkInput($obj)) {
            return new JsonResponse(null, 400);
        }

        $id = (string)intval($obj->id);

        $isClientId = $id != $obj->id;

        /** @var Result $result */
        $result = !$isClientId ? $this->resultRepository->find($obj->id) : $this->resultRepository->findByClientId($obj->id, $user);

        if (null === $result) { //new result

            $result = new Result();
            $imageName = md5(uniqid(rand(), true));
            $this->s3Client->putObject(['Bucket' => $this->measurementBucketName, 'Key' => $this->measurementImagePath . $imageName, 'Body' => base64_decode($obj->sampleImage)]);

            $oldUserLevel = $user->getActualMeasurementLevel();
            $synchronizationService->setResult($result, $user, $obj, $imageName);
            if ($isClientId) {
                $result->setClientId($obj->id);
            }
            $user->incrementResultCount($result);
            if ($user->getActualMeasurementLevel() > $oldUserLevel) {
                $notificationService->createLevelUpNotification($user);
            }

            $testStripPackage = $synchronizationService->handleTestStripPackage($user, $obj->citationForm);
            $result->setTestStripPackage($testStripPackage);

            if ($result->getTestStripLotNumber() !== $testStripPackage->getBatchNumber()) {
                $result->setTestStripLotNumber($testStripPackage->getBatchNumber());
                $result->setUpdatedAt(time());
            }

            $manager->persist($result);
            $manager->persist($user);
            $manager->flush();
        } else { //update
            if (($obj->discardedResult && false === $result->getDiscardedResult()) || $isClientId) {
                $synchronizationService->setResult($result, $user, $obj);
                $manager->flush();

                return new JsonResponse([
                    'id' => (string)$result->getId(),
                ]);
            }

            if ($result->getUpdatedAt() >= $obj->updatedAt || 1 === $result->getDiscardedResult()) {
                return new JsonResponse(null, 409);
            }

            $synchronizationService->setResult($result, $user, $obj);
            $manager->flush();
        }

        return new JsonResponse([
            'id' => (string)$result->getId(),
        ]);
    }

    /**
     * @Route("/api/measurement/latest", name="synchronizationLatest")
     *
     * @param Request $request
     *
     * @param AchievementService $achievementService
     * @return JsonResponse
     */
    public function latestAction(Request $request, AchievementService $achievementService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $lastSyncTime = $request->get('lastSyncTime');

        $achievementService->setAchievement($user, $lastSyncTime);

        $latest = $this->resultRepository->findByLastSyncTime($user, $lastSyncTime);

        return new JsonResponse($latest);
    }
}
