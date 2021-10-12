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

class FoloosiRedirectvalidationModuleFrontController extends ModuleFrontController
{
    
    public function postProcess()
    {
        global $cookie;
        $key_id            = Configuration::get('FOLOOSI_KEY_ID');
        $key_secret        = Configuration::get('FOLOOSI_KEY_SECRET');

        $paymentId = $_POST['transaction_no'];
        
        $status = $_POST['status'];

        $customer_id = $_GET['customer_id'];

        $cart_id = $_GET['cart_id'];

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

        if ($this->module->displayName == 'Foloosi') {
            $authorized = true;
        }

        if (!$authorized)
        {
            die($this->module->getTranslator()->trans('This payment method is not available.', array(), 'Modules.Foloosi.Shop'));
        }

        $customer = new Customer($customer_id);

        if (!Validate::isLoadedObject($customer))
        {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if($status == 'success') {
       
            $cart = $this->context->cart;

            $currency = $this->context->currency;

            $customer = new Customer($customer_id);

            $extraData = array(
                'transaction_id'    =>  $paymentId,
            );

            $ret = $this->module->validateOrder(
                $cart_id, 
                Configuration::get('PS_OS_PAYMENT'), 
                $cart->getOrderTotal(true, Cart::BOTH), 
                $this->module->displayName, 
                NULL, 
                $extraData, 
                (int)$currency->id, 
                false, 
                $customer->secure_key
            );

            Logger::addLog("Payment Successful for Order#".$cart_id.". Foloosi payment id: ".$paymentId . "Ret=" . (int)$ret, 1);

            $query = http_build_query([
                'controller'    => 'order-confirmation',
                'id_cart'       => (int) $cart_id,
                'id_module'     => (int) $this->module->id,
                'id_order'      => $this->module->currentOrder,
                'key'           => $customer->secure_key,
            ], '', '&');

            $url = 'index.php?' . $query;
            Tools::redirect($url);

        } 

        if($status == 'error'){

            $cart = $this->context->cart;

            $currency = $this->context->currency;

            $customer = new Customer($customer_id);

            $extraData = array(
                'transaction_id'    =>  $paymentId,
            );

            $ret = $this->module->validateOrder(
                $cart_id, 
                Configuration::get('PS_OS_ERROR'), 
                $cart->getOrderTotal(true, Cart::BOTH), 
                $this->module->displayName, 
                NULL, 
                $extraData, 
                (int)$currency->id, 
                false, 
                $customer->secure_key
            );

            echo "Payment Failed for Order# ".$cart_id."</br>";
            echo 'Error! Please contact the seller directly for assistance.</br>';
            echo 'Order Id: '.$cart_id.'</br>';
            exit;
        }

        if($status == 'closed'){
            echo "Payment Closed for Order# ".$cart_id;
            echo 'Error! Please contact the seller directly for assistance.</br>';
            echo 'Order Id: '.$cart_id.'</br>';
            exit;
        }
    }
}