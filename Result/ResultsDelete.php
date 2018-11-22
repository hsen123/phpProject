<?php

namespace App\Controller\Result;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Entity\Result;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResultsDelete extends Controller
{
    /**
     * @Route(
     *     name="delete_results",
     *     path="/api/results",
     *     defaults={"_api_resource_class"=Result::class, "_api_collection_operation_name"="delete"}
     *     )
     * @Method("DELETE")
     */
    public function __invoke(Paginator $data, Request $request, EntityManagerInterface $em)
    {
        $response = new JsonResponse();

        $idsToBeDeleted = json_decode($request->getContent());

        $allResults = $em->getRepository(Result::class)->findBy(['id' => $idsToBeDeleted]);

        $deletedResults = [];

        foreach ($allResults as $result) {
            foreach ($result->getDynamicShares()->getIterator() as $dynamicShare) {
                $em->remove($dynamicShare);
            }

            $result->setDiscardedResult(true);
            $result->setUpdatedAt(date_create()->getTimestamp());
            array_push($deletedResults, $result->getId());
        }
        $em->flush();
        $response->setContent(json_encode($deletedResults));
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }
}
