<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Analysis;
use App\Entity\User;
use App\Filter\RequestFilterService;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

final class AnalysisExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var RouterInterface
     */
    private $router;

    /** @var RequestFilterService */
    private $requestFilterService;

    public function __construct(RequestFilterService $requestFilterService, RequestStack $requestStack, RouterInterface $router)
    {
        $this->requestFilterService = $requestFilterService;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ($operationName !== "get") {
            return;
        }
        $this->addWhere($queryNameGenerator, $queryBuilder, $resourceClass);
    }

    /**
     * {@inheritdoc}
     */
    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        if ($operationName !== "get") {
            return;
        }
        $this->addWhere($queryNameGenerator, $queryBuilder, $resourceClass);
    }

    /**
     *
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param QueryBuilder $queryBuilder
     * @param string $resourceClass
     */
    private function addWhere(QueryNameGeneratorInterface $queryNameGenerator, QueryBuilder $queryBuilder, string $resourceClass)
    {
        if ($resourceClass !== Analysis::class) {
            return;
        }
        $user = $this->requestFilterService->getUser();
        if ($user === null || !$user instanceof User) {
            return;
        }

        $extension = $this->buildQueryExtensionsFor($user);
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $extension->extend($queryNameGenerator, $queryBuilder, $rootAlias);
    }

    /**
     * @param User $user
     * @return AnalysisQueryExtension
     */
    private function buildQueryExtensionsFor(User $user)
    {
        $request = $this->requestStack->getCurrentRequest();
        $pathInfo = $request->getPathInfo();
        $requestForSubresource = preg_match('/\/results$/', $pathInfo);
        $requestForCollection = preg_match('/\/analyses$/', $pathInfo);
        $isNotAdmin = false === $this->requestFilterService->isAdmin();

        if ($isNotAdmin) {
            return new DefaultUserAnalysisQueryExtension(
                $requestForCollection ? new CollectionAnalysisQueryExtension(null, $user) :
                    ($requestForSubresource ? new SubresourceAnalysisQueryExtension() : null)
            );
        } else {
            return new AdminAnalysisQueryExtension($this->requestFilterService);
        }
    }

}

interface AnalysisQueryExtension
{
    function extend(QueryNameGeneratorInterface $queryNameGenerator, QueryBuilder $queryBuilder, string $rootAlias);
}

class DefaultUserAnalysisQueryExtension implements AnalysisQueryExtension
{
    /** @var AnalysisQueryExtension */
    var $extension;

    public function __construct(AnalysisQueryExtension $extension = null)
    {
        $this->extension = $extension;
    }

    function extend(QueryNameGeneratorInterface $queryNameGenerator, QueryBuilder $queryBuilder, string $rootAlias)
    {
        if ($this->extension !== null) {
            $this->extension->extend($queryNameGenerator, $queryBuilder, $rootAlias);
        }

        $resultAlias = $queryNameGenerator->generateJoinAlias("r");

        $queryBuilder->andWhere(sprintf("%s.discarded = false", $rootAlias));
        $queryBuilder->addSelect($resultAlias);
        $queryBuilder->leftJoin(
            sprintf("%s.results", $rootAlias),
            $resultAlias,
            Expr\Join::WITH,
            $queryBuilder->expr()->eq(sprintf('%s.discardedResult', $resultAlias), 'false')
        );
    }
}

class AdminAnalysisQueryExtension implements AnalysisQueryExtension
{

    /** @var AnalysisQueryExtension */
    var $extension;

    /** @var RequestFilterService */
    private $requestFilterService;

    public function __construct(RequestFilterService $requestFilterService, AnalysisQueryExtension $extension = null)
    {
        $this->extension = $extension;
        $this->requestFilterService = $requestFilterService;
    }


    function extend(QueryNameGeneratorInterface $queryNameGenerator, QueryBuilder $queryBuilder, string $rootAlias)
    {
        $discardedResultsParamName = $queryNameGenerator->generateParameterName("discardedResultValue");
        $discardedResultsValue = $this->requestFilterService->extractDiscardedResultValue();

        $resultAlias = $queryNameGenerator->generateJoinAlias("r");

        $queryBuilder->addSelect($resultAlias);
        if ($discardedResultsValue !== null) {
            $queryBuilder->leftJoin(
                sprintf("%s.results", $rootAlias),
                $resultAlias,
                Expr\Join::WITH,
                sprintf("%s.discardedResult = :$discardedResultsParamName", $resultAlias)
            )->setParameter($discardedResultsParamName, $discardedResultsValue);
        } else {
            $queryBuilder->leftJoin(
                sprintf("%s.results", $rootAlias),
                $resultAlias
            );
        }

        if ($this->extension !== null) {
            $this->extension->extend($queryNameGenerator, $queryBuilder, $rootAlias);
        }

    }
}

class CollectionAnalysisQueryExtension implements AnalysisQueryExtension
{
    /** @var User */
    var $user;
    /** @var AnalysisQueryExtension */
    var $extension;

    public function __construct(AnalysisQueryExtension $extension = null, User $user)
    {
        $this->extension = $extension;
        $this->user = $user;
    }

    function extend(QueryNameGeneratorInterface $queryNameGenerator, QueryBuilder $queryBuilder, string $rootAlias)
    {
        $userParamName = $queryNameGenerator->generateParameterName("current_user");
        $queryBuilder->andWhere(sprintf("%s.user = :$userParamName", $rootAlias));
        $queryBuilder->setParameter($userParamName, $this->user);
    }
}

class SubresourceAnalysisQueryExtension implements AnalysisQueryExtension
{

    var $extension;

    public function __construct(AnalysisQueryExtension $extension = null)
    {
        $this->extension = $extension;
    }

    function extend(QueryNameGeneratorInterface $queryNameGenerator, QueryBuilder $queryBuilder, string $rootAlias)
    {
        if ($this->extension !== null) {
            $this->extension->extend($queryNameGenerator, $queryBuilder, $rootAlias);
        }
        $queryBuilder->andWhere(sprintf('%s.discardedResult = false', $queryBuilder->getAllAliases()[2]));
    }
}
