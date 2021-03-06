<?php

namespace App\Filter\Broadcast;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Broadcast;
use App\Filter\RequestFilterService;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class BroadcastSentFilter extends AbstractFilter
{

    const PROPERTY_NAME = "sent";

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
    private function extractAllowedValue(array $value)
    {
        if (is_array($value) && array_key_exists(0, $value)) {
            $filter = $value[0];
            if ($filter["field"] === self::PROPERTY_NAME) {
                if ($filter["value"] === 'true' || $filter["value"] === 'false') {
                    return $filter["value"];
                }
            }
        }
        return null;
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

        if ($resourceClass !== Broadcast::class || !is_array($value)) {
            return;
        }

        if ($property !== "filter") {
            return;
        }

        foreach ($value as $key => $val) {
            $extValue = $this->extractAllowedValue($val);
            if (($key === "eq" || $key === "ne") && $extValue !== null) {
                $eq = $key === "eq";

                $alias = $this->getAlias();
                if ($eq) {
                    $condition = $extValue === 'true' ? 'IS NOT NULL' : 'IS NULL';
                } else {
                    $condition = $extValue == 'true' ? 'IS NULL' : 'IS NOT NULL';
                }
                $queryBuilder->andWhere("$alias.sentDate $condition");
                return;
            }

            return;

        }

    }

    /**
     *
     * @see \ApiPlatform\Core\Swagger\Serializer\DocumentationNormalizer::getFiltersParameters
     *
     * @param string $resourceClass
     *
     * @return array
     */
    public
    function getDescription(string $resourceClass): array
    {
        $description = [];
        $description[sprintf('order[%s]', BroadcastSentFilter::PROPERTY_NAME)] = [
            'property' => BroadcastSentFilter::PROPERTY_NAME,
            'type' => 'string',
            'required' => false,
        ];
        return $description;
    }
}
