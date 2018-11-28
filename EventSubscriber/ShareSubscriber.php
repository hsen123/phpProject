<?php
/**
 * Created by PhpStorm.
 * User: aschattney
 * Date: 04.05.18
 * Time: 13:04
 */

namespace App\EventSubscriber;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\DataProvider\ResultCollectionDataProvider;
use App\Entity\Analysis;
use App\Entity\Result;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ShareSubscriber implements EventSubscriberInterface
{

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $checker;
    /**
     * @var ResultCollectionDataProvider
     */
    private $collectionDataProvider;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $checker,
        EntityManagerInterface $entityManager,
        ResultCollectionDataProvider $collectionDataProvider
    )
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->checker = $checker;
        $this->collectionDataProvider = $collectionDataProvider;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['processNewShareEntity', EventPriorities::PRE_VALIDATE]
        ];
    }

    public function processNewShareEntity(GetResponseForControllerResultEvent $event)
    {
        $analysis = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (Request::METHOD_POST === $method && $analysis instanceof Analysis) {
            $this->processNewAnalysis($event, $analysis);
        } else if (Request::METHOD_PUT === $method && $analysis instanceof Analysis) {
            $this->processUpdateAnalysis($event, $analysis);
        }
    }

    private function processNewAnalysis(GetResponseForControllerResultEvent $event, Analysis $analysis)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $analysis->setUser($user);

        $content = $event->getRequest()->getContent();
        $json = json_decode($content, true);
        if (!isset($json[AnalysisSubscriber::ADD_RESULT_IDS_KEY])) {
            $this->sendMissingAddResultIdsKeyResponse($event);
            return;
        }

        if (!is_array($json[AnalysisSubscriber::ADD_RESULT_IDS_KEY])) {
            $key = AnalysisSubscriber::ADD_RESULT_IDS_KEY;
            $this->sendErrorResponse($event, "\"$key\" must be an array");
            return;
        }
        $resultIds = $json[AnalysisSubscriber::ADD_RESULT_IDS_KEY];
        if ($event->getRequest()->get('fromQuery') === 'true') {
            try {
                $results = $this->collectionDataProvider->getCollection(Result::class);
            } catch (ResourceClassNotSupportedException $e) {
                return;
            }

            foreach ($results as $result) {
                /** @var Result $result */
                $resultIds[] = $result->getId();
            }
        }

        $this->addResultsTo($analysis, $resultIds, $event);
    }

    /**
     * @param Analysis $analysis
     * @param array $resultIds
     * @param GetResponseForControllerResultEvent $event
     * @return bool
     */
    private function addResultsTo(Analysis $analysis, array $resultIds, GetResponseForControllerResultEvent $event)
    {
        foreach ($resultIds as $id) {
            /**
             * @var $result Result
             */
            $result = $this->entityManager->find(Result::class, $id);
            if ($result === null) {
                $this->sendResultNotFoundResponse($event, $id);
                return false;
            }
            if ($this->usersAreNotEqual($result, $analysis)) {
                $this->sendResultUnauthorizedForAnalysis($event, $result->getId());
                return false;
            }
            if (false === $analysis->getResults()->contains($result)) {
                $analysis->addResult($result);
            }
        }
        return true;
    }

    private function usersAreNotEqual(Result $result, Analysis $analysis)
    {
        return $result->getCreatedByUser()->getId() !== $analysis->getUser()->getId();
    }

    /**
     * @param Analysis $analysis
     * @param array $resultIds
     * @param GetResponseForControllerResultEvent $event
     * @return bool
     */
    private function removeResultsFrom(Analysis $analysis, array $resultIds, GetResponseForControllerResultEvent $event)
    {
        foreach ($resultIds as $id) {
            $result = $this->entityManager->find(Result::class, $id);
            if ($result === null) {
                $this->sendResultNotFoundResponse($event, $id);
                return false;
            }
            if (true === $analysis->getResults()->contains($result)) {
                $analysis->removeResult($result);
            }
        }
        return true;
    }

    private function processUpdateAnalysis(GetResponseForControllerResultEvent $event, Analysis $analysis)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $isNotAdmin = false === $this->checker->isGranted("ROLE_ADMIN", $user);

        if ($isNotAdmin && $analysis->getUser()->getId() !== $user->getId()) {
            $this->sendErrorResponse($event, "Analysis can not be edited, because it's not owned by the user.");
            return;
        }

        $content = $event->getRequest()->getContent();
        $json = json_decode($content, true);

        if (isset($json[AnalysisSubscriber::ADD_RESULT_IDS_KEY]) && !empty($json[AnalysisSubscriber::ADD_RESULT_IDS_KEY])) {
            $resultIds = $json[AnalysisSubscriber::ADD_RESULT_IDS_KEY];
            if (!is_array($resultIds)) {
                $key = AnalysisSubscriber::ADD_RESULT_IDS_KEY;
                $this->sendErrorResponse($event, "\"$key\" must be an array");
                return;
            }
            if (!$this->addResultsTo($analysis, $resultIds, $event)) {
                return;
            }
        }

        if (isset($json[AnalysisSubscriber::REMOVE_RESULT_IDS_KEY]) && !empty($json[AnalysisSubscriber::REMOVE_RESULT_IDS_KEY])) {
            $resultIds = $json[AnalysisSubscriber::REMOVE_RESULT_IDS_KEY];
            if (!is_array($resultIds)) {
                $key = AnalysisSubscriber::REMOVE_RESULT_IDS_KEY;
                $this->sendErrorResponse($event, "\"$key\" must be an array");
                return;
            }
            if (!$this->removeResultsFrom($analysis, $resultIds, $event)) {
                return;
            }
        }

        if (isset($json["discarded"])) {
            $value = boolval($json["discarded"]);
            if ($isNotAdmin && false === $value && true === $analysis->getDiscarded()) {
                $this->sendErrorResponse($event, "A discarded analysis can not be reactivated");
            } else {
                $analysis->setDiscarded($value);
            }
        }

    }

    private function sendResultUnauthorizedForAnalysis(GetResponseForControllerResultEvent $event, int $resultId)
    {
        $message = sprintf("Author of result with id %d is not the same author of the analysis", $resultId);
        $this->sendErrorResponse($event, $message);
    }

    private function sendMissingAddResultIdsKeyResponse(GetResponseForControllerResultEvent $event)
    {
        $event->setResponse(new JWTAuthenticationFailureResponse("Key \"addResultIds\" is missing", Response::HTTP_BAD_REQUEST));
        $event->stopPropagation();
    }

    private function sendResultNotFoundResponse(GetResponseForControllerResultEvent $event, int $resultId)
    {
        $message = sprintf("result with id %d does not exist", $resultId);
        $this->sendErrorResponse($event, $message);
    }

    private function sendErrorResponse(GetResponseForControllerResultEvent $event, string $message)
    {
        $response = new JWTAuthenticationFailureResponse($message, Response::HTTP_BAD_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

}
