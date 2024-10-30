<?php
/**
* Plugin Name: WooCommerce JCCGateway Checkout plugin
* Plugin URI:
* Description: Allows to use a payment gateway with the WooCommerce
* Version: 5.1.6
* Author: JCCGateway
* Text Domain: wc-jccgateway-text-domain
* Domain Path: /lang
* Request at least:        4.7.0
* Tested up to:            6.5
* Requires PHP:            5.6
* WC requires at least:    4.0
* WC tested up to:         8.9.1
*/
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
require_once(__DIR__ . '/includes/include.php');
if (file_exists(__DIR__ . '/includes/DiscountHelper.php')) {
include(__DIR__ . '/includes/DiscountHelper.php');
}
add_filter('plugin_row_meta', 'jccgateway_register_plugin_links', 10, 2);
function jccgateway_register_plugin_links($links, $file)
{
$base = plugin_basename(__FILE__);
if ($file == $base) {
$links[] = '<a href="admin.php?page=wc-settings&tab=checkout&section=jccgateway">' . __('Settings', 'woocommerce') . '</a>';
}
return $links;
}
add_action('before_woocommerce_init', function() {
if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
}
});
add_action('plugins_loaded', 'load_jccgateway_textdomain');
function load_jccgateway_textdomain () {
$res = load_plugin_textdomain('wc-jccgateway-text-domain', false, dirname(plugin_basename(__FILE__)) . '/lang');
}
/**
* WC JCCGateway_ Payment gateway plugin class.
*
* @class WC_JCCGateway__Payments
*/
class WC_JCCGateway__Payments {
/**
* Plugin bootstrapping.
*/
public static function init() {
add_action( 'plugins_loaded', array( __CLASS__, 'includes' ), 0 );
add_filter( 'woocommerce_payment_gateways', array( __CLASS__, 'add_gateway' ) );
add_action( 'woocommerce_blocks_loaded', array( __CLASS__, 'woocommerce_gateway_jccgateway_woocommerce_block_support' ) );
}
/**
* Add the JCCGateway_ Payment gateway to the list of available gateways.
*
* @param array
*/
public static function add_gateway( $gateways ) {
$options = get_option( 'woocommerce_jccgateway_settings', array() );
$gateways[] = 'WC_Gateway_JCCGateway_';
return $gateways;
}
/**
* Plugin includes.
*/
public static function includes() {
if ( class_exists( 'WC_Payment_Gateway' ) ) {
require_once 'includes/class-wc-gateway-jccgateway.php';
}
}
/**
* Plugin url.
*
* @return string
*/
public static function plugin_url() {
return untrailingslashit( plugins_url( '/', __FILE__ ) );
}
/**
* Plugin url.
*
* @return string
*/
public static function plugin_abspath() {
return trailingslashit( plugin_dir_path( __FILE__ ) );
}
/**
* Registers WooCommerce Blocks integration.
*
*/
public static function woocommerce_gateway_jccgateway_woocommerce_block_support() {
if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
require_once 'includes/blocks/class-wc-jccgateway-payments-blocks.php';
add_action(
'woocommerce_blocks_payment_method_type_registration',
function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
$payment_method_registry->register( new WC_Gateway_JCCGateway__Blocks_Support() );
}
);
}
}
}
WC_JCCGateway__Payments::init();
