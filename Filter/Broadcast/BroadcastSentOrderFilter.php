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

class BroadcastSentOrderFilter extends AbstractFilter
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

        if ($resourceClass !== Broadcast::class || !is_array($value)) {
            return;
        }

        if ($property !== "order") {
            return;
        }

        foreach ($value as $key => $val) {
            if ($key === BroadcastSentOrderFilter::PROPERTY_NAME && $this->isAscendingOrDescending($val)) {
                $alias = $this->getAlias();
                $queryBuilder->addOrderBy("$alias.sentDate", $val);
                return;
            }
        }

    }

    /**
     * @see \ApiPlatform\Core\Swagger\Serializer\DocumentationNormalizer::getFiltersParameters
     *
     * @param string $resourceClass
     *
     * @return array
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];
        $description[sprintf('order[%s]', BroadcastSentOrderFilter::PROPERTY_NAME)] = [
            'property' => BroadcastSentOrderFilter::PROPERTY_NAME,
            'type' => 'string',
            'required' => false,
        ];
        return $description;
    }
}
