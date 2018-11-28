<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class ResultListDiscardedController extends Controller
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
     * @Route("/admin/result-list/discarded", name="admin-result-list-discarded")
     */
    public function indexAction()
    {
        return $this->render('admin/result-list/discarded-results/discarded-result-table.html.twig', [
            'title' => $this->translator->trans('general.page_section.result_list.discarded_result_list')
        ]);
    }
}
