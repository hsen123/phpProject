<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Controller\ShareController;
use App\Entity\Result;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ResultExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RequestStack $requestStack, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $checker, RouterInterface $router)
    {

        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $checker;
        $this->router = $router;
    }

    /**
     * @param QueryBuilder                $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string                      $resourceClass
     * @param string|null                 $operationName
     */
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        $this->addWhere($queryBuilder, $resourceClass);
        if($operationName ==="api_shared_analyses_analysis_results_get_subresource"){
            return;
        }
        $this->addUserAccessControl($queryBuilder, $resourceClass);
    }

    /**
     * @param QueryBuilder                $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string                      $resourceClass
     * @param array                       $identifiers
     * @param string|null                 $operationName
     * @param array                       $context
     */
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ) {
        $this->addWhere($queryBuilder, $resourceClass);
        $this->addUserAccessControl($queryBuilder, $resourceClass);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $resourceClass
     */
    private function addUserAccessControl(QueryBuilder $queryBuilder, string $resourceClass)
    {
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN') || Result::class !== $resourceClass) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->andWhere($rootAlias.'.createdByUser = :user');
        $queryBuilder->setParameter('user', $this->tokenStorage->getToken()->getUser());
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $resourceClass
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass)
    {

        if (Result::class !== $resourceClass) {
            return;
        }

        $isNotAdmin = (false == $this->authorizationChecker->isGranted('ROLE_ADMIN'));
        if ($isNotAdmin) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $this->forceDiscardedState($queryBuilder, $rootAlias, false);
        }
    }

    private function forceDiscardedState(QueryBuilder $queryBuilder, string $rootAlias, bool $value) {
        $queryBuilder->andWhere($rootAlias.'.discardedResult = :discardedResultState');
        $queryBuilder->setParameter('discardedResultState', $value);
    }

}
