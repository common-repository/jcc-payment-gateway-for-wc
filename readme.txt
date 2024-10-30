=== JCC Payment Gateway for Woocommerce ===

Contributors: jccpaymentsystems
Tags: payment,jcc,payment gateway,cyprus,jccpayment
Description: A plugin for adding the JCCgateway as a payment option in WooCommerce.
Author: JCC
Version: 5.1.6
License: GPLv2
Stable tag: 5.1.6
Requires at least: 5.4
Requires PHP: 5.6
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Donate link: https://notavailable.com
Tested up to:  6.5.0
WC tested up to: 8.9.1

== Description ==

JCC’s payment gateway offers real-time and batch payment processing. It uses all available security measures to prevent fraudulent transactions and ensure data safety yet it’s easy to integrate with merchants’ systems. 
In addition, it allows merchants to review and manage transactions, prepare reports, etc. through a user-friendly, intuitive administration interface.Another feature the plugin offers, is the ability for the merchant to define a prefix value that will be appended in the order id that is sent to JCCgateway.

All orders sent to JCC for processing by the e-shop will have that prefix.
It can be used when logging in to merchant admin console of JCC to identify from which e-shop does the order come, when merchant has multiple e-shops. The current plugin supports making payment via HTTP Post redirect to JCC payment gateway and also refunds via JCC Web Services\'s endpoint, called Financial Service.

**Note: After the payment, order's status updates as suggested by Woocommerce:**
 -*If items in the order are all physical , the order is processing until changed – Order Status = PROCESSING*
 -*If items in the order are all downloadable / virtual, the order is completed – Order Status = COMPLETED*
 -*If items are physical and downloadable / virtual, the order is processing until changed – Order Status = PROCESSING*

Supported Currencies
 - EUR (Euro)

== Guide ==

**Installation:**
1. Enter your admin area.
2. On the left panel select Plugins > Add New
3. Search the store for JCC Payments
	 
**Configuration:**
1. Access plugin settings either through:
**Plugins > JCC Payment Gateway for WooCommerce > Settings**
**WooCommerce > Settings > Payments > JCC Payment Gateway**
2. In WooCommerce JCCGateway Checkout plugin section click Settings.
3. Enter credentials for test and production environment
- To run in TEST MODE select the tick box Test Mode – Enable
- In the Login-API field enter the Login-API of your JCC test account, as provided by JCC through email
- In the Password field enter the API Password of your JCC test account, which you can get from the gateway-test merchant portal from the API section.
- For PRODUCTION untick the Test Mode - Enable tickbox and use the credentials provided for your PRODUCTION account
- Payments type One-phase payments can be used when no further confirmation or action is needed by the merchant for an order 
- Payments type Two-phase payments can be used when further confirmation or action is needed by the merchant for an order e.g. Complete the order Manually
- Optionally, success_url can be used to manually handle responses for successful orders
- Optionally, fail_url can be used to manually handle responses for failed orders
4. Save Changes
5. In Payments enable JCC Payment Gateway plugin

== Frequently Asked Questions == 
Please contact ecom.admins@jcc.com.cy for any enquires about the Plugin or the transactions.

== Upgrade Notice == 
Latest version 5.0.0, IMPORTANT NOTICE - URGENT: Please Contact JCC Before Updating! We have released a new version of the plugin that requires the usage of a new set of  credentials. 
To ensure uninterrupted service, please reach out to our team at customerservice@jcc.com.cy before updating the plugin to provide you with the new credentials. Failure to do so will result in the plugin not functioning.. 

== Screenshots ==
1. Section is used by Administrators to select Test or Production configuration.If Test Mode is enabled you can only use Test Credentials and if it is disabled you can only use the Production Credentials.
2. This screenshot shows JCC Gateway Checkout where users can procced to Gateway for payment.
3. The screenshot shows WooCommerce > Settings > Payments > JCC Payment Gateway where you can Enable the Plugin and Manage it.
4. JCC Gateway Payment screenshot. The screen that users will see when they are about to pay.

== Changelog ==

= 5.1.6 - Oct 16, 2024 =
*Bug fixes:
-Minor changes on the transaction status update

= 5.1.0 - Apr 24, 2024 =
* Plugin Upgrade:
-Added logo on checkout  

= 5.0.0 - Mar 19, 2024 =
* Plugin Upgrade:
-Added Google Pay and Apple Pay improve payments processing time and improved handling of transactions, please reach out to our team at customerservice@jcc.com.cy before updating the plugin  

= 1.3.7 - Feb 12, 2024 =
* Bug Fix:
-Minor bug fixes and enhancments
 
= 1.3.6 – Jun 07, 2022 =
* Bug Fix:
-Fix related to the payment gateway toggle button (Woocommerce -> Settings -> Payments) not working as intended.

= 1.3.5 – May 17, 2022 =
* Bug Fix:
-Fixes related to the plugin not working on a multisite network, fixes have been made to properly check for single/multisite setup and install accordingly

= 1.3.4 – Feb 21, 2022 =
* Bug Fix:
-Grammar fixes.

= 1.3.2 – Jul 21, 2021 =
* Bug Fix:
-Fixes related to the feature introduced on version 1.3.0. More specifically, fixes have been made to escape special characters included on the newly introduced fields before sending them to JCC Payment Gateway.

= 1.3.1 – Jul 21, 2021 =
* Bug Fix:
-Fixes related to the feature introduced on version 1.3.0. More specifically, fixes have been made to escape special characters included on the newly introduced fields before sending them to JCC Payment Gateway.

= 1.3.0 – Feb 25, 2021 =
* New Feature:
-Allow the merchant to decide whether additional info will be sent to the Issuing Bank in order to perform a real-time risk scoring of the transaction according to EMV 3DS, through the Settings tab of the plugin.

= 1.2.6 – Feb 02, 2021 =
* Bug Fix:
-Handling of failed order due to invalid user credentials bug resolved. Bug was resolved in the past but cam up again after last change.

= 1.2.5 – Nov 27, 2020 =
* Bug Fix:
-Removing the extra step of order's status validation that was added in version 1.2.3 since it is now handled on JCC Payment Gateway's side.

= 1.2.4 – Nov 11, 2020 =
* Bug Fix:
-Handling of failed order due to signature validation bug resolved.
-Enhcance the validity of transaction by applying the following:
	1.Go to WooCommerce -> Settings
	2.Choose the "Products" tab
	3.Choose the Category "Inventory"
	4.In the Manage Stock settings remove any value that is present in the Hold Stock (minutes) field

= 1.2.3 – Sep 07, 2020 =
* Adding an extra step of order's status validation. More specifically, when getting an error response on a payment request for a specific orderId, the actual status of the transaction on JCC's side is checked (using Query Service) and the order's status is updated accordingly.

= 1.2.1 – Aug 17, 2020 =
* Bug Fix:
-Payment orders bug issue resolved when expired session or duplicate order ID to JCC

= 1.2.0 – Aug 03, 2020 =
* Bug Fix:
-Minor bug fixes.

= 1.1.3 – Jul 29, 2020 =
* Bug Fix:
-Validating that data transferred between classes are set.

= 1.1.1 – Jul 03, 2020 =
* Bug Fix:
-Updating the way order key is set and saved

= 1.1.0 – Jun 29, 2020 =
* Adding an option for the user to choose the format of the Merchant Order ID from below options: 
-Alphanumeric staring with the prefix "wc_order_"
-Alphanumeric
-Numeric, given by woocomerce (matches the Order # found in the Orders section of admin's page)

= 1.0.0 – Jun 25, 2020 =
* Release