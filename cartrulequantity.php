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
if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use cdigruttola\CartRuleQuantity\Installer\CartRuleQuantityInstaller;
use cdigruttola\CartRuleQuantity\Installer\DatabaseYamlParser;
use cdigruttola\CartRuleQuantity\Installer\Provider\DatabaseYamlProvider;
use cdigruttola\CartRuleQuantity\Repository\CartRuleQuantityRepository;
use cdigruttola\CartRuleQuantity\Translations\TranslationDomains;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class Cartrulequantity extends Module
{
    public $multistoreCompatibility = self::MULTISTORE_COMPATIBILITY_YES;

    public function __construct()
    {
        $this->name = 'cartrulequantity';
        $this->author = 'cdigruttola';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->need_instance = 0;

        $this->ps_versions_compliancy = ['min' => '1.7.8.0', 'max' => _PS_VERSION_];

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Cart Rule quantities', [], TranslationDomains::TRANSLATION_DOMAIN_ADMIN);
        $this->description = $this->trans('Cart Rule Quantities allows you to set, for each category, a value whereby the total quantities of products in the specific category in the shopping cart must be multiples of that value.', [], TranslationDomains::TRANSLATION_DOMAIN_ADMIN);
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', [], TranslationDomains::TRANSLATION_DOMAIN_ADMIN);
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install($reset = false)
    {
        $tableResult = true;
        if (!$reset) {
            $tableResult = $this->getInstaller()->createTables();
        }

        return parent::install()
            && $tableResult
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionCartSave');
    }

    public function uninstall($reset = false)
    {
        $tableResult = true;
        if (!$reset) {
            $tableResult = $this->getInstaller()->dropTables();
        }

        return $tableResult && parent::uninstall();
    }

    public function reset()
    {
        return $this->uninstall(true) && $this->install(true);
    }

    public function getContent(): void
    {
        Tools::redirectAdmin(SymfonyContainer::getInstance()->get('router')->generate('cartrulequantity_controller'));
    }

    private function getInstaller(): CartRuleQuantityInstaller
    {
        try {
            $installer = $this->getService('cdigruttola.cartrulequantity.installer');
        } catch (Error $error) {
            $installer = null;
        }

        if (null === $installer) {
            $installer = new CartRuleQuantityInstaller(
                $this->getService('doctrine.dbal.default_connection'),
                new DatabaseYamlParser(new DatabaseYamlProvider($this)),
                $this->context
            );
        }

        return $installer;
    }

    private function getRepository(): ?CartRuleQuantityRepository
    {
        try {
            $entityRepository = $this->getService('cdigruttola.cartrulequantity.repository.cart_rule_quantity');
        } catch (Error $error) {
            $entityRepository = null;
        }

        return $entityRepository;
    }

    /**
     * @template T
     *
     * @param class-string<T>|string $serviceName
     *
     * @return T|object|null
     */
    public function getService($serviceName)
    {
        try {
            return $this->get($serviceName);
        } catch (ServiceNotFoundException|Exception $exception) {
            return null;
        }
    }

    public function hookDisplayHeader()
    {
        $jsUrl = '/modules/' . $this->name . '/views/js/front.js';
        $this->context->controller->registerJavascript(sha1($jsUrl), $jsUrl, ['position' => 'bottom', 'priority' => 80]);
    }

    public function hookActionCartSave($params)
    {
        /** @var Cart $cart */
        $cart = $params['cart'];

        $this->checkCartRuleQuantity($cart);
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     */
    public function checkCartRuleQuantity(Cart $cart): bool
    {
        $entityRepository = $this->getRepository();
        if ($entityRepository === null) {
            return false;
        }
        $rules = $entityRepository->getSimpleActiveCartRuleQuantityByStoreId($this->context->shop->id);
        if (empty($rules)) {
            // TODO check default value
        }

        $products = $cart->getProducts();

        $rules_product_qty = [];
        $rules_products = [];

        foreach ($products as $cart_product) {
            /** @var Product $product */
            $product = new Product($cart_product['id_product']);
            $product_categories = $product->getCategories();
            foreach ($rules as $rule) {
                $rule_categories = explode(',', $rule['categories_id']);
                $intersect = array_intersect($rule_categories, $product_categories);
                if (!empty($intersect)) {
                    if (isset($rules_product_qty[$rule['id']])) {
                        $rules_product_qty[$rule['id']] += $cart_product['cart_quantity'];
                        $rules_products[$rule['id']] = array_merge($rules_products[$rule['id']], [$product->name[$this->context->language->id]]);
                    } else {
                        $rules_product_qty[$rule['id']] = $cart_product['cart_quantity'];
                        $rules_products[$rule['id']] = [$product->name[$this->context->language->id]];
                    }
                }
            }
        }

        if (empty($rules_product_qty)) {
            return false;
        }

        $found_rule = false;
        foreach ($rules as $rule) {
            if ($rules_product_qty[$rule['id']] % $rule['multiple_quantity_value'] !== 0) {
                $this->context->controller->errors[] = $this->trans(
                    'Error in cart due to cart rule quantity, the sum of quantity of product(s) %s must be in multiple of %d',
                    [implode(', ', $rules_products[$rule['id']]), $rule['multiple_quantity_value']],
                    TranslationDomains::TRANSLATION_DOMAIN_FRONT
                );
                $found_rule = true;
            }
        }

        return $found_rule;
    }
}
