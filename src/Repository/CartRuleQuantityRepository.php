<?php

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
