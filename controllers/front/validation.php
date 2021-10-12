<?php
/**
 * Foloosi - Payment Gateway
 *
 * Order Validation Controller
 *
 * @author Foloosi
 */

require_once __DIR__.'/../../foloosi-sdk/Foloosi.php';
 
use Foloosi\Api\Api;

class FoloosiValidationModuleFrontController extends ModuleFrontController
{
    
    public function postProcess()
    {
        global $cookie;
        $key_id            = Configuration::get('FOLOOSI_KEY_ID');
        $key_secret        = Configuration::get('FOLOOSI_KEY_SECRET');

        $paymentId = $_REQUEST['transaction_no'];
        $status = $_REQUEST['status'];
       
        $cart = $this->context->cart;

        if (($cart->id_customer === 0) or
            ($cart->id_address_delivery === 0) or
            ($cart->id_address_invoice === 0) or
            (!$this->module->active))
        {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $authorized = false;

        // Edge case when payment method is disabled while payment in progress
        foreach (Module::getPaymentModules() as $module)
        {
            if ($module['name'] == 'foloosi')
            {
                $authorized = true;
                break;
            }
        }
        if (!$authorized)
        {
            die($this->module->getTranslator()->trans('This payment method is not available.', array(), 'Modules.Foloosi.Shop'));
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer))
        {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if($status == 'success') {

            $currency = $this->context->currency;

            $customer = new Customer($cart->id_customer);

            $extraData = array(
                'transaction_id'    =>  $paymentId,
            );

            $ret = $this->module->validateOrder(
                $cart->id, 
                Configuration::get('PS_OS_PAYMENT'), 
                $cart->getOrderTotal(true, Cart::BOTH),
                $this->module->displayName, 
                NULL, 
                $extraData, 
                (int)$currency->id, 
                false, 
                $customer->secure_key
            );

            Logger::addLog("Payment Successful for Order#".$cart->id.". Foloosi payment id: ".$paymentId . "Ret=" . (int)$ret, 1);

            $query = http_build_query([
                'controller'    => 'order-confirmation',
                'id_cart'       => (int) $cart->id,
                'id_module'     => (int) $this->module->id,
                'id_order'      => $this->module->currentOrder,
                'key'           => $customer->secure_key,
            ], '', '&');

            $url = 'index.php?' . $query;
            Tools::redirect($url);

        } 

        if($status == 'failure'){

            $currency = $this->context->currency;

            $customer = new Customer($cart->id_customer);

            $extraData = array(
                'transaction_id'    =>  $paymentId,
            );

            $ret = $this->module->validateOrder(
                $cart->id, 
                Configuration::get('PS_OS_ERROR'), 
                $cart->getOrderTotal(true, Cart::BOTH),
                $this->module->displayName, 
                NULL, 
                $extraData, 
                (int)$currency->id, 
                false, 
                $customer->secure_key
            );

            echo "Payment Failed for Order# ".$cart->id."</br>";
            echo 'Error! Please contact the seller directly for assistance.</br>';
            echo 'Order Id: '.$cart->id.'</br>';
            exit;
        }

        if($status == 'closed'){
            echo "Payment Closed for Order# ".$cart->id;
            echo 'Error! Please contact the seller directly for assistance.</br>';
            echo 'Order Id: '.$cart->id.'</br>';
            exit;
        }
    }
}