<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class ResultListSucessfullController extends Controller
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
     * @Route("/admin/result-list/successful", name="admin-result-list-success")
     */
    public function indexAction()
    {
        return $this->render('admin/result-list/successful-results/successful-result-table.html.twig', [
            'title' => $this->translator->trans('general.page_section.result_list.successful_result_list')
        ]);
    }
}
