<?php

namespace App\Filter;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use App\Entity\User;
use App\Filter\Result\AggregateFilter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RequestFilterService
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;


    /**
     * RequestFilterService constructor.
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface $tokenStorage
     * @param RequestStack $requestStack
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $tokenStorage, RequestStack $requestStack)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return true === $this->authorizationChecker->isGranted("ROLE_ADMIN");
    }

    /**
     * @return bool|null
     */
    private function extractBooleanFilterFromRequest(string $key)
    {
        $request = $this->requestStack->getCurrentRequest();
        $filter = $request->query->get("filter");

        if ($filter === null) {
            return null;
        }

        $value = $this->findFilterValueForField(
            $key,
            AdvancedEntityFilter::PARAMETER_EQUAL,
            $filter
        );

        if ($this->isAdmin() && ($value === "true" || $value === "false")) {
            $value = $value === "true";
        }
        return $value;
    }

    /**
     * @return bool|null
     */
    public function extractAnalysisDiscardedValue()
    {
        return $this->extractBooleanFilterFromRequest("discarded");
    }

    /**
     * @return bool|null
     */
    public function extractDiscardedResultValue()
    {
        return $this->extractBooleanFilterFromRequest("discardedResult");
    }

    public function analysisDiscardedFilterAllowed($value)
    {
        return $this->isAdmin() && $value !== null && is_bool($value);
    }

    public function discardedResultsFilterAllowed($value)
    {
        return $this->isAdmin() && $value !== null && is_bool($value);
    }

    public function extractValueOrNullFrom(array $arr, $key)
    {
        return array_key_exists($key, $arr) ? $arr[$key] : null;
    }

    /**
     * @return bool
     */
    public function isRequestForResultsOfAnAnalysis()
    {
        $request = $this->requestStack->getCurrentRequest();
        $pathInfo = $request->getPathInfo();
        $matches = [];
        $requestForResultsOfAnalysis = preg_match('/\/api\/analyses\/([0-9]+)\/results/', $pathInfo, $matches) === 1;
        return $requestForResultsOfAnalysis;
    }

    /**
     * @return int|null
     */
    public function extractAnalysisIdFromCurrentRequest()
    {
        $request = $this->requestStack->getCurrentRequest();
        $pathInfo = $request->getPathInfo();
        $matches = [];
        $requestForResultsOfAnalysis = preg_match('/\/api\/analyses\/([0-9]+)\/results/', $pathInfo, $matches) === 1;
        return $requestForResultsOfAnalysis ? intval($matches[1]) : null;
    }

    /**
     * @return int|null
     */
    public function extractCitationFormFilter()
    {
        $request = $this->requestStack->getCurrentRequest();
        /** @var array $value */

        $filter = $request->query->get("filter");

        if ($filter === null) {
            return null;
        }

        return $this->findFilterValueForField(
            AggregateFilter::CITATION_FORM_KEY,
            'eq',
            $filter[AdvancedEntityFilter::PARAMETER_EQUAL]
        );
    }

    /**
     * @return array|null
     */
    public function extractCreationDateFilter()
    {
        $request = $this->requestStack->getCurrentRequest();
        $filter = $request->query->get("filter");

        return $this->findMappedFilterValueForField(
            AggregateFilter::CREATION_DATE_KEY,
            [RangeFilter::PARAMETER_GREATER_THAN, RangeFilter::PARAMETER_LESS_THAN],
            $filter
        );
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        $token = $this->tokenStorage->getToken();
        return $token != null ? $token->getUser() : null;
    }

    function findMappedFilterValueForField($name, $operators, $array)
    {
        if (!isset($array) || !is_array($array)) {
            return null;
        }

        $map = [];

        foreach($array as $operator => $operatorFilter) {
            if (false === array_search($operator, $operators)) {
                continue;
            }
            foreach($operatorFilter as $filter) {
                if (!array_key_exists("field", $filter) || !array_key_exists("value", $filter)) {
                    continue;
                }
                if ($filter["value"] === "null" || $filter["value"] === "") {
                    continue;
                }
                if ($filter["field"] === $name) {
                    $map[$operator] = $filter["value"];
                }
            }
        }

        return $map;
    }

    private function findFilterValueForField($name, $operator, $array)
    {
        if ($array === null || !isset($array) || !array_key_exists($operator, $array)) {
            return null;
        }

        $filters = $array[$operator];

        foreach ($filters as $filter) {

            if (!array_key_exists("field", $filter) || !array_key_exists("value", $filter)) {
                continue;
            }
            if ($filter["value"] === "null" || $filter["value"] === "") {
                continue;
            }
            if ($filter["field"] === $name) {
                return $filter["value"];
            }
        }

        return null;
    }
}
