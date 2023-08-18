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

namespace cdigruttola\CartRuleQuantity\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PrestaShopBundle\Entity\Shop;

/**
 * @ORM\Entity(repositoryClass="cdigruttola\CartRuleQuantity\Repository\CartRuleQuantityRepository")
 *
 * @ORM\Table()
 */
class CartRuleQuantity
{
    /**
     * @var int
     *
     * @ORM\Id
     *
     * @ORM\Column(name="id_cart_rule_quantity", type="integer")
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var int
     *
     * @ORM\Column(name="multiple_quantity_value", type="integer")
     */
    private $multiple_quantity_value;

    /**
     * @var string
     *
     * @ORM\Column(name="categories_id", type="string")
     */
    private $categories_id;

    /**
     * @ORM\ManyToMany(targetEntity="PrestaShopBundle\Entity\Shop", cascade={"persist"})
     *
     * @ORM\JoinTable(
     *      joinColumns={@ORM\JoinColumn(name="id_cart_rule_quantity", referencedColumnName="id_cart_rule_quantity")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="id_shop", referencedColumnName="id_shop", onDelete="CASCADE")}
     * )
     */
    private $shops;

    public function __construct()
    {
        $this->shops = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): CartRuleQuantity
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return CartRuleQuantity $this
     */
    public function setActive(bool $active): CartRuleQuantity
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return int
     */
    public function getMultipleQuantityValue(): int
    {
        return $this->multiple_quantity_value;
    }

    /**
     * @param int $multiple_quantity_value
     * @return $this
     */
    public function setMultipleQuantityValue(int $multiple_quantity_value): CartRuleQuantity
    {
        $this->multiple_quantity_value = $multiple_quantity_value;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getCategoriesId(): ?array
    {
        if (!empty($this->categories_id)) {
            return explode(',', $this->categories_id);
        }
        return null;
    }

    /**
     * @param array|null $categories_id
     *
     * @return CartRuleQuantity $this
     */
    public function setCategoriesId(?array $categories_id): CartRuleQuantity
    {
        if (!empty($categories_id)) {
            $this->categories_id = implode(',', $categories_id);
        }

        return $this;
    }

    /**
     * @param Shop $shop
     *
     * @return CartRuleQuantity $this
     */
    public function addShop(Shop $shop): CartRuleQuantity
    {
        $this->shops[] = $shop;

        return $this;
    }

    /**
     * @param Shop $shop
     *
     * @return CartRuleQuantity $this
     */
    public function removeShop(Shop $shop): CartRuleQuantity
    {
        $this->shops->removeElement($shop);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getShops(): Collection
    {
        return $this->shops;
    }

    /**
     * @return CartRuleQuantity $this
     */
    public function clearShops(): CartRuleQuantity
    {
        $this->shops->clear();

        return $this;
    }

}
