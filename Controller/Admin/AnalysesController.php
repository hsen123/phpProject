<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class AnalysesController extends Controller
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/admin/analyses", name="admin-analyses")
     */
    public function indexAction()
    {
        return $this->render('admin/analyses/index.html.twig', [
            'title' => $this->translator->trans('general.page_section.analyses.title')
        ]);
    }
}
