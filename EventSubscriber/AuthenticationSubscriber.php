<?php

namespace App\EventSubscriber;

use App\Entity\User;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var EngineInterface
     */
    private $templating;

    public function __construct(RouterInterface $router, TranslatorInterface $translator, EngineInterface $templating)
    {
        $this->router = $router;
        $this->translator = $translator;
        $this->templating = $templating;
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
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
            FOSUserEvents::SECURITY_IMPLICIT_LOGIN => 'onSecurityImplicitLogin',
            FOSUserEvents::REGISTRATION_CONFIRMED => 'onRegistrationComplete',
            FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE => 'onResettingSendEmailInitialize',
        ];
    }

    /**
     * Before any action for password reset happens, this method is called to determine if the account for the user is
     * unconfirmed and the allowed time-frame for using the account in the unconfirmed state has expired. So we override the
     * response here, if the user is not allowed to reset his password, because his account is in this invalid state, does not exist
     * or is marked as deleted.
     * @param GetResponseNullableUserEvent $event
     */
    public function onResettingSendEmailInitialize(GetResponseNullableUserEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();

        if (null !== $user && $user->isAccountUnconfirmedAndConfirmationTimeFrameExpired()) {
            $this->renderConfirmationRequiredView($event, $user);
        } else if (null === $user || null !== $user->getDeleteDate()) {
            $this->renderAccountNotFoundOrDeletedView($event);
        }
    }

    private function renderConfirmationRequiredView(GetResponseNullableUserEvent $event, User $user) {
        $session = $event->getRequest()->getSession();
        $content = $this->templating->render('confirmation/confirmation_message.html.twig', [
            'confirmationToken' => $user->getConfirmationToken(),
            'canSendConfirmationMail' => $user->canSendConfirmationMail()
        ]);
        $session->getFlashBag()->add('authentication', $content);
        $event->setResponse($this->redirectToPasswordResetView());
    }

    private function renderAccountNotFoundOrDeletedView(GetResponseNullableUserEvent $event) {
        $event->getRequest()->getSession()->getFlashBag()->add('danger', $this->translator->trans('general.flashmessage.reset_password'));
        $event->setResponse($this->redirectToPasswordResetView());
    }

    /**
     * @return Response
     */
    private function redirectToPasswordResetView() {
        $url = $this->router->generate('fos_user_resetting_request');
        $response = new RedirectResponse($url);
        return $response;
    }

    public function onRegistrationComplete(FilterUserResponseEvent $event)
    {
        $url = $this->router->generate('registerComplete');
        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        $response->setTargetUrl($url);

        $session = $event->getRequest()->getSession();

        if (!$session instanceof Session) {
            return;
        }

        $session->getFlashBag()->add('mobile', 'confirmation.success');
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();

        if ($token instanceof JWTUserToken) {
            return;
        }

        $session = $event->getRequest()->getSession();

        if (!$session instanceof Session) {
            return;
        }

        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();

        $this->addAuthenticationFlashMessage($user, $session);
    }

    public function onSecurityImplicitLogin(UserEvent $event)
    {
        $session = $event->getRequest()->getSession();

        if (!$session instanceof Session) {
            return;
        }

        /** @var User $user */
        $user = $event->getUser();

        $this->addAuthenticationFlashMessage($user, $session);
    }

    private function addAuthenticationFlashMessage(User $user, Session $session)
    {
        if (null !== $user->getConfirmationToken()) {
            $session->getFlashBag()->add('authentication', 'show-confirmation-modal');
        }
    }
}
