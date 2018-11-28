<?php

namespace App\Controller\Admin;

use App\Entity\Analysis;
use App\Entity\Result;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends Controller
{
    /**
     * @Route("/admin/dashboard", name="admin-dashboard")
     */
    public function indexAction()
    {
        $userCount = intval($this->getDoctrine()->getRepository(User::class)->getCountOfUsers()[0][1]);
        $successfullCount = intval($this->getDoctrine()->getRepository(Result::class)->getCountOfSuccessfulResults()[0][1]);
        $discardedCount = intval($this->getDoctrine()->getRepository(Result::class)->getCountOfDiscardedResults()[0][1]);
        $analysesCount = intval($this->getDoctrine()->getRepository(Analysis::class)->getCountOfAnalyses()[0][1]);

        return $this->render('admin/dashboard/index.html.twig', [
            'userCount' => $userCount,
            'successfullCount' => $successfullCount,
            'discardedCount' => $discardedCount,
            'analysesCount' => $analysesCount
        ]);
    }
}
