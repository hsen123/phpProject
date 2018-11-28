<?php

namespace App\EventListener;

use App\Entity\DeviceEntry;
use App\Exception\ConfirmationException;
use App\Exception\DeviceLoggedOutException;
use App\Kernel;
use App\Repository\DeviceManagementRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;

class AuthenticationFailureListener
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var DeviceManagementRepository
     */
    private $deviceManagementRepository;

    /**
     * @var Router
     */
    private $router;

    /**
     * @param RequestStack $requestStack
     * @param DeviceManagementRepository $deviceManagementRepository
     */
    public function __construct(Router $router, RequestStack $requestStack, DeviceManagementRepository $deviceManagementRepository)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->deviceManagementRepository = $deviceManagementRepository;
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {

        $request = $this->requestStack->getCurrentRequest();

        // If the login for a device is revoked, the refresh token gets deleted from the table 'refresh_token'.
        // We need to check here for the error explicitly, to distinguish between bad credentials (caused by refresh token not found)
        // and device logged out remotely.
        if ($this->handleLoginRefreshErrorForVersion2($request, $event)) {
            return;
        }

        $ex = $event->getException();

        if ($ex instanceof ConfirmationException) {
            $message = 'You need to confirm your account.';
            $response = new JWTAuthenticationFailureResponse($message, JsonResponse::HTTP_UNAUTHORIZED);
        } else {
            $message = 'Bad credentials, please verify that your username/password are correctly set';
            $response = new JWTAuthenticationFailureResponse($message);
        }

        $event->setResponse($response);
    }

    /**
     * @param Request $request
     * @param AuthenticationFailureEvent $event
     * @return bool
     */
    private function handleLoginRefreshErrorForVersion2(Request $request, AuthenticationFailureEvent $event) {
        if (Kernel::isRequestVersion2($request) && $this->isLoginRefresh($request)) {
            $response = $this->handleLoginRefreshVersion2($request);
            if ($response !== null) {
                $event->setResponse($response);
                return true;
            }
        }
        return false;
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isLoginRefresh(Request $request) {
        $pathInfo = $this->router->generate(Kernel::REFRESH_TOKEN_CONFIG_KEY);
        return $request->getMethod() === Request::METHOD_POST && $request->getPathInfo() === $pathInfo;
    }

    /**
     * @param Request $request
     * @return Response
     */
    private function handleLoginRefreshVersion2(Request $request) {
        $json = $request->getContent();
        if (!empty($json))
        {
            $params = json_decode($json, true);
            $refreshToken = $params[DeviceEntry::PAYLOAD_KEY_REFRESH_TOKEN];
            /**
             * @var $deviceEntry DeviceEntry
             */
            $deviceEntry = $this->deviceManagementRepository->entryByRefreshToken($refreshToken);
            if ($deviceEntry != null && !$deviceEntry->getEnabled()) {
                $message = DeviceLoggedOutException::MESSAGE;
                $response = new JWTAuthenticationFailureResponse($message, JsonResponse::HTTP_UNAUTHORIZED);
                return $response;
            }
        }
        return null;
    }

}
