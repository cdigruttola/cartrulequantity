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
