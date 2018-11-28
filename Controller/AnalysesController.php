<?php

namespace App\Controller;

use App\Entity\Analysis;
use App\Repository\AnalysisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class AnalysesController extends Controller
{
    /**
     * @Route("/analyses", name="analyses")
     */
    public function analysesAction()
    {
        return $this->render('analyses/index.html.twig', [
            'title' => 'Analyses',
        ]);
    }

    /**
     * @Route("/analyses/{id}", name="analyses_detail")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction($id)
    {
        /** @var AnalysisRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Analysis::class);

        /** @var Analysis $analysis */
        $analysis = $repo->findByUserAndId($this->getUser(), $id);
        if (!$analysis) {
            throw $this->createNotFoundException();
        }

        return $this->render('analyses-detail-view/index.html.twig', [
            'title' => 'Analysis "'.$analysis->getName().'"',
            'analysis' => $analysis,
        ]);
    }
}
