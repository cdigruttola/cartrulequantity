<?php

declare(strict_types=1);

namespace cdigruttola\CartRuleQuantity\Form\DataHandler;

use cdigruttola\CartRuleQuantity\Entity\CartRuleQuantity;
use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataHandler\FormDataHandlerInterface;
use PrestaShopBundle\Entity\Shop;

class CartRuleQuantityFormDataHandler implements FormDataHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): int
    {
        $entity = new CartRuleQuantity();

        $entity->setActive($data['active']);
        $entity->setName($data['name']);
        $entity->setMultipleQuantityValue((int) $data['quantity']);
        $entity->setCategoriesId($data['categories_id']);
        $this->addAssociatedShops($entity, $data['shop_association'] ?? null);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data): int
    {
        $entity = $this->entityManager->getRepository(CartRuleQuantity::class)->find($id);

        $entity->setActive($data['active']);
        $entity->setName($data['name']);
        $entity->setMultipleQuantityValue((int) $data['quantity']);
        $entity->setCategoriesId($data['categories_id']);
        $this->addAssociatedShops($entity, $data['shop_association'] ?? null);

        $this->entityManager->flush();

        return $entity->getId();
    }

    /**
     * @param CartRuleQuantity $slider
     * @param array|null $shopIdList
     */
    private function addAssociatedShops(CartRuleQuantity &$slider, array $shopIdList = null): void
    {
        $slider->clearShops();

        if (empty($shopIdList)) {
            return;
        }

        foreach ($shopIdList as $shopId) {
            $shop = $this->entityManager->getRepository(Shop::class)->find($shopId);
            $slider->addShop($shop);
        }
    }
}
