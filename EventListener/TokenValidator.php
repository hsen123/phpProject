<?php

namespace App\EventListener;

use App\Entity\DeviceEntry;
use App\Entity\User;
use App\Exception\DeviceLoggedOutException;
use App\Kernel;
use App\Repository\DeviceManagementRepository;
use App\Repository\UserRepository;
use Gesdinet\JWTRefreshTokenBundle\Doctrine\RefreshTokenManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TokenValidator
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var RefreshTokenManager
     */
    private $refreshTokenManager;

    /**
     * @var DeviceManagementRepository
     */
    private $deviceManagementRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;


    /**
     * @param RequestStack $requestStack
     * @param RefreshTokenManager $refreshTokenManager
     * @param DeviceManagementRepository $deviceManagementRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        RequestStack $requestStack,
        RefreshTokenManager $refreshTokenManager,
        DeviceManagementRepository $deviceManagementRepository,
        UserRepository $userRepository
    )
    {
        $this->requestStack = $requestStack;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->deviceManagementRepository = $deviceManagementRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Method is called, when a new access token is created
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (Kernel::isRequestVersion2($request)) {
            $this->updatePayloadWithDeviceAttributes($event, $request);
        }
    }

    /**
     * Method is called, when an access token, which was sent in Authorization header of the current request,
     * gets decoded. Check for device logout here.
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {

        $request = $this->requestStack->getCurrentRequest();
        $payload = $event->getPayload();

        $deviceAttributesAvailable = $this->deviceAttributesAvailable($payload);

        // Force logout if access token does not contain device attributes for app client version 2 request
        if (!$deviceAttributesAvailable && Kernel::isRequestVersion2($request)) {
            throw new DeviceLoggedOutException();
        }

        // Abort validation for app client version 1 request
        if (!$deviceAttributesAvailable) {
            return;
        }

        $deviceId = $payload[DeviceEntry::PAYLOAD_KEY_DEVICE_ID];
        $username = $payload[DeviceEntry::PAYLOAD_KEY_USERNAME];

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['username' => $username]);

        /**
         * @var $entry DeviceEntry
         */
        $entry = $this->deviceManagementRepository->entryFor($user, $deviceId);

        $rejectLogin = ($entry === null || !$entry->getEnabled());

        if ($rejectLogin) {
            throw new DeviceLoggedOutException();
        }

    }

    /**
     * Checks if Device-Id and Device-Name are present in the payload. Returns true if both attributes are present.
     * @param $payload array
     * @return bool
     */
    private function deviceAttributesAvailable($payload)
    {
        // Device-Id and Device-Name are not present in payload of access-token, if they installed
        // the analytica version of the app.
        // So skip the validation here, if those attributes are not present in the payload.
        if (!isset($payload[DeviceEntry::PAYLOAD_KEY_DEVICE_ID])) {
            return false;
        }
        if (!isset($payload[DeviceEntry::PAYLOAD_KEY_DEVICE_NAME])) {
            return false;
        }
        return true;
    }

    private function updatePayloadWithDeviceAttributes(JWTCreatedEvent $event, Request $request)
    {
        $deviceId = $request->headers->get(DeviceEntry::HEADER_KEY_DEVICE_ID);
        $deviceName = $request->headers->get(DeviceEntry::HEADER_KEY_DEVICE_NAME);

        $payload = $event->getData();
        $payload[DeviceEntry::PAYLOAD_KEY_DEVICE_ID] = $deviceId;
        $payload[DeviceEntry::PAYLOAD_KEY_DEVICE_NAME] = $deviceName;

        $event->setData($payload);
    }

}