<?php

declare(strict_types=1);

namespace cdigruttola\CartRuleQuantity\Grid\Data\Factory;

use PrestaShop\PrestaShop\Core\Grid\Data\Factory\DoctrineGridDataFactory;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

final class CartRuleQuantityGridDataFactory implements GridDataFactoryInterface
{
    /**
     * @var DoctrineGridDataFactory
     */
    private $doctrineDataFactory;

    /**
     * @param DoctrineGridDataFactory $doctrineDataFactory
     */
    public function __construct(
        DoctrineGridDataFactory $doctrineDataFactory
    ) {
        $this->doctrineDataFactory = $doctrineDataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(SearchCriteriaInterface $searchCriteria)
    {
        $data = $this->doctrineDataFactory->getData($searchCriteria);

        return new GridData(
            new RecordCollection($data->getRecords()->all()),
            $data->getRecordsTotal(),
            $data->getQuery()
        );
    }
}
