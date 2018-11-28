<?php

namespace App\Controller;

use App\Entity\Result;
use App\Entity\User;
use App\Form\Type\ResultType;
use App\Repository\ResultRepository;
use App\Service\S3ImageService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResultDetailViewController extends Controller
{
    /**
     * @Route("/results/{id}", name="resultDetailView")
     */
    public function resultDetailViewAction(Request $request, $id)
    {
        /** @var Result $result */
        $result = $this->getDoctrine()
            ->getRepository(Result::class)
            ->find($id);

        if (!$result || $result->getDiscardedResult() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->redirect('/');
        }

        $form = $this->createForm(ResultType::class, $result, [
            'action' => $this->generateUrl('updateResult', ['id' => $id]),
            'method' => 'POST',
            'validation_groups' => ['web-edit'],
        ]);
        /** @var User $user */
        $user = $this->getUser();
        if($user->getId() !== $result->getCreatedByUser()->getId() && !$user->hasRole('ROLE_ADMIN')){
            throw $this->createNotFoundException();
        }

        switch ($result->getCitationForm()) {
            default:
                $citation = '';
                $citationShort = ''; break;
            case 0:
                $citation = 'Nitrate';
                $citationShort = 'NO3-'; break;
            case 1:
                $citation = 'pH';
                $citationShort = 'pH';
        }

        return $this->render('result-detail-view/result-detail-view.html.twig', [
            'result' => $result,
            'title' => $result->getTitle(),
            'form' => $form->createView(),
            'citation' => $citation,
            'id' => $result->getId(),
        ]);
    }

    /**
     * @Route("/results/{id}/update", name="updateResult")
     */
    public function updateResultAction(Request $request, $id)
    {
        /** @var Result $result */
        $result = $this->getDoctrine()
            ->getRepository(Result::class)
            ->find($id);

        $form = $this->createForm(ResultType::class, $result, [
            'action' => $this->generateUrl('updateResult', ['id' => $id]),
            'method' => 'POST',
            'validation_groups' => ['web-edit'],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result->setUpdatedAt(time());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Result successfully updated!');
        } else {
            $this->addFlash('success', 'Error updating result!');
        }

        return $this->redirectToRoute('resultDetailView', ['id' => $id]);
    }

    /**
     * @Route("/api/result/download/{id}", name="resultImageDownload")
     * @Method({"GET"})
     * @param ResultRepository $resultRepository
     * @param S3ImageService $imageService
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function resultImageDownload(ResultRepository $resultRepository, S3ImageService $imageService, $id)
    {
        /** @var Result $result */
        $result = $resultRepository->find($id);

        if ($result === null) {
            throw $this->createNotFoundException();
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user->hasRole("ROLE_ADMIN") && $result->getCreatedByUser()->getId() != $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        $image = $imageService->getResultImage($id);
        if ($image === null) {
            throw $this->createNotFoundException();
        }

        $measurementName = $result->getMeasurementName();
        $measurementName = $measurementName !== "" ? $measurementName : $result->getVisibleMeasurementId();

        $response = new Response($image);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "{$measurementName}.jpg"
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'image/*');

        return $response;

    }
}
