<?php

namespace App\Controller\Admin;

use App\Entity\Broadcast;
use App\Repository\BroadcastRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class AdminNotificationsController extends Controller
{

    /**
     * @Route("/admin/notifications", name="admin-notifications")
     */
    public function indexAction()
    {
        return $this->render('admin/notifications/index.html.twig', [
            'title' => 'Notifications',
        ]);
    }

    /**
     * @Route("/admin/notification/{id}", name="admin-notification-detail")
     * @param $id
     * @param BroadcastRepository $broadcastRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderAdminNotificationDetail($id, BroadcastRepository $broadcastRepository)
    {
        /** @var Broadcast $broadcast */
        $broadcast = $broadcastRepository->findOneBy(['id' => $id]);

        return $this->render('notification/notification-admin-detail.html.twig', [
            'title' => 'Notification - '.$broadcast->getTitle(),
            'broadcast' => $broadcast
        ]);
    }
}
