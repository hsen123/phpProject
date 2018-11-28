<?php

namespace App\Filter;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Analysis;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use phpDocumentor\Reflection\Types\Integer;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\Query\Expr;

abstract class AdvancedEntityFilter extends RangeFilter
{
    const PARAMETER_EQUAL = 'eq';

    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack, LoggerInterface $logger = null, array $properties = null)
    {
        parent::__construct($managerRegistry, $requestStack, $logger, $properties);
    }

    /**
     * @return string
     */
    protected abstract function getRequestPropertyName();

    /**
     * @return string
     */
    protected abstract function getFilterPropertyName();

    protected abstract function getResourceClass();

    /**
     * @return string
     */
    protected function getAlias() {
        return "o";
    }

    public function getDescription(string $resourceClass): array
    {
        if ($resourceClass !== $this->getResourceClass()) {
            return [];
        }

        $property = $this->getRequestPropertyName();

        $description = [];

        $description += $this->getFilterDescription($property, AdvancedEntityFilter::PARAMETER_EQUAL);
        $description += $this->getFilterDescription($property, RangeFilter::PARAMETER_BETWEEN);
        $description += $this->getFilterDescription($property, RangeFilter::PARAMETER_GREATER_THAN);
        $description += $this->getFilterDescription($property, RangeFilter::PARAMETER_GREATER_THAN_OR_EQUAL);
        $description += $this->getFilterDescription($property, RangeFilter::PARAMETER_LESS_THAN);
        $description += $this->getFilterDescription($property, RangeFilter::PARAMETER_LESS_THAN_OR_EQUAL);

        return $description;
    }

    protected function filterProperty(string $property, $values, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {

        if ($resourceClass !== $this->getResourceClass() || $property !== $this->getFilterPropertyName()) {
            return;
        }

        $alias = $this->getAlias();

        if (true === is_array($values)) {
            foreach ($values as $operator => $value) {
                $this->addWhere(
                    $queryBuilder,
                    $queryNameGenerator,
                    $alias,
                    "",
                    $operator,
                    $value
                );
            }
        } else {
            $this->addWhere(
                $queryBuilder,
                $queryNameGenerator,
                $alias,
                "",
                self::PARAMETER_EQUAL,
                $values
            );
        }


    }


    protected function addWhere(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, $alias, $field, $operator, $value)
    {

        switch ($operator) {
            case RangeFilter::PARAMETER_BETWEEN:
                $rangeValue = explode('..', $value);

                if (2 !== \count($rangeValue)) {
                    $this->logger->notice('Invalid filter ignored', [
                        'exception' => new InvalidArgumentException(sprintf('Invalid format for "[%s]", expected "<min>..<max>"', $operator)),
                    ]);

                    return;
                }

                if (!is_numeric($rangeValue[0]) || !is_numeric($rangeValue[1])) {
                    $this->logger->notice('Invalid filter ignored', [
                        'exception' => new InvalidArgumentException(sprintf('Invalid values for "[%s]" range, expected numbers', $operator)),
                    ]);

                    return;
                }

                $this->modifyQuery($queryBuilder, $queryNameGenerator, RangeFilter::PARAMETER_BETWEEN, $rangeValue);

                break;
            case RangeFilter::PARAMETER_GREATER_THAN:
                if (!is_numeric($value)) {
                    $this->logger->notice('Invalid filter ignored', [
                        'exception' => new InvalidArgumentException(sprintf('Invalid value for "[%s]", expected number', $operator)),
                    ]);

                    return;
                }

                $this->modifyQuery($queryBuilder, $queryNameGenerator, ">", [$value]);

                break;
            case RangeFilter::PARAMETER_GREATER_THAN_OR_EQUAL:
                if (!is_numeric($value)) {
                    $this->logger->notice('Invalid filter ignored', [
                        'exception' => new InvalidArgumentException(sprintf('Invalid value for "[%s]", expected number', $operator)),
                    ]);

                    return;
                }

                $this->modifyQuery($queryBuilder, $queryNameGenerator, ">=", [$value]);

                break;
            case RangeFilter::PARAMETER_LESS_THAN:
                if (!is_numeric($value)) {
                    $this->logger->notice('Invalid filter ignored', [
                        'exception' => new InvalidArgumentException(sprintf('Invalid value for "[%s]", expected number', $operator)),
                    ]);

                    return;
                }

                $this->modifyQuery($queryBuilder, $queryNameGenerator, "<", [$value]);

                break;
            case RangeFilter::PARAMETER_LESS_THAN_OR_EQUAL:
                if (!is_numeric($value)) {
                    $this->logger->notice('Invalid filter ignored', [
                        'exception' => new InvalidArgumentException(sprintf('Invalid value for "[%s]", expected number', $operator)),
                    ]);

                    return;
                }

                $this->modifyQuery($queryBuilder, $queryNameGenerator, "<=", [$value]);

                break;
            case self::PARAMETER_EQUAL:
                if (!is_numeric($value)) {
                    $this->logger->notice('Invalid filter ignored', [
                        'exception' => new InvalidArgumentException(sprintf('Invalid value for "[%s]", expected number', $operator)),
                    ]);

                    return;
                }

                $this->modifyQuery($queryBuilder, $queryNameGenerator, "=", [$value]);
                break;
        }
    }

    protected abstract function modifyQuery(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $operator, array $value);

    protected function getEqualFilterDescription(string $fieldName): array
    {
        return [
            sprintf('%s', $fieldName) => [
                'property' => $fieldName,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }

}