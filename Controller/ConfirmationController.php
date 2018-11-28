<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfirmationController extends Controller
{
    /**
     * @Route("/resend/{token}", name="resend-confirmation")
     *
     * @param EntityManagerInterface $manager
     * @param LoggerInterface        $logger
     * @param UserRepository         $userRepository
     * @param string                 $token
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function resend(EntityManagerInterface $manager, LoggerInterface $logger, UserRepository $userRepository, $token = '')
    {
        /** @var User $user */
        $user = $userRepository->findOneBy(['confirmationToken' => $token]);

        if (null === $user) {
            return new Response('', 404);
        }

        if (!$user->canSendConfirmationMail()) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        $mailer = $this->get('fos_user.mailer');
        $mailer->sendConfirmationEmailMessage($user);

        $user->setLastConfirmationMail(new \DateTime());

        try {
            $manager->flush();
        } catch (\Exception $ex) {
            $logger->log(Logger::CRITICAL, $ex->getMessage());
        }

        $this->addFlash('success', 'success.confirmation.mail');

        return $this->redirectToRoute('fos_user_security_login');
    }
}
