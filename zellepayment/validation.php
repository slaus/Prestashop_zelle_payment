<?php
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../header.php');
include(dirname(__FILE__) . '/zellepayment.php');

if (_PS_VERSION_ > "1.5.0.0") {
    $context = Context::getContext();
    $cart    = $context->cart;
}

$currency  = new Currency(intval(isset($_POST['currency_payement']) ? $_POST['currency_payement'] : $cookie->id_currency));
$total     = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
$customer  = new Customer((int) $cart->id_customer);
$mailVars  = array(
    '{zellepayment_owner}' => Configuration::get('ZELLEPAYMENT_OWNER'),
    '{zellepayment_details}' => nl2br(Configuration::get('ZELLEPAYMENT_DETAILS')),
    '{zellepayment_address}' => nl2br(Configuration::get('ZELLEPAYMENT_ADDRESS'))
);
$id_estado = Configuration::get('ZELLEPAYMENT_STATE1');

$zellepayment = new ZellePayment();

if (_PS_VERSION_ > "1.4.0.0" && _PS_VERSION_ < "1.5.0.0") {
    $zellepayment->validateOrder($cart->id, Configuration::get('ZELLEPAYMENT_STATE1'), $total, $zellepayment->displayName, NULL, $mailVars, $currency->id, false, $customer->secure_key);
}
if (_PS_VERSION_ > "1.5.0.0") {
    $zellepayment->validateOrder((int) $cart->id, Configuration::get('ZELLEPAYMENT_STATE1'), $total, $zellepayment->displayName, NULL, $mailVars, (int) $currency->id, false, $customer->secure_key);
    
}
if (_PS_VERSION_ < "1.4.0.0") {
    
    $zellepayment->validateOrder($cart->id, Configuration::get('ZELLEPAYMENT_STATE1'), $total, $zellepayment->displayName, NULL, $mailVars, $currency->id);
}

$order = new Order($zellepayment->currentOrder);
Tools::redirectLink(__PS_BASE_URI__ . 'order-confirmation.php?id_cart=' . $cart->id . '&id_module=' . $zellepayment->id . '&id_order=' . $zellepayment->currentOrder . '&key=' . $order->secure_key);
?>