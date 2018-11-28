<?php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class IdsFilter extends AbstractFilter
{
    /**
     * @param string $resourceClass
     *
     * @return array
     */
    public function getDescription(string $resourceClass): array
    {
        $description['ids'] = [
            'property' => 'ids',
            'type' => 'array',
            'required' => false,
            'swagger' => ['description' => 'Filter to get an subset of selected items of an entity by ID.'],
        ];

        return $description;
    }

    protected function filterProperty(string $property, $ids, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if ('ids' !== $property) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $parameterName = $queryNameGenerator->generateParameterName('ids');

        $queryBuilder->andWhere($rootAlias . ".id IN (:$parameterName)")
            ->setParameter($parameterName, $ids);
    }
}
