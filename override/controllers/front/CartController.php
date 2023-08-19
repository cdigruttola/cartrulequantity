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
class CartController extends CartControllerCore
{
    /** @var Cartrulequantity|false */
    private $module;

    /**
     * @inerhitDoc
     */
    public function init()
    {
        parent::init();

        $this->module = Module::getInstanceByName('cartrulequantity');
        if ('show' === Tools::getValue('action')) {
            if ($this->module && $this->module->active && ($errors = $this->module->checkCartRuleQuantity($this->context->cart))) {
                foreach ($errors as $error) {
                    $this->errors[] = $error;
                }
            }
        }
    }

    public function displayAjaxUpdate()
    {
        if ($this->module && $this->module->active && ($errors = $this->module->checkCartRuleQuantity($this->context->cart))) {
            foreach ($errors as $error) {
                $this->updateOperationError[] = $error;
            }
        }
        parent::displayAjaxUpdate();
    }

    public function displayAjaxRefresh()
    {
        if (Configuration::isCatalogMode()) {
            return;
        }

        ob_end_clean();
        header('Content-Type: application/json');

        $cart_detailed_action = $this->render('checkout/_partials/cart-detailed-actions');
        if ($this->module && $this->module->active && ($errors = $this->module->checkCartRuleQuantity($this->context->cart))) {
            foreach ($errors as $error) {
                $this->errors[] = $error;
            }

            $tpl = $this->context->smarty->createTemplate(
                _PS_MODULE_DIR_ . $this->module->name . '/views/templates/front/cart-detailed-actions.tpl',
                $this->context->smarty
            );
            $cart_detailed_action = $tpl->fetch();
        }

        $this->ajaxRender(json_encode([
            'cart_detailed' => $this->render('checkout/_partials/cart-detailed'),
            'cart_detailed_totals' => $this->render('checkout/_partials/cart-detailed-totals'),
            'cart_summary_items_subtotal' => $this->render('checkout/_partials/cart-summary-items-subtotal'),
            'cart_summary_products' => $this->render('checkout/_partials/cart-summary-products'),
            'cart_summary_subtotals_container' => $this->render('checkout/_partials/cart-summary-subtotals'),
            'cart_summary_totals' => $this->render('checkout/_partials/cart-summary-totals'),
            'cart_detailed_actions' => $cart_detailed_action,
            'cart_voucher' => $this->render('checkout/_partials/cart-voucher'),
            'cart_summary_top' => $this->render('checkout/_partials/cart-summary-top'),
        ]));
    }
}
