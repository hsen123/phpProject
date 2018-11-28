<?php

namespace App\Controller;

use App\Entity\BetaFeedback;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class FeedbackController extends Controller
{
    private static $CSV_HEADER = 'Answer 1; Answer 2 (Checkbox 1); Answer 2 (Checkbox 2); Answer 2 (Checkbox 3); Answer 2 (Checkbox 4); Answer 2 (Checkbox 5); Answer 2 Free Text; Answer 3; Answer 4; Answer 5; Answer 6; Answer 7; Answer 8; Answer 9; Answer 10; Answer 11; Answer 12; Answer 13; Answer 14; Answer 15; Answer 16; Answer 17; Answer 18; Answer 19; Answer 20; Answer 21; Answer 22; Answer 23; Answer 24; Answer 25; Answer 26; Answer 27; Answer 28; Answer 29; Answer 30;';
    /**
     * @Route("/api/betafeedback", name="postBetaFeedback")
     * @Method({"POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function postBetaFeedback(Request $request, EntityManagerInterface $em)
    {
        $content = json_decode($request->getContent());

        if (!isset($content->feedback) && !empty($content->feedback)) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
        //check if amount of semicolons is okay
        $allowedAmountSemicolons = substr_count(FeedbackController::$CSV_HEADER, ';');
        $actualAmountSemicolons =  substr_count($content->feedback, ';');
        if($actualAmountSemicolons !== $allowedAmountSemicolons){
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
        $feedbackEntity = new BetaFeedback();
        $feedbackEntity->setFeedback($content->feedback);
        $em->persist($feedbackEntity);
        $em->flush();

        //Everything went all right
        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * @Route("/admin/export/betafeedback", name="exportBetaFeedback")
     * @Method({"GET"})
     */
    public function exportBetaFeedback()
    {
        if (false === $this->getUser()->hasRole('ROLE_ADMIN')) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        /** @var BetaFeedback $completeFeedback[] */
        $completeFeedback = $this->getDoctrine()
            ->getRepository(BetaFeedback::class)
            ->findAll();

        $rows = [FeedbackController::$CSV_HEADER];

        /** @var BetaFeedback $feedback */
        foreach ($completeFeedback as $feedback) {
            $rows[] = $feedback->getFeedback();
        }

        $content = implode("\n", $rows);
        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="betafeedback.csv";');

        return $response;
    }
}
