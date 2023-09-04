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

namespace cdigruttola\CartRuleQuantity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class CartRuleQuantityRepository extends EntityRepository
{
    public function getAllIds(): array
    {
        /** @var QueryBuilder $qb */
        $qb = $this
            ->createQueryBuilder('s')
            ->select('s.id')
        ;

        $result = $qb->getQuery()->getScalarResult();

        return array_map(function ($row) {
            return $row['id'];
        }, $result);
    }

    public function getSimpleActiveCartRuleQuantityByStoreId(
        int $idStore,
        bool $activeOnly = true,
        int $limit = 0
    ): array {
        $qb = $this
            ->createQueryBuilder('s')
            ->select('s.id, s.name, s.active, s.multiple_quantity_value, s.categories_id')
            ->join('s.shops', 'ss')
            ->andWhere('ss.id = :storeId')
            ->setParameter('storeId', (int) $idStore);

        if ($activeOnly) {
            $qb->andWhere('s.active = 1');
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getScalarResult();
    }
}
