<?php

declare(strict_types=1);

namespace cdigruttola\CartRuleQuantity\Form\Provider;

use cdigruttola\CartRuleQuantity\Entity\CartRuleQuantity;
use Doctrine\ORM\EntityRepository;
use PrestaShop\PrestaShop\Adapter\Shop\Context;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataProvider\FormDataProviderInterface;

class CartRuleQuantityFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var Context
     */
    private $shopContext;

    /**
     * Constructor.
     *
     * @param EntityRepository $repository
     * @param Context $shopContext
     */
    public function __construct(
        EntityRepository $repository,
        Context $shopContext
    ) {
        $this->repository = $repository;
        $this->shopContext = $shopContext;
    }

    /**
     * @param mixed $id
     *
     * @return array
     */
    public function getData($id): array
    {
        /** @var CartRuleQuantity $entity */
        $entity = $this->repository->findOneBy(['id' => (int) $id]);

        $shopIds = [];
        $entityData = [];

        foreach ($entity->getShops() as $shop) {
            $shopIds[] = $shop->getId();
        }

        $entityData['shop_association'] = $shopIds;
        $entityData['active'] = $entity->getActive();
        $entityData['name'] = $entity->getName();
        $entityData['quantity'] = $entity->getMultipleQuantityValue();
        $entityData['categories_id'] = $entity->getCategoriesId();

        return $entityData;
    }

    /**
     * @return array
     */
    public function getDefaultData(): array
    {
        return [
            'name' => [],
            'categories_id' => null,
            'active' => false,
            'shop_association' => $this->shopContext->getContextListShopID(),
        ];
    }
}
