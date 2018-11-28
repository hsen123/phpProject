<?php

namespace App\EventSubscriber;


use ApiPlatform\Core\Api\OperationType;
use App\Service\ExportService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EncodedResponseSubscriber implements EventSubscriberInterface
{

    const SUPPORTED_FORMATS = [
        'zip', 'xlsx', 'csv'
    ];

    /**
     * @var ExportService
     */
    private $exportService;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(ExportService $exportService, RequestStack $requestStack)
    {
        $this->exportService = $exportService;
        $this->requestStack = $requestStack;
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
            KernelEvents::RESPONSE => 'setZipArchiveFileName',
            KernelEvents::VIEW => 'pipeControllerResultAsResponse'
        ];
    }

    public function pipeControllerResultAsResponse(GetResponseForControllerResultEvent $event) {
        if ($this->isZipRequest($event)) {
            $result = $event->getControllerResult();
            if ($result instanceof Response) {
                $event->setResponse($result);
                $event->setControllerResult(null);
            }
        }
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     * @return bool
     */
    private function isZipRequest(KernelEvent $event) {
        $attributes = $event->getRequest()->attributes;
        if (!$attributes->has('_format')) {
            return false;
        }
        $format = $attributes->get('_format');
        if (false === array_search($format, self::SUPPORTED_FORMATS)) {
            return false;
        }
        if (!$attributes->has("_api_resource_class")) {
            return false;
        }

        $resourceClass = $attributes->get('_api_resource_class');
        $operationType = $this->resolveOperationType($attributes);

        if ($operationType === "") {
            return false;
        }
        if (!$this->exportService->canResolveTitle($resourceClass, $operationType)) {
            return false;
        }

        return $format === 'zip';
    }

    public function setZipArchiveFileName(FilterResponseEvent $event) {

        $attributes = $event->getRequest()->attributes;
        if (!$attributes->has('_format')) {
            return;
        }
        $format = $attributes->get('_format');
        if (false === array_search($format, self::SUPPORTED_FORMATS)) {
            return;
        }
        if (!$attributes->has("_api_resource_class")) {
            return;
        }

        $resourceClass = $attributes->get('_api_resource_class');
        $operationType = $this->resolveOperationType($attributes);

        if ($operationType === "") {
            return;
        }
        if (!$this->exportService->canResolveTitle($resourceClass, $operationType)) {
            return;
        }

        $title = $this->exportService->resolveTitle($resourceClass, $operationType);
        $response = $event->getResponse();
        $response->headers->set("Content-Disposition", "attachment; filename=\"$title.$format\"");
    }

    /**
     * @param ParameterBag $attributes
     * @return string
     */
    private function resolveOperationType(ParameterBag $attributes) {
        if ($attributes->has('_api_collection_operation_name')) {
            return OperationType::COLLECTION;
        } elseif ($attributes->has('_api_subresource_operation_name')) {
            return OperationType::SUBRESOURCE;
        } elseif ($attributes->has('_api_item_operation_name')) {
            return OperationType::ITEM;
        } else {
           return "";
        }
    }
}