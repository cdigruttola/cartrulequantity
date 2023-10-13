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

namespace cdigruttola\CartRuleQuantity\Form\DataHandler;

use cdigruttola\CartRuleQuantity\Entity\CartRuleQuantity;
use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataHandler\FormDataHandlerInterface;
use PrestaShopBundle\Entity\Shop;

if (!defined('_PS_VERSION_')) {
    exit;
}

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
