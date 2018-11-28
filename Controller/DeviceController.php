<?php
/**
 * Created by PhpStorm.
 * User: aschattney
 * Date: 30.04.18
 * Time: 20:29
 */

namespace App\Controller;


use App\Entity\DeviceEntry;
use App\Repository\DeviceManagementRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class DeviceController extends Controller
{

    /**
     * @Route("/device/revoke", name="revokeDevice")
     * @Method({"DELETE"})
     * @param Request $request
     * @param DeviceManagementRepository $repository
     * @return Response
     */
    public function revokeDevice(Request $request, DeviceManagementRepository $repository)
    {

        $content = $request->getContent();
        if (empty($content)) {
            return Response::create(null, Response::HTTP_BAD_REQUEST);
        }

        $json = json_decode($content, true);

        if (empty($json))
        {
            return Response::create(null, Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var string
         */
        $deviceId = $json[DeviceEntry::PAYLOAD_KEY_DEVICE_ID];
        if ($deviceId === null || empty($deviceId)) {
            return Response::create(null, Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var $user UserInterface
         */
        $user = $this->getUser();

        if ($user === null) {
            return Response::create(null, Response::HTTP_UNAUTHORIZED);
        }

        $repository->revokeAllEntriesFor($user, $deviceId);

        return Response::create(null, Response::HTTP_OK);
    }

}