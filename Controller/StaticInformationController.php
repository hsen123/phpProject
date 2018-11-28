<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class StaticInformationController extends Controller
{
    /**
     * @Route("/register/success", name="registerComplete")
     * @param Request $request
     * @param RouterInterface $router
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderRegisterSuccessPage(Request $request, RouterInterface $router)
    {
        /** @var User $user */
        $user = $this->getUser();
        if(!$user){
            return $this->redirectToRoute("fos_user_security_login");
        }
        $displayName = '' === $user->getDisplayName() || null === $user->getDisplayName() ? $user->getEmail() : $user->getDisplayName();

        return $this->render('success.html.twig', [
            'title' => "Welcome, $displayName!",
        ]);
    }

    /**
     * @Route("/info/terms-of-use", name="infoTermsOfUse")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function termsOfUseAction(Request $request)
    {
        return $this->render('static-info-pages/info-pages/terms-of-use.html.twig', [
            'title' => 'Terms of use',
        ]);
    }

    /**
     * @Route("/info/privacy-statement-newsletter", name="infoPrivacyStatementNewsletter")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function privacyStatementNewsletterAction(Request $request)
    {
        return $this->render('static-info-pages/info-pages/privacy-statement-newsletter.html.twig', [
            'title' => 'Privacy statement',
        ]);
    }

    /**
     * @Route("/info/feedback", name="infoFeedback")
     * @Route("/feedback", name="betaFeedback")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function feedbackAction(Request $request)
    {
        return $this->render('static-info-pages/info-pages/feedback.twig', [
            'title' => 'Beta Feedback',
        ]);
    }

    /**
     * @Route("/info/privacy-statement", name="infoPrivacyStatement")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function privacyStatementAction(Request $request)
    {
        return $this->render('static-info-pages/info-pages/privacy-statement.html.twig', [
            'title' => 'Privacy statement',
        ]);
    }

    /**
     * @Route("/info/imprint", name="infoImprint")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function imprintAction(Request $request)
    {
        return $this->render('static-info-pages/info-pages/imprint.html.twig', [
            'title' => 'Imprint',
        ]);
    }

    /**
     * @Route("/info/how-to", name="infoHowTo")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function howToAction(Request $request)
    {
        return $this->render('static-info-pages/info-pages/how-to.html.twig', [
            'title' => 'How to',
        ]);
    }
}
