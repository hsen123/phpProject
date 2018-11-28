<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Exception\ConfirmationException;
use FOS\UserBundle\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

final class UserEmailConfirmationSubscriber implements EventSubscriberInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    private $tokenStorage;

    public function __construct(MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, UserPasswordEncoderInterface $encoder, TokenStorageInterface $tokenStorage)
    {
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->encoder = $encoder;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onUserCreation', EventPriorities::PRE_WRITE],
            KernelEvents::REQUEST => 'checkUserConfirmation',
        ];
    }

    public function checkUserConfirmation(GetResponseEvent $event)
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return;
        }

        $user = $token->getUser();

        if (null === $user || 'anon.' === $user) {
            return;
        }

        if (null !== $user->getConfirmationToken() && time() - $user->getCreated()->getTimestamp() > 86400) {
            $ex = new ConfirmationException('You need to confirm your account.');
            $ex->setUser($user);
            throw $ex;
        }
    }

    /**
     * Sends an email after the user was created.
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function onUserCreation(GetResponseForControllerResultEvent $event)
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (
            !$user instanceof User ||
            Request::METHOD_POST !== $method
        ) {
            return;
        }

        $user->setEnabled(true);
        $user->setPassword($this->encoder->encodePassword($user, $user->getPlainPassword()));

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->mailer->sendConfirmationEmailMessage($user);
    }
}
