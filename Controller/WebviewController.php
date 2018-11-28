<?php

namespace App\Controller;

use App\Entity\FaqCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class WebviewController extends Controller
{
    /**
     * @Route("/info/webview/help", name="help_webview")
     */
    public function help(Request $request)
    {
        /** @var FaqCategory $allCategories */
        $allCategories = $this->getDoctrine()
            ->getRepository(FaqCategory::class)
            ->findAll();

        $dropDownCategories = [];

        /** @var FaqCategory $category */
        foreach ($allCategories as $category) {
            $dropDownCategories[$category->getName()] = $category->getId();
        }

        $response = $this->render('webview/help.html.twig', [
            'categories' => $allCategories,
            'dropDownCategories' => $dropDownCategories,
        ]);

        $response->setEtag(md5($response->getContent()));
        $response->isNotModified($request);

        return $response;
    }

    /**
     * @Route("/info/webview/about", name="about_webview")
     */
    public function about(Request $request)
    {
        $response = $this->render('webview/about.html.twig', [
            'version_number' => getenv('VERSION_NUMBER'),
        ]);

        $response->setEtag(md5($response->getContent()));
        $response->isNotModified($request);

        return $response;
    }

    /**
     * @Route("/info/webview/newsletter-privacy", name="newsletter_privacy_webview")
     */
    public function newsletterPrivacy(Request $request)
    {
        $response = $this->render('webview/privacy-newsletter.html.twig', [
        ]);

        $response->setEtag(md5($response->getContent()));
        $response->isNotModified($request);

        return $response;
    }

    /**
     * @Route("/info/webview/tos", name="tos_webview")
     */
    public function tos(Request $request)
    {
        $response = $this->render('webview/tos.html.twig', []);

        $response->setEtag(md5($response->getContent()));
        $response->isNotModified($request);

        return $response;
    }

    /**
     * @Route("/info/webview/privacy", name="privacy_webview")
     */
    public function privacy(Request $request)
    {
        $response = $this->render('webview/privacy.html.twig', []);

        $response->setEtag(md5($response->getContent()));
        $response->isNotModified($request);

        return $response;
    }

    /**
     * @Route("/info/webview/imprint", name="imprint_webview")
     */
    public function imprint(Request $request)
    {
        $response = $this->render('webview/imprint.html.twig', []);

        $response->setEtag(md5($response->getContent()));
        $response->isNotModified($request);

        return $response;
    }
}
