<?php

namespace App\EventSubscriber;

use App\Entity\DeviceEntry;
use App\Entity\User;
use App\Exception\ConfirmationException;
use App\Kernel;
use App\Repository\DeviceManagementRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JWTAuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var DeviceManagementRepository
     */
    private $deviceManagementRepository;

    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoder;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage,
        DeviceManagementRepository $deviceManagementRepository,
        UserRepository $userRepository,
        JWTEncoderInterface $jwtEncoder)
    {
        $this->tokenStorage = $tokenStorage;
        $this->deviceManagementRepository = $deviceManagementRepository;
        $this->jwtEncoder = $jwtEncoder;
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
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
            EVENTS::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccessResponse',
            EVENTS::AUTHENTICATION_FAILURE => 'onAuthenticationFailure'
        ];
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        if ($event->getException() instanceof ConfirmationException)
        {
            /** @var ConfirmationException $confirmationException */
            $confirmationException = $event->getException();
            $statusCode = Response::HTTP_UNAUTHORIZED;

            $event->setResponse(JsonResponse::create(array(
                "code" => $statusCode,
                "message" => $confirmationException->getMessage(),
                "confirmationToken" => $confirmationException->getConfirmationToken()

            ), $statusCode));
        }
    }

    /**
     * @param AuthenticationSuccessEvent $event
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {

        $request = $this->requestStack->getCurrentRequest();


        $data = $event->getData();
        $user = $event->getUser();

        if ($user instanceof User) {
            $data['userId'] = $user->getId();
        }

        if (Kernel::isRequestVersion2($request)) {
            $refreshToken = $data[DeviceEntry::PAYLOAD_KEY_REFRESH_TOKEN];
            $payload = $this->jwtEncoder->decode($data[DeviceEntry::PAYLOAD_KEY_ACCESS_TOKEN]);
            $username = $payload[DeviceEntry::PAYLOAD_KEY_USERNAME];

            /** @var User $payloadUser */
            $payloadUser = $this->userRepository->findOneBy(['username' => $username]);

            if (!$payloadUser) {
                throw new NotFoundHttpException('User not found');
            }

            $deviceId = $payload[DeviceEntry::PAYLOAD_KEY_DEVICE_ID];
            $deviceName = $payload[DeviceEntry::PAYLOAD_KEY_DEVICE_NAME];
            $this->deviceManagementRepository->save($payloadUser, $deviceId, $deviceName, $refreshToken);
        }

        $event->setData($data);
    }
}
