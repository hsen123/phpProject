<?php

namespace App\Controller;

use App\Entity\AutomatedNotification;
use App\Entity\Broadcast;
use App\Entity\User;
use App\Form\Type\BroadcastCreateType;
use App\Repository\AutomatedNotificationRepository;
use App\Repository\BroadcastRepository;
use Aws\S3\S3Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    /**
     * @Route("/notifications", name="notifications")
     * @Route("/notification/{type}/{userNotificationId}", name="notificationDetail")
     * @param Request $request
     * @param AutomatedNotificationRepository $anRepository
     * @param BroadcastRepository $broadcastRepository
     * @param null $userNotificationId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderNotifications(Request $request, AutomatedNotificationRepository $anRepository, BroadcastRepository $broadcastRepository, $type = null, $userNotificationId = null)
    {
        /** @var User $user */
        $user = $this->getUser();

        $combinedInbox = $this->getCombinedInbox($user, $anRepository, $broadcastRepository);
        $index = 0;
        $detailNotification = null;
        if (isset($userNotificationId)) {
            foreach ($combinedInbox as $i => $notification) {
                //check for type
                if ($notification["id"] == $userNotificationId && $type == $notification['type']) {
                    $detailNotification = $notification;
                    $index = $i;
                    break;
                }
            }
        } else {
            // if not just get the latest notification
            if (sizeof($combinedInbox) > 0) {
                $detailNotification = $combinedInbox[0];
            }
        }


        if ($detailNotification) {
            if ($detailNotification['type'] === Broadcast::$BROADCAST_TYPE) {
                $broadcastRepository->setBroadCastAsReadById($detailNotification['id'], $user);
            } else {
                $anRepository->setNotificationAsReadById($detailNotification['id']);
            }
            $combinedInbox[$index]['isRead'] = true;
        }

        return $this->render('notification/index.html.twig', [
            'title' => 'Notifications',
            'notifications' => $combinedInbox,
            'activeIndex' => $index + 1,
            'detailNotification' => $detailNotification
        ]);

    }

    /**
     * @param User $user
     * @param AutomatedNotificationRepository $anRepository
     * @param BroadcastRepository $broadcastRepository
     *
     * @return array
     */
    private function getCombinedInbox(User $user, AutomatedNotificationRepository $anRepository, BroadcastRepository $broadcastRepository)
    {
        $ans = $anRepository->findByUserId($user->getId());
        $broadcasts = $broadcastRepository->findAll();
        /** @var Broadcast $broadcast */
        foreach ($broadcasts as $index => $broadcast) {
            $broadcasts[$index]['isRead'] = $broadcastRepository->isBroadcastReadByUser($broadcast['id'], $user->getId());
        }
        $combined = array_merge($ans, $broadcasts);
        usort(/**
         * @param Broadcast|AutomatedNotification $item1
         * @param Broadcast|AutomatedNotification $item2
         * @return bool
         */
            $combined, function ($item1, $item2) {
                $date1 = isset($item1['sentDate'])? $item1['sentDate']: $item1['creationDate'];
                $date2 = isset($item2['sentDate'])? $item2['sentDate']: $item2['creationDate'];
            return $date1 <= $date2;
        });
        return $combined;
    }

    /**
     * @Route("/api/broadcast-image/{broadcastId}", name="broadcastImage")
     *
     * @param Request $request
     * @param BroadcastRepository $broadCastRepository
     * @param S3Client $s3Client
     * @param $broadcastId
     *
     * @return JsonResponse|Response
     */
    public function broadcastImage(Request $request, BroadcastRepository $broadCastRepository, S3Client $s3Client, $broadcastId)
    {
        /** @var Broadcast $broadcast */
        $broadcast = $broadCastRepository->find($broadcastId);

        if (null === $broadcast) {
            return new JsonResponse(null, 404);
        }

        try {
            $bucketName = getenv('AWS_S3_BROADCAST_IMAGE_BUCKET');
            $bucketPath = getenv('AWS_S3_BROADCAST_IMAGE_PATH');
            if ('dev' === getenv('APP_ENV')) {
                $bucketName = $bucketName . ' ';
            }
            $filePath = $bucketPath . $broadcast->getImage();
            if (!$s3Client->doesObjectExist($bucketName, $filePath)) {
                return new JsonResponse(['error' => 'Image not found.'], 404);
            }

            $imageObject = $s3Client->getObject(['Bucket' => $bucketName, 'Key' => $filePath]);
            $body = $imageObject->get('Body');
            $body->rewind();
            $content = $body->read($imageObject['ContentLength']);

            return new Response($content, 200, [
                'Content-Type' => 'image/png',]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unexpected error.'], 500);
        }
    }

    private function removeImageForBroadcast(Broadcast $broadcast, S3Client $s3Client)
    {
        if (null === $broadcast) {
            return false;
        }

        try {
            $bucketName = getenv('AWS_S3_BROADCAST_IMAGE_BUCKET');
            $bucketPath = getenv('AWS_S3_BROADCAST_IMAGE_PATH');
            if ('dev' === getenv('APP_ENV')) {
                $bucketName = $bucketName . ' ';
            }
            $filePath = $bucketPath . $broadcast->getImage();
            if (!$s3Client->doesObjectExist($bucketName, $filePath)) {
                return false;
            }

            $s3Client->deleteObject(['Bucket' => $bucketName, 'Key' => $filePath]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @Route("/api/notifications", name="api_notifications")
     * @Method({"GET"})
     * @param Request $request
     * @param AutomatedNotificationRepository $anRepository
     *
     * @param BroadcastRepository $broadcastRepository
     * @return JsonResponse
     */
    public function getNotifications(Request $request, AutomatedNotificationRepository $anRepository, BroadcastRepository $broadcastRepository)
    {
        $user = $this->getUser();

        if (null !== $user) {
            $notifications = $this->getCombinedInbox($user, $anRepository, $broadcastRepository);
        } else {
            $notifications = $broadcastRepository->findAll();
        }

        return new JsonResponse($notifications);
    }

    /**
     * @Route("/api/broadcast/create", name="api_create_broadcast")
     * @Method({"POST"})
     * @param Request $request
     * @param S3Client $s3Client
     * @return JsonResponse|Response
     */
    public function createBroadcast(Request $request, S3Client $s3Client)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            throw new AccessDeniedException();
        }
        $broadcast = new Broadcast();
        $obj = json_decode($request->getContent());
        $form = $this->createForm(BroadcastCreateType::class, $broadcast);
        $form->submit(["title" => $obj->title, "content" => $obj->content]);
        if ($form->isValid()) {

            if (isset($obj->base64)) {

                $bucketName = getenv('AWS_S3_BROADCAST_IMAGE_BUCKET');
                $bucketPath = getenv('AWS_S3_BROADCAST_IMAGE_PATH');
                if ('dev' === getenv('APP_ENV')) {
                    $bucketName = $bucketName . ' ';
                }
                $imageName = md5(uniqid(rand(), true));
                $decoded = base64_decode($obj->base64);
                $sizeInMb = strlen($decoded) / 1024 / 1024 * 0.67;
                if ($sizeInMb > 200) {
                    return new Response("Exceeds maximum file size of 200MB ", 400, []);
                }
                $s3Client->putObject(['Bucket' => $bucketName, 'Key' => $bucketPath . $imageName, 'Body' => $decoded]);

                $broadcast->setImage($imageName);
            }

            $manager = $this->getDoctrine()->getManager();
            $broadcast->setOwner($user);
            $manager->persist($broadcast);
            $manager->flush();
            return new JsonResponse(['id' => $broadcast->getId()], 200);
        }
        return new JsonResponse($form->getErrors(), 500, [], true);
    }

    /**
     * @Route("/api/broadcast/update", name="api_update_broadcast")
     * @Method({"PUT"})
     * @param Request $request
     * @param S3Client $s3Client
     * @param BroadcastRepository $broadcastRepository
     * @return JsonResponse|Response
     */
    public function updateBroadcast(Request $request, S3Client $s3Client, BroadcastRepository $broadcastRepository)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            throw new AccessDeniedException();
        }
        $obj = json_decode($request->getContent());
        /** @var Broadcast $broadcast */
        $broadcast = $broadcastRepository->findOneBy(['id' => $obj->id]);
        if (!$broadcast) {
            return new Response('Not found', 404);
        }
        if ($broadcast->getImage() !== null) {
            $this->removeImageForBroadcast($broadcast, $s3Client);
        }
        $form = $this->createForm(BroadcastCreateType::class, $broadcast);
        $form->submit(["title" => $obj->title, "content" => $obj->content]);
        if ($form->isValid()) {

            if (isset($obj->base64)) {

                $bucketName = getenv('AWS_S3_BROADCAST_IMAGE_BUCKET');
                $bucketPath = getenv('AWS_S3_BROADCAST_IMAGE_PATH');
                if ('dev' === getenv('APP_ENV')) {
                    $bucketName = $bucketName . ' ';
                }
                $imageName = md5(uniqid(rand(), true));
                $decoded = base64_decode($obj->base64);
                $sizeInMb = strlen($decoded) / 1024 / 1024 * 0.67;
                if ($sizeInMb > 200) {
                    return new Response("Exceeds maximum file size of 200MB ", 400, []);
                }
                $s3Client->putObject(['Bucket' => $bucketName, 'Key' => $bucketPath . $imageName, 'Body' => $decoded]);

                $broadcast->setImage($imageName);
            } else {
                $broadcast->setImage(null);
            }

            $manager = $this->getDoctrine()->getManager();
            $broadcast->setOwner($user);
            $manager->persist($broadcast);
            $manager->flush();
            return new JsonResponse(['id' => $broadcast->getId()], 200);
        }
        return new JsonResponse($form->getErrors(), 500, [], true);
    }

    /**
     * @Route("/api/delete-broadcast/{broadcastId}", name="api_delete_broadcast")
     * @Method({"DELETE"})
     * @param Request $request
     * @param S3Client $s3Client
     * @param BroadcastRepository $broadcastRepository
     * @param $broadcastId
     * @return JsonResponse|Response
     */
    public function deleteBroadcast(Request $request, S3Client $s3Client, BroadcastRepository $broadcastRepository, $broadcastId)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            throw new AccessDeniedException();
        }
        $broadcast = $broadcastRepository->findOneBy(['id' => $broadcastId]);
        if (!$broadcast) {
            return new Response('Not found', 404);
        }
        $this->removeImageForBroadcast($broadcast, $s3Client);
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($broadcast);
        $manager->flush();
        return new JsonResponse('', 200);
    }

    /**
     * @Route("/api/send-broadcast/{broadcastId}", name="api_send_broadcast")
     * @Method({"PUT"})
     * @param Request $request
     * @param BroadcastRepository $broadcastRepository
     * @param $broadcastId
     * @return JsonResponse|Response
     */
    public function sendBroadcast(Request $request, BroadcastRepository $broadcastRepository, $broadcastId)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            throw new AccessDeniedException();
        }
        $broadcast = $broadcastRepository->findOneBy(["id" => $broadcastId]);
        if (!$broadcast) {
            return new Response("Broadcast not found.", 404, []);
        }
        $manager = $this->getDoctrine()->getManager();
        $broadcast->setSentDate(time());
        $manager->persist($broadcast);
        $manager->flush();
        return new JsonResponse('', 200);

    }

    /**
     * @Route("/api/read-notification/{type}/{id}", name="api_notifications_read")
     * @Method({"PUT"})
     * @param Request $request
     *
     * @param BroadcastRepository $broadcastRepository
     * @param AutomatedNotificationRepository $anRepository
     *
     * @param $type
     * @param $id
     * @return JsonResponse|Response
     */
    public function markAsRead(Request $request, BroadcastRepository $broadcastRepository, AutomatedNotificationRepository $anRepository, $type, $id)
    {
        if (isset($type) && isset($id)) {
            if ($type === Broadcast::$BROADCAST_TYPE) {
                $broadcastRepository->setBroadcastAsReadById($id, $this->getUser());
            } else if ($type === AutomatedNotification::$AUTOMATED_NOTIFICATION_TYPE) {
                $anRepository->setNotificationAsReadById($id);
            }
        }
        return new Response();
    }
}
