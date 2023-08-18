<?php

declare(strict_types=1);

namespace cdigruttola\CartRuleQuantity\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Adapter\Shop\Context;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

final class CartRuleQuantityQueryBuilder extends AbstractDoctrineQueryBuilder
{
    /**
     * @var Context
     */
    private $shopContext;


    /**
     * Constructor.
     *
     * @param Connection $connection
     * @param $dbPrefix
     * @param Context $shopContext
     */
    public function __construct(Connection $connection, $dbPrefix, Context $shopContext)
    {
        parent::__construct($connection, $dbPrefix);

        $this->shopContext = $shopContext;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return QueryBuilder
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getBaseQuery($searchCriteria->getFilters());
        $qb->select('s.*');
        if (!$this->shopContext->isAllShopContext()) {
            $qb->join('s', $this->dbPrefix . 'cart_rule_quantity_shop', 'ss', 'ss.id_cart_rule_quantity = s.id_cart_rule_quantity')
                ->where('ss.id_shop in (' . implode(', ', $this->shopContext->getContextListShopID()) . ')')
                ->groupBy('s.id_cart_rule_quantity');
        }

        $qb->orderBy(
            $searchCriteria->getOrderBy(),
            $searchCriteria->getOrderWay()
        )
            ->setFirstResult($searchCriteria->getOffset())
            ->setMaxResults($searchCriteria->getLimit());

        $qb->orderBy('id_cart_rule_quantity');

        return $qb;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return QueryBuilder
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getBaseQuery();
        $qb->select('COUNT(DISTINCT s.id_cart_rule_quantity)');
        if (!$this->shopContext->isAllShopContext()) {
            $qb->join('s', $this->dbPrefix . 'cart_rule_quantity_shop', 'ss', 'ss.id_cart_rule_quantity = s.id_cart_rule_quantity')
                ->where('ss.id_shop in (' . implode(', ', $this->shopContext->getContextListShopID()) . ')');
        }

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    private function getBaseQuery(): QueryBuilder
    {
        return $this->connection
            ->createQueryBuilder()
            ->from($this->dbPrefix . 'cart_rule_quantity', 's');
    }
}
