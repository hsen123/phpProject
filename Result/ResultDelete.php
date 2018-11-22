<?php

namespace App\Controller\Result;

use App\Entity\Result;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResultDelete extends Controller
{
    /**
     * @Route(
     *     name="delete_result",
     *     path="/api/results/{id}",
     *     defaults={"_api_resource_class"=Result::class, "_api_item_operation_name"="delete"}
     * )
     * @Method({"DELETE"})
     * @param Result $data
     * @param EntityManagerInterface $em
     * @return object
     */
    public function __invoke(Result $data, EntityManagerInterface $em): object
    {
        $response = new JsonResponse();

        $result = $em->getRepository(Result::class)->find($data->getId());

        if (!$result) {
            $response->setStatusCode(Response::HTTP_NO_CONTENT);
        } else {
            foreach ($result->getDynamicShares()->getIterator() as $dynamicShare) {
                $em->remove($dynamicShare);
            }

            $result->setDiscardedResult(true);
            $result->setUpdatedAt(date_create()->getTimestamp());
            $em->flush();
            $response->setContent($result->getId());
            $response->setStatusCode(Response::HTTP_OK);
        }

        return $response;
    }
}
