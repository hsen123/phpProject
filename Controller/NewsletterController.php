<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewsletterController extends Controller
{
    const NEWSLETTER_CONFIRMED_FLASH = 'newsletter_confirmed_flash';

    /**
     * @Route("/confirm/newsletter/{token}", name="confirm-newsletter")
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $manager
     * @param string $token
     *
     * @return RedirectResponse
     */
    public function confirmNewsLetter(Request $request, UserRepository $userRepository, EntityManagerInterface $manager, $token = '')
    {
        $user = $userRepository->findByToken($token);

        if (null === $user) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        $user->setNewsletterToken(null);
        $user->setNewsletterTokenTime(null);
        $user->setNewsletterSubscriptionDate(new \DateTime());
        $user->setNewsletterActive(true);

        $log = new Log();
        $log->setUser($user);
        $log->setTime(time());
        $log->setType(Log::NEWSLETTER_IN);

        $manager->persist($log);
        $manager->flush();

        $session = $request->getSession();
        if ($session instanceof Session) {
            $session->getFlashBag()->get(UserService::NEWSLETTER_REGISTERED_FLASH);
        }
        $this->addFlash(self::NEWSLETTER_CONFIRMED_FLASH, 'confirmation_newsletter.success');

        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/unsubscribe/newsletter/{token}", name="unsubscribe-newsletter")
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $manager
     * @param string $token
     *
     * @return RedirectResponse
     */
    public function unsubscribeNewsletter(Request $request, UserRepository $userRepository, EntityManagerInterface $manager, $token = '')
    {
        $user = $userRepository->findOneBy(['unsubscribeToken' => $token]);

        if (null === $user) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        $user->setNewsletterActive(false);
        $log = new Log();
        $log->setUser($user);
        $log->setTime(time());
        $log->setType(Log::NEWSLETTER_OUT);

        $manager->persist($log);
        $manager->flush();

        $this->addFlash('success', 'unsubscribe_newsletter');

        return $this->redirectToRoute('fos_user_security_login');
    }

    /**
     * @Route("/api/activate/newsletter", name="activate-newsletter")
     *
     * @param Request $request
     * @param UserService $userService
     *
     * @return JsonResponse
     */
    public function activateNewsletter(Request $request, UserService $userService)
    {
        $userService->activateNewsletter($this->getUser(), $request);

        return new JsonResponse();
    }

    /**
     * @Route("/profile/newsletter", name="updateNewsLetter")
     * @Method({"POST"})
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserService $userService
     * @return RedirectResponse
     */
    public function updateNewsletter(Request $request, EntityManagerInterface $manager, UserService $userService)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($request->request->has('newsletter')) {
            if (!$user->isNewsletterActive()) {
                $userService->activateNewsletter($user, $request);
            }
        } else {
            if ($user->isNewsletterActive()) {
                $this->addFlash('success', 'unsubscribe_newsletter');
                $user->setNewsletterActive(false);
                $log = new Log();
                $log->setUser($user);
                $log->setTime(time());
                $log->setType(Log::NEWSLETTER_OUT);
                $manager->persist($log);
                $manager->flush();
            }
        }

        return $this->redirectToRoute('profile');
    }

    /**
     * @Route("/admin/export/newsletter", name="exportNewsletter")
     * @Method({"GET"})
     * @param UserRepository $userRepository
     * @return JsonResponse|Response
     */
    public function exportNewsletter(UserRepository $userRepository)
    {
        if (false === $this->getUser()->hasRole('ROLE_ADMIN')) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        /** @var User $users [] */
        $users = $userRepository->findBy(['newsletterActive' => true]);

        $rows = ['email;Link to unsubscribe Newsletter;subscriptionTimestamp'];

        /** @var User $user */
        foreach ($users as $user) {
            $subscriptionDate = $user->getNewsletterSubscriptionDate();
            if ($subscriptionDate) {
                $subscriptionDate = $subscriptionDate->getTimestamp();
            } else {
                $subscriptionDate = 'not tracked';
            }
            $rows[] = $user->getEmail() . ';' . $this->generateUrl('unsubscribe-newsletter', ['token' => $user->getUnsubscribeToken()], UrlGeneratorInterface::ABSOLUTE_URL) . ';' . $subscriptionDate;
        }

        $content = implode("\n", $rows);
        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="newsletter.csv";');

        return $response;
    }
}
