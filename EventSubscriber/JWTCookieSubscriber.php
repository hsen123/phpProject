<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JWTCookieSubscriber implements EventSubscriberInterface
{
    /**
     * @var JWTTokenManagerInterface
     */
    private $JWTTokenManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var JWTEncoderInterface
     */
    private $JWTEncoder;

    public function __construct(JWTTokenManagerInterface $JWTTokenManager, TokenStorageInterface $tokenStorage, JWTEncoderInterface $JWTEncoder)
    {
        $this->JWTTokenManager = $JWTTokenManager;
        $this->tokenStorage = $tokenStorage;
        $this->JWTEncoder = $JWTEncoder;
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
            KernelEvents::REQUEST => 'checkBearer',
            KernelEvents::RESPONSE => 'setBearerCookie',
        ];
    }

    /**
     * Checks if the requests origin is the app
     * @param Request $request
     * @return bool
     */
    private function isRequestOriginApp(Request $request) {
        return strpos($request->getPathInfo(), '/api') === 0;
    }

    public function checkBearer(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $token = $this->tokenStorage->getToken();

        if (null === $token || $token instanceof JWTUserToken) {
            return;
        }

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        if (null === $user || is_string($user)) {
            $request->cookies->remove('BEARER');

            return;
        }

        if ($request->cookies->has('BEARER')) {
            $bearer = $request->cookies->get('BEARER');
            $data = [];

            try {
                $data = $this->JWTEncoder->decode($bearer);
            } catch (\Exception $ex) {
            }

            if (!isset($data['username']) || $data['username'] !== $user->getUsername()) {
                $request->cookies->remove('BEARER');
            }
        }
    }

    public function setBearerCookie(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($this->isRequestOriginApp($request)) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token || $token instanceof JWTUserToken) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        if (null === $user || is_string($user)) {
            return;
        }

        if (!$request->cookies->has('BEARER')) {
            $cookie = $this->createCookie($user);
            $event->getResponse()->headers->setCookie($cookie);
        }
    }

    private function createCookie(User $user)
    {
        $token = $this->JWTTokenManager->create($user);

        return new Cookie('BEARER', $token);
    }
}
