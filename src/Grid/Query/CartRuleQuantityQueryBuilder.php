<?php
/**
 * Copyright since 2007 Carmine Di Gruttola
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    cdigruttola <c.digruttola@hotmail.it>
 * @copyright Copyright since 2007 Carmine Di Gruttola
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

declare(strict_types=1);

namespace cdigruttola\CartRuleQuantity\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Adapter\Shop\Context;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
