<?php

namespace App\Controller;

use App\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResettingController extends Controller
{
    /**
     * Request reset user password: submit form and send email.
     *
     * @Route("/api/password/reset", name="api_password_reset")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sendEmailAction(Request $request)
    {
        $content = $request->getContent();

        if (null === $content) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $params = json_decode($content, true); // 2nd param to get as array

        if (!isset($params['email'])) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $email = $params['email'];

        /** @var $user User */
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);

        if ($user->isAccountUnconfirmedAndConfirmationTimeFrameExpired()) {
            return new JWTAuthenticationFailureResponse('You need to confirm your account.', JsonResponse::HTTP_UNAUTHORIZED);
        }

        $ttl = $this->container->getParameter('fos_user.resetting.retry_ttl');

        if (null !== $user && !$user->isPasswordRequestNonExpired($ttl)) {
            if (null === $user->getConfirmationToken()) {
                /** @var $tokenGenerator TokenGeneratorInterface */
                $tokenGenerator = $this->get('fos_user.util.token_generator');
                $user->setConfirmationToken($tokenGenerator->generateToken());
            }

            $this->get('fos_user.mailer')->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \DateTime());
            $this->get('fos_user.user_manager')->updateUser($user);
        }

        return new JsonResponse();
    }
}
