<?php

namespace App\Filter\Analysis;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Analysis;
use App\Filter\RequestFilterService;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AnalysisResultParameterOrderFilter extends AbstractFilter
{

    const PROPERTY_NAME = "parameters";

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var RequestFilterService */
    private $requestFilterService;

    public function __construct(RequestFilterService $requestFilterService, EntityManagerInterface $entityManager, ManagerRegistry $managerRegistry, RequestStack $requestStack, LoggerInterface $logger = null, array $properties = null)
    {
        parent::__construct($managerRegistry, $requestStack, $logger, $properties);
        $this->entityManager = $entityManager;
        $this->requestFilterService = $requestFilterService;
    }

    /**
     * @return string
     */
    private function getAlias()
    {
        return "o";
    }

    /**
     * @param $value
     * @return bool
     */
    private function isAscendingOrDescending(string $value)
    {
        $lower = strtolower($value);
        return $lower === "asc" || $lower === "desc";
    }

    /**
     * Passes a property through the filter.
     *
     * @param string $property
     * @param mixed $value
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param string|null $operationName
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {

        if ($resourceClass !== Analysis::class || !is_array($value)) {
            return;
        }

        if ($property !== "order") {
            return;
        }

        foreach ($value as $key => $val) {
            if ($key === AnalysisResultParameterFilter::PROPERTY_NAME && $this->isAscendingOrDescending($val)) {
                $analysisAlias = $this->getAlias();
                $isAdmin = $this->requestFilterService->isAdmin();
                $discardedResultsValue = $this->requestFilterService->extractDiscardedResultValue();
                $allowDiscardedResultsFilter = $this->requestFilterService->discardedResultsFilterAllowed($discardedResultsValue);

                if ($isAdmin) {
                    if ($allowDiscardedResultsFilter) {
                        $queryBuilder
                            ->addOrderBy("$analysisAlias.countOfPhWithDiscarded", $val)
                            ->addOrderBy("$analysisAlias.countOfNO3WithDiscarded", $val);
                        return;
                    }
                }
                $queryBuilder
                    ->addOrderBy("$analysisAlias.countOfPh", $val)
                    ->addOrderBy("$analysisAlias.countOfNO3", $val);
                return;

            }
        }

    }

    /**
     * Gets the description of this filter for the given resource.
     *
     * Returns an array with the filter parameter names as keys and array with the following data as values:
     *   - property: the property where the filter is applied
     *   - type: the type of the filter
     *   - required: if this filter is required
     *   - strategy: the used strategy
     *   - swagger (optional): additional parameters for the path operation,
     *     e.g. 'swagger' => [
     *       'description' => 'My Description',
     *       'name' => 'My Name',
     *       'type' => 'integer',
     *     ]
     * The description can contain additional data specific to a filter.
     *
     * @see \ApiPlatform\Core\Swagger\Serializer\DocumentationNormalizer::getFiltersParameters
     *
     * @param string $resourceClass
     *
     * @return array
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];
        $description[sprintf('order[%s]', AnalysisResultParameterOrderFilter::PROPERTY_NAME)] = [
            'property' => AnalysisResultParameterOrderFilter::PROPERTY_NAME,
            'type' => 'string',
            'required' => false,
        ];
        return $description;
    }
}
