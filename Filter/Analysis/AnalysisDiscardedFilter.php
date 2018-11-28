<?php

namespace App\Filter\Analysis;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Analysis;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use function GuzzleHttp\Promise\all;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AnalysisDiscardedFilter extends BooleanFilter
{

    const FILTER_KEY = "discarded";
    const PROPERTY_NAME = "filter";
    const FILTER_ABBREV = "eq";

    /** @var AuthorizationCheckerInterface */
    private $checker;

    /** @var TokenStorageInterface  */
    private $tokenStorage;

    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack, LoggerInterface $logger = null, array $properties = null, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $checker)
    {
        parent::__construct($managerRegistry, $requestStack, $logger, $properties);
        $this->tokenStorage = $tokenStorage;
        $this->checker = $checker;
    }

    protected function filterProperty(string $property, $allFilters, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (AnalysisDiscardedFilter::PROPERTY_NAME !== $property ||
            !array_key_exists(self::FILTER_ABBREV, $allFilters) ||
            $resourceClass !== Analysis::class
        ) {
           return;
        }

        $constraintFilters = $allFilters[self::FILTER_ABBREV];

        foreach($constraintFilters as $filter) {

            $field = $filter["field"];
            if ($field !== AnalysisDiscardedFilter::FILTER_KEY) {
                continue;
            }

            $value = $filter["value"];

            if ($value !== "true" && $value !== "false") {
                continue;
            }

            $token = $this->tokenStorage->getToken();
            if ($token == null) {
                throw new AccessDeniedException();
            }


            if (false === $this->checker->isGranted("ROLE_ADMIN", $token->getUser()) && $value === "true") {
                throw new AccessDeniedException();
            }

            parent::filterProperty($field, $value, $queryBuilder, $queryNameGenerator, $resourceClass, $operationName);

        }

    }


}