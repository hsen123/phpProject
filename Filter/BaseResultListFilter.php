<?php

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;

abstract class BaseResultListFilter extends AbstractFilter
{
    protected function getFieldNames($singleLikeFilter)
    {
        if (array_key_exists('field', $singleLikeFilter)) {
            return $singleLikeFilter['field'];
        }
    }

    protected function getAliasForJoinedEntity($fieldNameInMotherEntity, $queryBuilder)
    {
        $allJoinedEntities = current($queryBuilder->getDQLPart('join'));

        foreach ($allJoinedEntities as $joinEntity) {
            $joinName = $joinEntity->getJoin();
            if ($joinName === $fieldNameInMotherEntity) {
                return $joinEntity->getAlias();
            }
        }
    }
}
