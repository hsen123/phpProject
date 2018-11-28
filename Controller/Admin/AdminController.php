<?php

namespace App\Controller\Admin;

use App\Entity\Broadcast;
use App\Repository\BroadcastRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class AdminController extends Controller
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/admin/notifications", name="admin-notifications")
     */
    public function renderAdminNotifications()
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

    /**
     * @Route("/admin/users", name="admin-users")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderAdminUsersTable()
    {
        return $this->render('admin/users/index.html.twig', [
            'title' => $this->translator->trans('pages.admin_user_table.all_users'),
        ]);
    }
}
