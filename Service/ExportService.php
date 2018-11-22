<?php

namespace App\Service;


use ApiPlatform\Core\Api\OperationType;
use App\Entity\Analysis;
use App\Entity\Result;
use App\Entity\User;
use App\Repository\AnalysisRepository;
use App\Repository\ResultRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

class ExportService
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var ResultRepository
     */
    private $resultRepository;
    /**
     * @var AnalysisRepository
     */
    private $analysisRepository;

    public function __construct(TranslatorInterface $translator, RequestStack $requestStack, ResultRepository $resultRepository, AnalysisRepository $analysisRepository)
    {
        $this->requestStack = $requestStack;
        $this->resultRepository = $resultRepository;
        $this->analysisRepository = $analysisRepository;
        $this->translator = $translator;
    }

    /**
     * @param $parameterArray
     * @return mixed|string
     */
    public function parameterArrayToString($parameterArray)
    {
        $parameterString = implode(' / ', $parameterArray);

        if (in_array (0, $parameterArray)) {
            $parameterString = str_replace('0', $this->translator->trans('general.citation.0'), $parameterString);
        }

        if (in_array (1, $parameterArray)) {
            $parameterString = str_replace('1', $this->translator->trans('general.citation.1'), $parameterString);
        }

        return $parameterString;
    }

    const EXCEL_FILENAMES = [
        OperationType::ITEM => [
            Result::class => "result",
            Analysis::class => "analysis"
        ],
        OperationType::COLLECTION => [
            Result::class => "results",
            Analysis::class => "analyses",
            User::class => "users"
        ],
        OperationType::SUBRESOURCE => [
            Result::class => "results"
        ]
    ];

    /**
     * Checks if the filename for the zip should be overriden
     * @param string $resourceClass Analysis::class, Result::class, etc.
     * @param string $operationType ApiPlatform\Core\Api\OperationType
     * @return bool
     */
    public function canResolveTitle(string $resourceClass, string $operationType) {
        if ($resourceClass === Analysis::class && $operationType === OperationType::ITEM) {
            return true;
        }
        if ($resourceClass === Result::class) {
            return true;
        }
        return false;
    }

    /**
     * @param string $resourceClass
     * @param string $operationType
     * @return string
     */
    public function resolveTitle(string $resourceClass, string $operationType) {

        $fileNamePrefix = $this->resolveFileNamePrefix($resourceClass, $operationType);

        if ($fileNamePrefix === "") {
            $fileNameResolvable = array_key_exists($operationType, self::EXCEL_FILENAMES) && array_key_exists($resourceClass, self::EXCEL_FILENAMES[$operationType]);
            $fileNamePrefix = $fileNameResolvable ? self::EXCEL_FILENAMES[$operationType][$resourceClass] : "";
        }

        $fileNamePrefix = $fileNamePrefix === "" ? $resourceClass : $fileNamePrefix;

        $currentDateString = (new \DateTime())->format('Y-m-d');
        $title = $fileNamePrefix."_".$currentDateString;

        return $title;
    }

    /**
     * @param $class
     * @return string
     */
    public function resourceClassToIdentifier($class)
    {
        return strtolower(str_replace('App\Entity\\', "", $class));
    }


    /**
     * @param string $resourceClass
     * @param string $operationType
     * @return mixed|string
     */
    private function resolveFileNamePrefix(string $resourceClass, string $operationType) {
        $pathInfo = $this->requestStack->getCurrentRequest()->getPathInfo();
        $matches = [];
        if ($resourceClass === Analysis::class && $operationType === OperationType::ITEM) {
            if (preg_match('#^/api/analyses/(?P<id>[^/\\.]++)(?:\\.(?P<_format>[^/]++))?$#s', $pathInfo, $matches)) {
                $analysisId = $matches["id"];
                /** @var Analysis $analysis */
                $analysis = $this->analysisRepository->find($analysisId);
                return $analysis !== null ? $analysis->getName() : "";
            }
        }

        if ($resourceClass === Result::class && $operationType === OperationType::SUBRESOURCE) {
            if (preg_match('#^/api/analyses/(?P<id>[^/]++)/results(?:\\.(?P<_format>[^/]++))?$#s', $pathInfo, $matches))  {
                $analysisId = $matches["id"];
                /** @var Analysis $analysis */
                $analysis = $this->analysisRepository->find($analysisId);
                return $analysis !== null ? $analysis->getName() : "";
            }
        }

        if ($resourceClass === Result::class && $operationType === OperationType::ITEM) {
            if (preg_match('#^/api/results/(?P<id>[^/\\.]++)(?:\\.(?P<_format>[^/]++))?$#s', $pathInfo, $matches)) {
                $resultId = $matches["id"];
                /** @var Result $result */
                $result = $this->resultRepository->find($resultId);
                return $result !== null ? $result->getMeasurementName() : "";
            }
        }

        return "";
    }

}