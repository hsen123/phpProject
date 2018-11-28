<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserFormTypes\UserCompanyType;
use App\Form\Type\UserFormTypes\UserDevicesType;
use App\Form\Type\UserFormTypes\UserPasswordType;
use App\Form\Type\UserFormTypes\UserSegmentType;
use App\Form\Type\UserFormTypes\UserType;
use App\Repository\AchievementRepository;
use App\Repository\AnalysisSnapshotRepository;
use App\Repository\SharedAnalysisRepository;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;


class ProfileController extends Controller
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(EntityManagerInterface $manager, TranslatorInterface $translator, UserRepository $userRepository)
    {
        $this->manager = $manager;
        $this->translator = $translator;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/profile/resend-confirmation", name="profile-resend-confirmation")
     *
     * @param EntityManagerInterface $manager
     * @param LoggerInterface $logger
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function resendConfirmationMailAction(EntityManagerInterface $manager, LoggerInterface $logger)
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->canSendConfirmationMail()) {
            return $this->redirectToRoute('profile');
        }

        $mailer = $this->get('fos_user.mailer');
        $mailer->sendConfirmationEmailMessage($user);

        $user->setLastConfirmationMail(new \DateTime());

        try {
            $this->manager->flush();
            $this->addFlash('success', 'Confirmation email has been sent again!');
        } catch (\Exception $ex) {
            $logger->log(Logger::CRITICAL, $ex->getMessage());
        }

        return $this->redirectToRoute('profile');
    }

    /**
     * @Route("/api/resend-confirmation/{token}", name="api-resend-confirmation-with-token")
     *
     * @param LoggerInterface $logger
     *
     * @param string $token
     * @return JsonResponse
     */
    public function apiResendConfirmationMailWithTokenAction(LoggerInterface $logger, $token)
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['confirmationToken' => $token]);

        if (null === $user) {
            return JsonResponse::create(null, Response::HTTP_NOT_FOUND);
        }

        if (!$user->canSendConfirmationMail()) {
            return JsonResponse::create(null, Response::HTTP_BAD_REQUEST);
        }

        $mailer = $this->get('fos_user.mailer');
        $mailer->sendConfirmationEmailMessage($user);
        $user->setLastConfirmationMail(new \DateTime());

        try {
            $this->manager->flush();
        } catch (\Exception $ex) {
            $logger->log(Logger::CRITICAL, $ex->getMessage());
        }

        return JsonResponse::create();
    }

    /**
     * DEPRECATED!!! This is used only from app versions < 08.08.2018
     *
     *
     * @Route("/api/resend-confirmation", name="api-resend-confirmation")
     *
     * @param LoggerInterface $logger
     *
     * @return JsonResponse
     */
    public function apiResendConfirmationMailAction(LoggerInterface $logger)
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->canSendConfirmationMail()) {
            return new JsonResponse(null, 400);
        }

        $mailer = $this->get('fos_user.mailer');
        $mailer->sendConfirmationEmailMessage($user);

        $user->setLastConfirmationMail(new \DateTime());

        try {
            $this->manager->flush();
        } catch (\Exception $ex) {
            $logger->log(Logger::CRITICAL, $ex->getMessage());
        }

        return new JsonResponse();
    }

    /**
     * @Route("/profile/metadata", name="getMetaData")
     * @Method({"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getMetaDataAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $metaData = json_decode($user->getResultListmetaData());
        $response = new JsonResponse($metaData);

        return $response;
    }

    /**
     * @Route("/profile/metadata", name="setMetaData")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function postMetadataAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $body = $request->getContent();
        if (!empty($body)) {
            $user->setResultListmetaData($body);
            $this->manager->flush();
            $response = new JsonResponse(null, 200);
        } else {
            $response = new JsonResponse(null, 204);
        }

        return $response;
    }

    /**
     * @Route("/profile/metadata/analysis", name="setAnalysisMetaData")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function postAnalysisMetadataAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $body = $request->getContent();
        if (!empty($body)) {
            $user->setAnalysisListMetaData($body);
            $this->manager->flush();
            $response = new JsonResponse(null, 200);
        } else {
            $response = new JsonResponse(null, 204);
        }

        return $response;
    }

    /**
     * @Route("/profile/metadata/user", name="setUserMetaData")
     * @Method({"POST"})
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    public function postUserMetadataAction(Request $request, EntityManagerInterface $manager)
    {
        /** @var User $user */
        $user = $this->getUser();
        $body = $request->getContent();
        if (!empty($body)) {
            $user->setUserListMetaData($body);
            $manager->flush();
            $response = new JsonResponse(null, 200);
        } else {
            $response = new JsonResponse(null, 204);
        }

        return $response;
    }

    /**
     * @Route("/profile/update", name="updateUserData")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateUserData(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user, [
            'action' => $this->generateUrl('updateUserData'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->translator->trans('pages.profile.flashmessages.updated_profile'));
        } else {
            $this->addFlash('danger', $this->translator->trans('pages.profile.flashmessages.update_profile_error'));
        }

        return $this->redirectToRoute('profile');
    }

    /**
     * @Route("/profile/updateCompanyData", name="updateCompanyData")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateCompanyData(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserCompanyType::class, $user, [
            'action' => $this->generateUrl('updateCompanyData'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->translator->trans('pages.profile.flashmessages.updated_profile'));
        } else {
            $this->addFlash('danger', $this->translator->trans('pages.profile.flashmessages.update_profile_error'));
        }

        return $this->redirectToRoute('profile');
    }

    /**
     * @Route("/profile/updateSegmentData", name="updateSegmentData")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateSegmentData(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserSegmentType::class, $user, [
            'action' => $this->generateUrl('updateCompanyData'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->translator->trans('pages.profile.flashmessages.updated_profile'));
        } else {
            $this->addFlash('danger', $this->translator->trans('pages.profile.flashmessages.update_profile_error'));
        }

        return $this->redirectToRoute('profile');
    }

    /**
     * @Route("/profile/updateLoggedInDevices", name="updateLoggedInDevices")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateLoggedInDevices(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserDevicesType::class, $user, [
            'action' => $this->generateUrl('updateLoggedInDevices'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->translator->trans('pages.profile.flashmessages.updated_profile'));
        } else {
            $this->addFlash('danger', $this->translator->trans('pages.profile.flashmessages.update_profile_error'));
        }

        return $this->redirectToRoute('profile');
    }

    /**
     * @Route("/profile/changePassword", name="changePassword")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changePassword(Request $request)
    {
        $user = $this->getUser();

        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);

        $form = $this->createForm(UserPasswordType::class, $user, [
            'action' => $this->generateUrl('changePassword'),
            'method' => 'POST',
        ]);

        $checkPassword = $encoder->isPasswordValid($user->getPassword(), $request->request->get('user_password')['password'], $user->getSalt());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $checkPassword) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->translator->trans('pages.profile.flashmessages.updated_password'));
        } else {
            $this->get('session')->set('isWrongPassword', true);
        }

        return $this->redirectToRoute('profile');
    }

    /**
     * @Route("/manage-share-links", name="manageShareLinks")
     * @Method(methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderManageShareLinksPage(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('manage-share-links/index.html.twig', [
            'title' => $this->translator->trans('header.manage_share_links')
        ]);
    }

    /**
     * @Route("/api/share-links/{shareType}", name="getShareLinksForUser")
     * @Method(methods={"GET"})
     * @param Request $request
     * @param SharedAnalysisRepository $saRepo
     * @param AnalysisSnapshotRepository $asRepo
     * @param SerializerInterface $serializer
     * @param $shareType
     * @return Response
     */
    public function getShareLinksForUser(Request $request, SharedAnalysisRepository $saRepo, AnalysisSnapshotRepository $asRepo, SerializerInterface $serializer, $shareType)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        $pageSize = (int)$request->get('itemsPerPage');
        $page = (int)$request->get('page');
        if (!$page || $page < 1) {
            $page = 0;
        } else {
            $page = $page - 1;
        }
        if (!$pageSize || $pageSize > 100 || $pageSize < 0) {
            $pageSize = 20;
        }
        $payload = ['results' => [], 'totalCount' => 0];
        switch ($shareType) {
            case 'snapshot':
                $payload['results'] = $asRepo->getCombinedSnapshotSharesForUser($user->getId(), $page, $pageSize);
                $payload['totalCount'] = $asRepo->getCombinedSnapshotSharesForUserCount($user->getId());
                break;
            case 'dynamic':
                $payload['results'] = $saRepo->getCombinedDynamicSharesForUser($user->getId(), $page, $pageSize);
                $payload['totalCount'] = $saRepo->getCombinedDynamicSharesForUserCount($user->getId());
        }
        return new Response($serializer->serialize($payload, 'json'));
    }

    /**
     * @Route("/profile/{id}", name="profile", defaults={"id"=null})
     * @Security("id === null or id == user.getId() or is_granted('ROLE_ADMIN')")
     * @param $id
     * @param UserRepository $userRepository
     * @param UserService $userService
     * @param AchievementRepository $achievementRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index($id, UserRepository $userRepository, UserService $userService, AchievementRepository $achievementRepository)
    {
        /** @var User $user */
        $user = $id === null ? $this->getUser() : $userRepository->find($id);
        $maxValues = 15;

        $allAchievements = $achievementRepository->findAll();

        $nullColumns = $userService->getAllNullColumns($user);

        $filledValuesInPercent = (($maxValues - $nullColumns) * 100) / $maxValues;

        $wrongPassword = $this->get('session')->get('isWrongPassword');
        $this->get('session')->set('isWrongPassword', false);

        $userDataForm = $this->createForm(UserType::class, $user, [
            'action' => $this->generateUrl('updateUserData'),
            'method' => 'POST',
        ]);

        $userPasswordForm = $this->createForm(UserPasswordType::class, $user, [
            'action' => $this->generateUrl('changePassword'),
            'method' => 'POST',
        ]);

        $userCompanyForm = $this->createForm(UserCompanyType::class, $user, [
            'action' => $this->generateUrl('updateCompanyData'),
            'method' => 'POST',
        ]);

        $userSegmentForm = $this->createForm(UserSegmentType::class, $user, [
            'action' => $this->generateUrl('updateSegmentData'),
            'method' => 'POST',
        ]);

        if (!empty($user->getSegment())) {
            $segment = 'segment.' . $user->getSegment();
        } else {
            $segment = '';
        }

        $userLoggedInDevicesForm = $this->createForm(UserDevicesType::class, $user, [
            'action' => $this->generateUrl('updateLoggedInDevices'),
            'method' => 'POST'
        ]);

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'segment' => $segment,
            'userDataForm' => $userDataForm->createView(),
            'userPasswordForm' => $userPasswordForm->createView(),
            'userCompanyForm' => $userCompanyForm->createView(),
            'userSegmentForm' => $userSegmentForm->createView(),
            'userLoggedInDevicesForm' => $userLoggedInDevicesForm->createView(),
            'hasLoggedInDevices' => $user->getLoggedInDevices()->count() > 0,
            'filledValuesInPercent' => intval($filledValuesInPercent),
            'resendInterval' => round(intval(getenv('CONFIRMATION_RESEND_TIME_SECONDS')) / 60),
            'wrongPassword' => $wrongPassword,
            'allAchievements' => $allAchievements,
        ]);
    }

}
