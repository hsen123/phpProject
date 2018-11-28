<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 26.03.2018
 * Time: 08:23.
 */

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Service\NotificationService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RegistrationSubscriber implements EventSubscriberInterface
{
    private $mailer;

    private $manger;

    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var NotificationService
     */
    private $notificationService;

    public function __construct(\Swift_Mailer $mailer, EntityManagerInterface $manager, TranslatorInterface $translator, RouterInterface $router, UserService $userService, NotificationService $notificationService)
    {
        $this->mailer = $mailer;
        $this->manger = $manager;
        $this->translator = $translator;
        $this->router = $router;
        $this->userService = $userService;
        $this->notificationService = $notificationService;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationComplete',
            KernelEvents::VIEW => ['onRegistrationCompleteApiPlatform', EventPriorities::POST_WRITE],
        ];
    }

    public function onRegistrationCompleteApiPlatform(GetResponseForControllerResultEvent $event)
    {
        /** @var User $user */
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User || Request::METHOD_POST !== $method) {
            return;
        }

        $this->notificationService->createWelcomeNotification($user);

        if ($user->isNewsletterActive()) {
            $this->userService->activateNewsletter($user, $event->getRequest());
        }
    }

    public function onRegistrationComplete(FilterUserResponseEvent $event)
    {
        $request = $event->getRequest();
        $formData = $request->request->get('fos_user_registration_form');
        /** @var User $user */
        $user = $event->getUser();

        $this->notificationService->createWelcomeNotification($user);

        if (isset($formData['newsletter']) && 1 === intval($formData['newsletter'])) {
            $this->userService->activateNewsletter($user, $event->getRequest());
        }
    }
}
