<?php
/**
 * 2007-2015 PrestaShop
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
 * @author PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *           International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class RozetkaPayValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'rozetkapay') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            exit($this->trans('This payment method is not available.', [], 'Modules.RozetkaPay.Shop'));
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

        $this->module->validateOrder((int) $cart->id, Configuration::get('PS_OS_CHEQUE'), $total, $this->module->displayName, null, [], (int) $currency->id, false, $customer->secure_key);
        Tools::redirect(Context::getContext()->link->getModuleLink(
            'rozetkapay',
            'rozetkapay',
            ['action' => 'creatPay', 'id_order' => $this->module->currentOrder]
        ));
    }
}
