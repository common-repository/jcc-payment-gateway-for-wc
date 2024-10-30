<?php
/**
* WC_Gateway_JCCGateway_ class
*/
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
/**
* JCCGateway_ Gateway.
* @class    WC_Gateway_JCCGateway_
*/
class WC_Gateway_JCCGateway_ extends WC_Payment_Gateway {
/**
* Payment gateway instructions.
* @var string
*
*/
protected $instructions;
/**
* Whether the gateway is visible for non-admin users.
* @var boolean
*
*/
public $id = 'jccgateway';
public $module_version = "5.1.6";
public $has_fields;
public $supports;
public $method_title;
public $method_description;
public $title;
public $description;
public $merchant;
public $password;
public $test_mode;
public $stage_mode;
public $order_status_paid;
public $send_order;
public $tax_system;
public $tax_type;
public $success_url;
public $fail_url;
public $backToShopUrl;
public $backToShopUrlName;
public $versionFfd;
public $paymentMethodType;
public $paymentObjectType;
public $paymentObjectType_delivery;
public $pData;
public $logging;
public $orderNumberById;
public $allowCallbacks;
public $enable_for_methods;
public $test_url;
public $prod_url;
public $cacert_path;
public function __construct() {
$this->icon               = plugin_dir_url(__FILE__) . '../assets/images/logo.png';
$this->has_fields         = false;
$this->supports           = array(
'products',
'subscriptions',
'subscription_cancellation',
'subscription_suspension',
'subscription_reactivation',
'subscription_amount_changes',
'subscription_date_changes',
'multiple_subscriptions'
);
if (defined('JCCGATEWAY_ENABLE_REFUNDS') && JCCGATEWAY_ENABLE_REFUNDS == true) {
$this->supports[] = 'refunds';
}
$this->method_title       = JCCGATEWAY_PAYMENT_NAME;
$this->method_description = __( 'Allows jccgateway payments.', 'woocommerce-gateway-jccgateway' );
$this->init_form_fields();
$this->init_settings();
$this->title                    = $this->get_option( 'title' );
$this->description              = $this->get_option( 'description' );
$this->instructions             = $this->get_option( 'instructions', $this->description );
$this->merchant = $this->get_option('merchant');
$this->password = $this->get_option('password');
if (!empty($this->get_option('token'))) {
$decoded_credentials = base64_decode($this->get_option('token'));
list($l, $p) = explode(':', $decoded_credentials);
$this->merchant = $l;
$this->password = $p;
}
$this->test_mode = $this->get_option('test_mode');
$this->stage_mode = $this->get_option('stage_mode');
$this->description = $this->get_option('description');
$this->order_status_paid = $this->get_option('order_status_paid');
$this->send_order = $this->get_option('send_order');
$this->tax_system = $this->get_option('tax_system');
$this->tax_type = $this->get_option('tax_type');
$this->success_url = $this->get_option('success_url');
$this->fail_url = $this->get_option('fail_url');
$this->backToShopUrl = $this->get_option('backToShopUrl');
$this->backToShopUrlName = $this->get_option('backToShopUrlName');
$this->versionFfd = $this->get_option('versionFfd');
$this->paymentMethodType = $this->get_option('paymentMethodType');
$this->paymentObjectType = $this->get_option('paymentObjectType');
$this->paymentObjectType_delivery = $this->get_option('paymentMethodType_delivery');
$this->pData = get_plugin_data(__FILE__);
$this->logging = JCCGATEWAY_ENABLE_LOGGING;
$this->orderNumberById = true; //false - must be installed WooCommerce Sequential Order Numbers
$this->allowCallbacks = defined('JCCGATEWAY_ENABLE_CALLBACK') ? JCCGATEWAY_ENABLE_CALLBACK : false;
$this->enable_for_methods = $this->get_option('enable_for_methods', array());
$this->test_url = JCCGATEWAY_TEST_URL;
$this->prod_url = JCCGATEWAY_PROD_URL;
$this->cacert_path = null;
if (defined('JCCGATEWAY_PROD_URL_ALTERNATIVE_DOMAIN') && defined('JCCGATEWAY_PROD_URL_ALT_PREFIX')) {
if (substr($this->merchant, 0, strlen(JCCGATEWAY_PROD_URL_ALT_PREFIX)) == JCCGATEWAY_PROD_URL_ALT_PREFIX) {
$pattern = '/^https:\/\/[^\/]+/';
$this->prod_url = preg_replace($pattern, rtrim(JCCGATEWAY_PROD_URL_ALTERNATIVE_DOMAIN, '/'), $this->prod_url);
} else {
$this->allowCallbacks = false;
}
}
add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
add_action( 'woocommerce_scheduled_subscription_payment_jccgateway', array( $this, 'process_subscription_payment' ), 10, 2 );
add_action( 'woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
add_action( 'woocommerce_api_jccgateway', array($this, 'webhook_result'));
add_action( 'woocommerce_before_checkout_form', array($this, 'display_custom_error_message'), 12 );
}
public function display_custom_error_message() {
if ( WC()->session->get( 'custom_error_message' ) ) {
wc_print_notices();
WC()->session->__unset( 'custom_error_message' );
}
}
public function init_form_fields() {
$shipping_methods = array();
if (is_admin())
foreach (WC()->shipping()->load_shipping_methods() as $method) {
$shipping_methods[$method->id] = $method->get_method_title();
}
$form_fields = array(
'enabled' => array(
'title' => __('Enable/Disable', 'wc-' . $this->id . '-text-domain'),
'type' => 'checkbox',
'label' => __('Enable', 'woocommerce') . " " . JCCGATEWAY_PAYMENT_NAME ,
'default' => 'yes'
),
'title' => array(
'title' => __('Title', 'wc-' . $this->id . '-text-domain'),
'type' => 'text',
'description' => __('Title displayed to your customer when they make their order.', 'wc-' . $this->id . '-text-domain'),
),
'merchant' => array(
'title' => __('Login-API', 'wc-' . $this->id . '-text-domain'),
'type' => 'text',
'default' => '',
),
'password' => array(
'title' => __('Password', 'wc-' . $this->id . '-text-domain'),
'type' => 'password',
'default' => '',
),
'test_mode' => array(
'title' => __('Test mode', 'wc-' . $this->id . '-text-domain'),
'type' => 'checkbox',
'label' => __('Enable', 'woocommerce'),
'description' => __('In this mode no actual payments are processed.', 'wc-' . $this->id . '-text-domain'),
'default' => 'no'
),
'stage_mode' => array(
'title' => __('Payments type', 'wc-' . $this->id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => 'one-stage',
'options' => array(
'one-stage' => __('One-phase payments', 'wc-' . $this->id . '-text-domain'),
'two-stage' => __('Two-phase payments', 'wc-' . $this->id . '-text-domain'),
),
),
);
if (defined('JCCGATEWAY_API_VERSION') && JCCGATEWAY_API_VERSION >= 2) {
$settings = get_option('woocommerce_jccgateway_settings');
$merchant = isset($settings['merchant']) ? $settings['merchant'] : '';
$password = isset($settings['password']) ? $settings['password'] : '';
$token_default = '';
if (!empty($merchant) && !empty($password)) {
$token_default = base64_encode($merchant . ":" . $password);
}
$token_field = array(
'title' => __('Token', 'wc-' . $this->id . '-text-domain'),
'type' => 'text',
'default' => $token_default,
'css'         => 'width:80%;',
);
$merchant_key = array_search('merchant', array_keys($form_fields), true);
$form_fields = array_merge(
array_slice($form_fields, 0, $merchant_key),
array('token' => $token_field),
array_slice($form_fields, $merchant_key)
);
unset($form_fields['merchant']);
unset($form_fields['password']);
}
$form_fields_ext1 = array(
'description' => array(
'title' => __('Description', 'wc-' . $this->id . '-text-domain'),
'type' => 'textarea',
'description' => __('Payment description displayed to your customer.', 'wc-' . $this->id . '-text-domain'),
'css'         => 'width:80%;',
),
'order_status_paid' => array(
'title' => __('Paid order status', 'wc-' . $this->id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => 'wc-completed',
'options' => array(
'wc-processing' => _x('Processing', 'Order status', 'woocommerce'),
'wc-completed' => _x('Completed', 'Order status', 'woocommerce'),
),
),
'success_url' => array(
'title' => __('successUrl', 'wc-' . $this->id . '-text-domain'),
'type' => 'text',
'description' => __('Page your customer will be redirected to after a <b>successful payment</b>.<br/>Leave this field blank, if you want to use default settings.', 'wc-' . $this->id . '-text-domain'),
),
'fail_url' => array(
'title' => __('failUrl', 'wc-' . $this->id . '-text-domain'),
'type' => 'text',
'description' => __('Page your customer will be redirected to after an <b>unsuccessful payment</b>.<br/>Leave this field blank, if you want to use default settings.', 'wc-' . $this->id . '-text-domain'),
),
);
$form_fields = array_merge($form_fields, $form_fields_ext1);
if (defined('JCCGATEWAY_ENABLE_BACK_URL_SETTINGS') && JCCGATEWAY_ENABLE_BACK_URL_SETTINGS === true) {
$form_fields_backToShopUrlSettings = array(
'backToShopUrl' => array(
'title' => __('Back to shop URL', 'wc-' . $this->id . '-text-domain'),
'type' => 'text',
'default' => '',
'description' => __('Adds URL for checkout page button that will take a cardholder back to the assigned merchant web-site URL.', 'wc-' . $this->id . '-text-domain'),
),
);
$form_fields = array_merge($form_fields, $form_fields_backToShopUrlSettings);
}
if (defined('JCCGATEWAY_ENABLE_CART_OPTIONS') && JCCGATEWAY_ENABLE_CART_OPTIONS == true) {
$form_fields_cartOptions = array(
'send_order' => array(
'title' => __("Send cart data<br />(including customer info)", 'wc-' . $this->id . '-text-domain'),
'type' => 'checkbox',
'label' => __('Enable', 'woocommerce'),
'description' => __('If this option is enabled order receipts will be created and sent to your customer and to the revenue service.', 'wc-' . $this->id . '-text-domain'),
'default' => 'no'
),
'tax_system' => array(
'title' => __('Tax system', 'wc-' . $this->id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => '0',
'options' => array(
'0' => __('General', 'wc-' . $this->id . '-text-domain'),
'1' => __('Simplified, income', 'wc-' . $this->id . '-text-domain'),
'2' => __('Simplified, income minus expences', 'wc-' . $this->id . '-text-domain'),
'3' => __('Unified tax on imputed income', 'wc-' . $this->id . '-text-domain'),
'4' => __('Unified agricultural tax', 'wc-' . $this->id . '-text-domain'),
'5' => __('Patent taxation system', 'wc-' . $this->id . '-text-domain'),
),
),
'tax_type' => array(
'title' => __('Default VAT', 'wc-' . $this->id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => '0',
'options' => array(
'0' => __('No VAT', 'wc-' . $this->id . '-text-domain'),
'1' => __('VAT 0%', 'wc-' . $this->id . '-text-domain'),
'2' => __('VAT 10%', 'wc-' . $this->id . '-text-domain'),
'3' => __('VAT 18%', 'wc-' . $this->id . '-text-domain'),
'6' => __('VAT 20%', 'wc-' . $this->id . '-text-domain'),
'4' => __('VAT applicable rate 10/110', 'wc-' . $this->id . '-text-domain'),
'5' => __('VAT applicable rate 18/118', 'wc-' . $this->id . '-text-domain'),
'7' => __('VAT applicable rate 20/120', 'wc-' . $this->id . '-text-domain'),
),
),
'versionFfd' => array(
'title' => __('Fiscal document format', 'wc-' . $this->id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => 'v1_05',
'options' => array(
'v1_05' => __('v1.05', 'wc-' . $this->id . '-text-domain'),
'v1_2' => __('v1.2', 'wc-' . $this->id . '-text-domain'),
),
'description' => __('Also specify the version in your bank web account and in your fiscal service web account.', 'wc-' . $this->id . '-text-domain'),
),
'paymentMethodType' => array(
'title' => __('Payment type', 'wc-' . $this->id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => '1',
'options' => array(
'1' => __('Full prepayment', 'wc-' . $this->id . '-text-domain'),
'2' => __('Partial prepayment', 'wc-' . $this->id . '-text-domain'),
'3' => __('Advance payment', 'wc-' . $this->id . '-text-domain'),
'4' => __('Full payment', 'wc-' . $this->id . '-text-domain'),
'5' => __('Partial payment with further credit', 'wc-' . $this->id . '-text-domain'),
'6' => __('No payment with further credit', 'wc-' . $this->id . '-text-domain'),
'7' => __('Payment on credit', 'wc-' . $this->id . '-text-domain'),
),
),
'paymentMethodType_delivery' => array(
'title' => __('Payment type for delivery', 'wc-' . $this->id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => '1',
'options' => array(
'1' => __('Full prepayment', 'wc-' . $this->id . '-text-domain'),
'2' => __('Partial prepayment', 'wc-' . $this->id . '-text-domain'),
'3' => __('Advance payment', 'wc-' . $this->id . '-text-domain'),
'4' => __('Full payment', 'wc-' . $this->id . '-text-domain'),
'5' => __('Partial payment with further credit', 'wc-' . $this->id . '-text-domain'),
'6' => __('No payment with further credit', 'wc-' . $this->id . '-text-domain'),
'7' => __('Payment on credit', 'wc-' . $this->id . '-text-domain'),
),
),
'paymentObjectType' => array(
'title' => __('Type of goods and services', 'wc-' . $this->id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => '1',
'options' => array(
'1' => __('Goods', 'wc-' . $this->id . '-text-domain'),
'2' => __('Excised goods', 'wc-' . $this->id . '-text-domain'),
'3' => __('Job', 'wc-' . $this->id . '-text-domain'),
'4' => __('Service', 'wc-' . $this->id . '-text-domain'),
'5' => __('Stake in gambling', 'wc-' . $this->id . '-text-domain'),
'7' => __('Lottery ticket', 'wc-' . $this->id . '-text-domain'),
'9' => __('Intellectual property provision', 'wc-' . $this->id . '-text-domain'),
'10' => __('Payment', 'wc-' . $this->id . '-text-domain'),
'11' => __("Agent's commission", 'wc-' . $this->id . '-text-domain'),
'12' => __('Combined', 'wc-' . $this->id . '-text-domain'),
'13' => __('Other', 'wc-' . $this->id . '-text-domain'),
),
),
);
$form_fields = array_merge($form_fields, $form_fields_cartOptions);
}
$this->form_fields = $form_fields;
}
public function is_available() {
return parent::is_available();
}
public function process_admin_options() {
if ($this->allowCallbacks == false) {
$this->writeLog("Nothing to update: " . __LINE__);
return parent::process_admin_options();
}
if (isset($_POST['woocommerce_jccgateway_test_mode'])) {
$action_adr = $this->test_url;
$gate_url = str_replace("payment/rest", "mportal/mvc/public/merchant/update", $action_adr);
if (defined('JCCGATEWAY_TEST_URL_ALTERNATIVE_DOMAIN')) {
$pattern = '/^https:\/\/[^\/]+/';
$gate_url = preg_replace($pattern, rtrim(JCCGATEWAY_TEST_URL_ALTERNATIVE_DOMAIN, '/'), $gate_url);
}
} else {
$action_adr = $this->prod_url;
$gate_url = str_replace("payment/rest", "mportal/mvc/public/merchant/update", $action_adr);
if (defined('JCCGATEWAY_PROD_URL_ALTERNATIVE_DOMAIN')) {
$pattern = '/^https:\/\/[^\/]+/';
$gate_url = preg_replace($pattern, rtrim(JCCGATEWAY_PROD_URL_ALTERNATIVE_DOMAIN, '/'), $gate_url);
}
}
$gate_url .= substr($this->merchant, 0, -4);
$callback_addresses_string = get_option('siteurl') . '?wc-api=jccgateway' . '&action=callback';
if ($this->allowCallbacks !== false) {
$response = $this->_updateGatewayCallback($this->merchant, $this->password, $gate_url, $callback_addresses_string, null);
if (JCCGATEWAY_ENABLE_LOGGING === true) {
$this->writeLog("REQUEST:\n". $gate_url . "\n[callback_addresses_string]: " . $callback_addresses_string . "\nRESPONSE:\n" . $response);
}
}
parent::process_admin_options();
}
public function _updateGatewayCallback($login, $password, $action_address, $callback_addresses_string, $ca_info = null)
{
$headers = array(
'Content-Type:application/json',
'Authorization: Basic ' . base64_encode($login . ":" . $password)
);
$data['callbacks_enabled'] = true;
$data['callback_type'] = "STATIC";
$data['callback_addresses'] = $callback_addresses_string;
$data['callback_http_method'] = "GET";
$data['callback_operations'] = "deposited,approved,declinedByTimeout";
$response = $this->_sendGatewayData(json_encode($data), $action_address, $headers, $ca_info);
return $response;
}
public function _sendGatewayData($data, $action_address, $headers = array(), $ca_info = null)
{
$curl_opt = array(
CURLOPT_HTTPHEADER => $headers,
CURLOPT_VERBOSE => true,
CURLOPT_SSL_VERIFYHOST => false,
CURLOPT_URL => $action_address,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_POST => true,
CURLOPT_POSTFIELDS => $data,
CURLOPT_HEADER => true,
);
$ssl_verify_peer = false;
if ($ca_info != null) {
$ssl_verify_peer = true;
$curl_opt[CURLOPT_CAINFO] = $ca_info;
}
$curl_opt[CURLOPT_SSL_VERIFYPEER] = $ssl_verify_peer;
$ch = curl_init();
curl_setopt_array($ch, $curl_opt);
$response = curl_exec($ch);
if ($response === false) {
$this->writeLog("The payment gateway is returning an empty response.");
}
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);
return substr($response, $header_size);
}
function writeLog($var, $info = true) {
if ($this->test_mode != "yes") {
}
$information = "";
if ($var) {
if ($info) {
$information = "\n\n";
$information .= str_repeat("-=", 64);
$information .= "\nDate: " . date('Y-m-d H:i:s');
$information .= "\nWordpress version " . get_bloginfo('version') . "; Woocommerce version: " . wpbo_get_woo_version_number() . "\n";
}
$result = $var;
if (is_array($var) || is_object($var)) {
$result = "\n" . print_r($var, true);
}
$result .= "\n\n";
$path = dirname(__FILE__) . '/../logs/wc_jccgateway_' . date('Y-m') . '.log';
error_log($information . $result, 3, $path);
return true;
}
return false;
}
public function process_payment( $order_id ) {
$order = wc_get_order($order_id);
if (!empty($_GET['pay_for_order']) && $_GET['pay_for_order'] == 'true') {
$this->generate_form($order_id);
exit();
}
$pay_now_url = $order->get_checkout_payment_url(true);
return array(
'result' => 'success',
'redirect' => $pay_now_url
);
}
public function generate_form($order_id)
{
$order = wc_get_order($order_id);
$amount = $order->get_total() * 100;
$coupons = array();
global $woocommerce;
if (!empty($woocommerce->cart->applied_coupons)) {
foreach ($woocommerce->cart->applied_coupons as $code) {
$coupons[] = new WC_Coupon($code);
}
}
if ($this->test_mode == 'yes') {
$action_adr = $this->test_url;
} else {
$action_adr = $this->prod_url;
}
if ($this->stage_mode == 'two-stage') {
$action_adr .= 'registerPreAuth.do';
} else if ($this->stage_mode == 'one-stage') {
$action_adr .= 'register.do';
}
$order_data = $order->get_data();
$language = substr(get_bloginfo("language"), 0, 2);
switch ($language) {
case  ('uk'):
$language = 'ua';
break;
case ('be'):
$language = 'by';
break;
}
$jsonParams = array(
'CMS' => 'Wordpress ' . get_bloginfo('version') . " + woocommerce version: " . wpbo_get_woo_version_number(),
'Module-Version' => $this->module_version,
);
if (!empty($order_data['billing']['email'])) {
$jsonParams['email'] = $order_data['billing']['email'];
}
if (!empty($order_data['billing']['phone'])) {
$jsonParams['phone'] = preg_replace("/(\W*)/", "", $order_data['billing']['phone']);
}
if (!empty($order_data['billing']['first_name'])) {
$jsonParams['payerFirstName'] = $order_data['billing']['first_name'];
}
if (!empty($order_data['billing']['last_name'])) {
$jsonParams['payerLastName'] = $order_data['billing']['last_name'];
}
if (!empty($order_data['billing']['address_1'])) {
$jsonParams['postAddress'] = $order_data['billing']['address_1'];
}
if (!empty($order_data['billing']['city'])) {
$jsonParams['payerCity'] = $order_data['billing']['city'];
}
if (!empty($order_data['billing']['state'])) {
$jsonParams['payerState'] = $order_data['billing']['state'];
}
if (!empty($order_data['billing']['postcode'])) {
$jsonParams['payerPostalCode'] = $order_data['billing']['postcode'];
}
if (!empty($order_data['billing']['country'])) {
$jsonParams['payerCountry'] = $order_data['billing']['country'];
}
foreach ($order->get_items('tax') as $item) {
$label = strtolower($item->get_label());
if ($label == "iva") {
$jsonParams["IVA.amount"] = $item->get_tax_total() * 100;
}
if ($label == "iac") {
$jsonParams["IAC.amount"] = $item->get_tax_total() * 100;
}
}
if (defined('JCCGATEWAY_ENABLE_BACK_URL_SETTINGS')
&& JCCGATEWAY_ENABLE_BACK_URL_SETTINGS === true
&& !empty($this->backToShopUrl)
) {
$jsonParams['backToShopUrl'] = $this->backToShopUrl;
}
$args = array(
'userName' => $this->merchant,
'password' => $this->password,
'amount' => $amount,
'returnUrl' => get_option('siteurl') . '?wc-api=jccgateway' . '&action=result&order_id=' . $order_id,
'jsonParams' => json_encode($jsonParams),
);
if (defined('JCCGATEWAY_MANDATORY_CURRENCY') && JCCGATEWAY_MANDATORY_CURRENCY === true) {
$currency_code = $order->get_currency();
$numeric_code = $this->get_numeric_currency_code($currency_code);
if ($numeric_code !== null) {
$args['currency'] = $numeric_code;
}
}
if (defined('JCCGATEWAY_SEND_CLIENT_FULL_INFO') && JCCGATEWAY_SEND_CLIENT_FULL_INFO === true) {
$billingPayerData = $this->_getBillingPayerData($order_data);
if(!empty($billingPayerData)) {
$args['billingPayerData'] = json_encode($billingPayerData);
}
}
if (!empty($order_data['customer_id'] && $order_data['customer_id'] > 0)) {
$client_email = !empty($order_data['billing']['email']) ? $order_data['billing']['email'] : "";
$args['clientId'] = md5($order_data['customer_id']  .  $client_email  . get_option('siteurl'));
}
if (defined('JCCGATEWAY_ENABLE_CART_OPTIONS') && JCCGATEWAY_ENABLE_CART_OPTIONS == true && $this->send_order == 'yes') {
$args['taxSystem'] = $this->tax_system;
$order_bundle = $this->_createOrderBundle($order);
if (class_exists('DiscountHelper')) {
$discountHelper = new DiscountHelper();
$discount = $discountHelper->discoverDiscount($args['amount'], $order_bundle['cartItems']['items']);
if ($discount != 0) {
$discountHelper->setOrderDiscount($discount);
$recalculatedPositions = $discountHelper->normalizeItems($order_bundle['cartItems']['items']);
$recalculatedAmount = $discountHelper->getResultAmount();
$order_bundle['cartItems']['items'] = $recalculatedPositions;
}
}
if (!empty($order_bundle)) {
$args['orderBundle'] = json_encode($order_bundle);
}
}
if ($this->orderNumberById) {
$args['orderNumber'] = $order_id . '_' . time();
} else {
$args['orderNumber'] = trim(str_replace('#', '', $order->get_order_number())) . "_" . time(); // PLUG-3966, PLUG-4300
}
$headers = array(
'CMS: Wordpress ' . get_bloginfo('version') . " + woocommerce version: " . wpbo_get_woo_version_number(),
'Module-Version: ' . $this->module_version,
);
$response = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr, $headers, $this->cacert_path);
if (JCCGATEWAY_ENABLE_LOGGING === true) {
$logData = $args;
$logData['password'] = '**removed from log**';
$this->writeLog("[REQUEST]: " . $action_adr . ": \nDATA: " . print_r($logData, true) . "\n[RESPONSE]: " . $response);
}
$response = json_decode($response, true);
if (empty($response['errorCode'])) {
if (JCCGATEWAY_SKIP_CONFIRMATION_STEP == true) {
wp_redirect($response['formUrl']); //PLUG-4104 Comment this line for redirect via pressing button (step)
exit();
}
} else {
wc_add_notice(__('There was an error while processing payment', 'wc-' . $this->id . '-text-domain') . "<br/>ERRORCODE# " . $response['errorCode'].  " " . $response['errorMessage'], 'error');
wp_safe_redirect($order->get_checkout_payment_url());
exit();
return;
}
}
protected function _createOrderBundle($order) {
$order_bundle = array();
$order_data = $order->get_data();
$order_items = $order->get_items();
$order_timestamp_created = $order_data['date_created']->getTimestamp();
$items = array();
$itemsCnt = 1;
foreach ($order_items as $value) {
$item = array();
$product_variation_id = $value['variation_id'];
if ($product_variation_id) {
$product = new WC_Product_Variation($value['variation_id']);
$item_code = $itemsCnt . "-" . $value['variation_id'];
} else {
$product = new WC_Product($value['product_id']);
$item_code = $itemsCnt . "-" . $value['product_id'];
}
$product_sku = get_post_meta($value['product_id'], '_sku', true);
$item_code = !empty($product_sku) ? $product_sku : $item_code;
$tax_type = $this->getTaxType($product);
$product_price = round((($value['total'] + $value['total_tax']) / $value['quantity']) * 100);
if ($product->get_type() == 'variation') {
}
$item['positionId'] = $itemsCnt++;
$item['name'] = $value['name'];
if ($this->versionFfd == 'v1_05') {
$item['quantity'] = array(
'value' => $value['quantity'],
'measure' => defined('JCCGATEWAY_MEASUREMENT_NAME') ? JCCGATEWAY_MEASUREMENT_NAME : 'pcs'
);
} else {
$item['quantity'] = array(
'value' => $value['quantity'],
'measure' => defined('JCCGATEWAY_MEASUREMENT_CODE') ? JCCGATEWAY_MEASUREMENT_CODE : '0'
);
}
$item['itemAmount'] = $product_price * $value['quantity'];
$item['itemCode'] = $item_code;
$item['tax'] = array('taxType' => $tax_type);
$item['itemPrice'] = $product_price;
$attributes = array();
$attributes[] = array("name" => "paymentMethod", "value" => $this->paymentMethodType);
$attributes[] = array("name" => "paymentObject", "value" => $this->paymentObjectType);
$item['itemAttributes']['attributes'] = $attributes;
$items[] = $item;
}
$shipping_total = $order->get_shipping_total();
$shipping_tax = $order->get_shipping_tax();
if ($shipping_total > 0) {
$WC_Order_Item_Shipping = new WC_Order_Item_Shipping();
$itemShipment['positionId'] = $itemsCnt;
$itemShipment['name'] = __('Delivery', 'wc-' . $this->id . '-text-domain');
if ($this->versionFfd == 'v1_05') {
$itemShipment['quantity'] = array(
'value' => 1,
'measure' => defined('JCCGATEWAY_MEASUREMENT_NAME') ? JCCGATEWAY_MEASUREMENT_NAME : 'pcs'
);
} else {
$itemShipment['quantity'] = array(
'value' => 1,
'measure' => defined('JCCGATEWAY_MEASUREMENT_CODE') ? JCCGATEWAY_MEASUREMENT_CODE : '0'
);
}
$itemShipment['itemAmount'] = $itemShipment['itemPrice'] = $shipping_total * 100;
$itemShipment['itemCode'] = 'delivery';
$itemShipment['tax'] = array('taxType' => $this->getTaxType($WC_Order_Item_Shipping));
$attributes = array();
$attributes[] = array("name" => "paymentMethod", "value" => $this->paymentObjectType_delivery);
$attributes[] = array("name" => "paymentObject", "value" => 4);
$itemShipment['itemAttributes']['attributes'] = $attributes;
$items[] = $itemShipment;
}
$order_bundle['orderCreationDate'] = $order_timestamp_created;
$order_bundle['cartItems'] = array('items' => $items);
if (!empty($order_data['billing']['email'])) {
$order_bundle['customerDetails']['email'] = $order_data['billing']['email'];
}
if (!empty($order_data['billing']['phone'])) {
$order_bundle['customerDetails']['phone'] = preg_replace("/(\W*)/", "", $order_data['billing']['phone']);
}
return $order_bundle;
}
function getTaxType($product)
{
$tax = new WC_Tax();
if (get_option("woocommerce_calc_taxes") == "no") { // PLUG-4056
$item_rate = -1;
} else {
$base_tax_rates = $tax->get_base_tax_rates($product->get_tax_class(true));
if (!empty($base_tax_rates)) {
$temp = $tax->get_rates($product->get_tax_class());
$rates = array_shift($temp);
$item_rate = round(array_shift($rates));
} else {
$item_rate = -1;
}
}
if ($item_rate == 20) {
$tax_type = 6;
} else if ($item_rate == 18) {
$tax_type = 3;
} else if ($item_rate == 10) {
$tax_type = 2;
} else if ($item_rate == 0) {
$tax_type = 1;
} else {
$tax_type = $this->tax_type;
}
return $tax_type;
}
function correctBundleItem(&$item, $discount)
{
$item['itemAmount'] -= $discount;
$diff_price = fmod($item['itemAmount'], $item['quantity']['value']); //0.5 quantity
if ($diff_price != 0) {
$item['itemAmount'] += $item['quantity']['value'] - $diff_price;
}
$item['itemPrice'] = $item['itemAmount'] / $item['quantity']['value'];
}
function _getBillingPayerData($order_data) {
$billingPayerData = array();
if (!empty($order_data['billing']['city'])) {
$billingPayerData['billingCity'] = $order_data['billing']['city'];
}
if (!empty($order_data['billing']['country'])) {
$billingPayerData['billingCountry'] = $order_data['billing']['country'];
}
if (!empty($order_data['billing']['address_1'])) {
$billingPayerData['billingAddressLine1'] = $order_data['billing']['address_1'];
}
if (!empty($order_data['billing']['address_2'])) {
$billingPayerData['billingAddressLine2'] = $order_data['billing']['address_2'];
}
if (!empty($order_data['billing']['address_3'])) {
$billingPayerData['billingAddressLine3'] = $order_data['billing']['address_3'];
}
if (!empty($order_data['billing']['postcode'])) {
$billingPayerData['billingPostalCode'] = $order_data['billing']['postcode'];
}
if (!empty($order_data['billing']['state'])) {
$billingPayerData['billingState'] = $order_data['billing']['state'];
}
return $billingPayerData;
}
function receipt_page($order)
{
$this->generate_form($order);
exit();
}
public function webhook_result()
{
if (isset($_GET['action'])) {
$action = $_GET['action'];
if ($this->test_mode == 'yes') {
$action_adr = $this->test_url;
} else {
$action_adr = $this->prod_url;
}
$action_adr .= 'getOrderStatusExtended.do';
$args = array(
'userName' => $this->merchant,
'password' => $this->password,
);
switch ($action) {
case "result":
$args['orderId'] = isset($_GET['orderId']) ? $_GET['orderId'] : null;
$order_id = $_GET['order_id'];
$order = wc_get_order($order_id);
$response = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr, array(), $this->cacert_path);
if (JCCGATEWAY_ENABLE_LOGGING === true) {
$logData = $args;
$logData['password'] = '**removed from log**';
$this->writeLog("[REQUEST RU]: " . $action_adr . ": " . print_r($logData, true) . "\n[RESPONSE]: " . print_r($response, true));
}
$response = json_decode($response, true);
$orderStatus = $response['orderStatus'];
if ($orderStatus == '1' || $orderStatus == '2') {
if ($this->allowCallbacks === false) {
$order->update_status($this->order_status_paid, "JCCGateway: " . __('Payment successful', 'wc-' . $this->id . '-text-domain'));
try {
wc_reduce_stock_levels($order_id);
} catch (Exception $e) {
}
update_post_meta($order_id, 'orderId', $args['orderId']);
$transaction_id = sanitize_text_field( $response['authRefNum'] );
$order->set_transaction_id($transaction_id);
$order->payment_complete();
}
if (!empty($this->success_url)) {
WC()->cart->empty_cart();
wp_redirect($this->success_url . "?order_id=" . $order_id);
exit;
}
wp_redirect($this->get_return_url($order));
exit;
} else {
$order->update_status('failed', "JCCGateway: " . __('Payment failed', 'wc-' . $this->id . '-text-domain'));
if (!empty($this->fail_url)) {
wp_redirect($this->fail_url . "?order_id=" . $order_id);
exit;
}
wc_add_notice(__('There was an error while processing payment', 'wc-' . $this->id . '-text-domain') . "<br/>" . $response['actionCodeDescription'], 'error');
wp_safe_redirect($order->get_checkout_payment_url());
exit;
}
$order->save();
break;
case "callback":
$args['orderId'] = isset($_GET['mdOrder']) ? $_GET['mdOrder'] : null;
$response = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr);
$response = json_decode($response, true);
if (empty($response['orderNumber'])) {
exit;
} else {
}
$p = explode("_", $response['orderNumber']);
$order_id = $p[0];
$order = wc_get_order($order_id);
$orderStatus = $response['orderStatus'];
$this->writeLog("[Incoming cb (".$order_id.")]: OrderStatus= " . $orderStatus);
if ($orderStatus == '1' || $orderStatus == '2') {
update_post_meta($order_id, 'orderId', $args['orderId']);
$transaction_id = sanitize_text_field( $response['authRefNum'] );
$order->set_transaction_id($transaction_id);
if (strpos($order->get_status(), "pending") !== false || strpos($order->get_status(), "failed") !== false) { //PLUG-4415, 4495
$order->update_status($this->order_status_paid, "JCCGateway: " . __('Payment successful', 'wc-' . $this->id . '-text-domain'));
$this->writeLog("[VALUE TO SET ORDER_STATUS]: " . $this->order_status_paid); //PLUG-7155
try {
wc_reduce_stock_levels($order_id);
} catch (Exception $e) {
}
$order->payment_complete();
}
} else if ($orderStatus == '4') {
exit();
} elseif (empty(get_post_meta($order_id, 'orderId', true))
&& $this->id == $order->get_payment_method()
)  {
$this->writeLog(">>" . $order->get_meta('orderId') . "<<");
$order->update_status('failed', "JCCGateway: " . __('Payment failed', 'wc-' . $this->id . '-text-domain'));
}
$order->save();
break;
}
exit;
}
}
public function process_refund($order_id, $amount = null, $reason = '')
{
$order = wc_get_order($order_id);
if ($amount == "0.00") {
$amount = 0;
} else {
$amount = $amount * 100;
}
$order_key = $order->get_order_key();
$args = array(
'userName' => $this->merchant,
'password' => $this->password,
'orderId' => get_post_meta($order_id, 'orderId', true),
'amount' => $amount
);
if ($this->test_mode == 'yes') {
$action_adr = $this->test_url;
} else {
$action_adr = $this->prod_url;
}
$gose = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'getOrderStatusExtended.do', array(), $this->cacert_path);
$res = json_decode($gose, true);
if ($res["orderStatus"] == "2" || $res["orderStatus"] == "4") { //DEPOSITED||REFUNDED
$result = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'refund.do', array(), $this->cacert_path);
if (JCCGATEWAY_ENABLE_LOGGING === true) {
$logData = $args;
$logData['password'] = '**removed from log**';
$this->writeLog("[DEPOSITED REFUND RESPONSE]: " . print_r($logData, true) . " \n" . $result);
}
} elseif ($res["orderStatus"] == "1") { //APPROVED 2x
if ($amount == 0) {
unset($args['amount']);
}
$result = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'reverse.do', array(), $this->cacert_path);
if (JCCGATEWAY_ENABLE_LOGGING === true) {
$logData = $args;
$logData['password'] = '**removed from log**';
$this->writeLog("[APPROVED REVERSE RESPONSE]: " . print_r($logData, true) . " \n" . $result);
}
} else {
return new WP_Error('wc_' . $this->id . '_refund_failed', sprintf(__('Order ID (%s) failed to be refunded. Please contact administrator for more help.', 'wc-' . $this->id . '-text-domain'), $order_id));
}
$response = json_decode($result, true);
if ($response["errorCode"] != "0") {
if ($response["errorCode"] == "7") {
return new WP_Error('wc_' . $this->id . '_refund_failed', "For partial refunds Order state should be in DEPOSITED in Gateway");
}
return new WP_Error('wc_' . $this->id . '_refund_failed', $response["errorMessage"]);
} else {
$result = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'getOrderStatusExtended.do', array(), $this->cacert_path);
if (JCCGATEWAY_ENABLE_LOGGING === true) {
$this->writeLog("[FINALE STATE]: " . $result);
}
$response = json_decode($result, true);
$orderStatus = $response['orderStatus'];
if ($orderStatus == '4' || $orderStatus == '3') {
return true;
} elseif ($orderStatus == '1') {
return true;
}
}
return false;
}
/**
* Process subscription payment.
*
* @param  float     $amount
* @param  WC_Order  $order
* @return void
*/
public function process_subscription_payment( $amount, $order ) {
$payment_result = $this->get_option( 'result' );
if ( 'success' === $payment_result ) {
$order->payment_complete();
} else {
$message = __( 'Order payment failed. To make a successful payment using JCCGateway_ Payments, please review the gateway settings.', 'woocommerce-gateway-jccgateway' );
throw new Exception( $message );
}
}
function get_numeric_currency_code($currency_code) {
$currency_codes = array(
'BYN' => '933',
'BHD' => '048',
'BYR' => '974',
'CAD' => '124',
'CNY' => '156',
'EUR' => '978',
'GBP' => '826',
'HKD' => '344',
'HUF' => '348',
'ILS' => '376',
'JPY' => '392',
'KGS' => '417',
'KRW' => '410',
'KZT' => '398',
'MDL' => '498',
'MYR' => '458',
'OMR' => '512',
'PHP' => '608',
'RON' => '946',
'RUB' => '643',
'RUR' => '810',
'SGD' => '702',
'UAH' => '980',
'USD' => '840',
'NGN' => '566',
'MZN' => '943',
'BGN' => '975',
'BZD' => '084',
'GHS' => '936',
'GNF' => '324',
'XOF' => '952',
'PLN' => '985',
'LSL' => '426',
'TZS' => '834',
'NZD' => '554',
'KHR' => '116',
'TRY' => '949',
'AMD' => '051',
'SAR' => '682',
'AED' => '784',
'COP' => '170',
'AUD' => '036',
'IDR' => '360',
'KWD' => '414',
'JOD' => '400',
'INR' => '356'
);
return isset($currency_codes[$currency_code]) ? $currency_codes[$currency_code] : null;
}
}
if (!function_exists('wpbo_get_woo_version_number')) {
function wpbo_get_woo_version_number()
{
if (!function_exists('get_plugins'))
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
$plugin_folder = get_plugins('/' . 'woocommerce');
$plugin_file = 'woocommerce.php';
if (isset($plugin_folder[$plugin_file]['Version'])) {
return $plugin_folder[$plugin_file]['Version'];
} else {
return "Unknown";
}
}
}