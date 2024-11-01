<?php
/*
Plugin Name: Checkbook.io
Plugin URI: www.checkbook.io
Description: WooCommerce plugin for Checkbook.io payments
Version: 2.0.5
Author: Checkbook.io
Author URI: www.checkbook.io
Support email: support@checkbook.io
Phone Number: 650-761-0008
Text Domain: checkbook-io
Domain Path: /languages
*/


if ( ! defined( 'ABSPATH' ) ) exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}


/**
* Add the gateway to WC Available Gateways
*
* @since 1.0.0
* @param array $gateways all available WC gateways
* @return array $gateways all WC gateways + checkbook.io gateway
*/
function checkbookio_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_Checkbook';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'checkbookio_add_to_gateways' );

function checkbookio_add_query_vars_filter( $vars ) {
	$vars[] = "code";
	return $vars;
}
add_filter( 'query_vars', 'checkbookio_add_query_vars_filter' );



/**
* Initialize the tingle.js file (for the modal)
*/
function checkbookio_customjs_init() {
	wp_enqueue_script("jquery");
	wp_enqueue_script( 'tingle-js', plugins_url( 'js/tingle.js', __FILE__ ));
	wp_enqueue_script( 'scripts-js', plugins_url( 'js/scripts.js', __FILE__ ), array( 'jquery' ), '', true );
}
add_action('wp_enqueue_scripts','checkbookio_customjs_init');


/**
* Initialize the tingle.css file (for the modal)
*/
function checkbookio_customcss_init() {
	$plugin_url = plugin_dir_url(__FILE__ );

	wp_enqueue_style( 'style1', $plugin_url . 'css/tingle.css' );
	wp_enqueue_style( 'style2', $plugin_url . 'css/styles.css' );

}
add_action( 'wp_enqueue_scripts', 'checkbookio_customcss_init' );



/**
* Adds plugin page links
*
* @since 1.0.0
* @param array $links all plugin links
* @return array $links all plugin links + our custom links (i.e., "Settings")
*/
function checkbookio_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=checkbookio_gateway' ) . '">' . __( 'Configure', 'wc-gateway-checkbookio' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'checkbookio_gateway_plugin_links' );

/**
* Checkbook.io Payment Gateway
*
*
* @class 		WC_Gateway_Checkbook
* @extends		WC_Payment_Gateway
* @version		2.0.5
* @package		WooCommerce/Classes/Payment
* @author 		Checkbook.io
*/

add_action( 'plugins_loaded', 'checkbookio_gateway_init', 11 );

function checkbookio_gateway_init() {

	class WC_Gateway_Checkbook extends WC_Payment_Gateway {

		/**
		* Constructor for the gateway.
		*/
		public function __construct() {
			session_start();
			$this->id                 = 'checkbookio_gateway';
			$this->icon               = plugins_url( 'images/main-logo.png', __FILE__ );
			$this->has_fields         = true;
			$this->method_title       = __( 'Checkbook direct payments', 'wc-gateway-checkbookio' );
			$this->method_description = __( 'Allows Checkbook.io payments via digital checks. '. "\n". 
			'In order to configure this plugin, you must set the callback URL in the Checkbook.io API dashboard to the 
			URL to your "Checkout" page.', 'wc-gateway-checkbookio' );

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->clientID     = $this->get_option('clientID');
			$this->checkRecipient = $this->get_option('checkRecipient');
			$this->recipientEmail = $this->get_option('recipientEmail');
			$this->apiSecret = $this->get_option('secretKey');
			$this->redirectURL = $this->get_option('redirectURL');
			$this->sandbox = $this->get_option('sandbox');
			$this->debugMode = $this->get_option('debugMode');
			// $this->customEmailAddress = $this->get_option('customEmailAddress');
			$this->baseURL = 'https://app.checkbook.io';
			if($this->sandbox == "yes"){
				$this->baseURL = 'https://sandbox.app.checkbook.io';
			}

			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );


			if(isset($_GET['code'])){
				$_SESSION['auth_code'] = get_query_var('code');
			}
		}



		/**
		* Initialize Gateway Settings Form Fields
		*/
		public function init_form_fields() {

			$this->form_fields = apply_filters( 'wc_checkbookio_form_fields', array(

				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'wc-gateway-checkbookio' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Checkbook.io Payments', 'wc-gateway-checkbookio' ),
					'default' => 'yes'
				),
				'sandbox' => array(
					'title'   => __( 'Sandbox Mode', 'wc-gateway-checkbookio' ),
					'type'    => 'checkbox',
					'label'   => __( 'Use Checkbook in Sandbox mode', 'wc-gateway-checkbookio' ),
					'default' => 'no'
				),
				'title' => array(
					'title'       => __( 'Title', 'wc-gateway-checkbookio' ),
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-checkbookio' ),
					'default'     => __( 'Pay with Checkbook', 'wc-gateway-checkbookio' ),
					'desc_tip'    => true,
				),
				'clientID' => array(
					'title'       => __( 'Client ID', 'wc-gateway-checkbookio' ),
					'type'        => 'text',
					'description' => __( 'Please enter your Checkbook.io API ClientID here', 'wc-gateway-checkbookio' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'secretKey' => array(
					'title'       => __( 'API Secret', 'wc-gateway-checkbookio' ),
					'type'        => 'password',
					'description' => __( 'Please enter your Checkbook.io API Secret here', 'wc-gateway-checkbookio' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'checkRecipient' => array(
					'title'       => __( 'Check Recipient (Your/Your Business\' Name)', 'wc-gateway-checkbookio' ),
					'type'        => 'text',
					'description' => __( 'Please enter the name of the check recipient. (Person/business to whom payments on the site should be directed)', 'wc-gateway-checkbookio' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'recipientEmail' => array(
					'title'       => __( 'Email (Your/Your Business\' Email)', 'wc-gateway-checkbookio' ),
					'type'        => 'text',
					'description' => __( 'Please enter the email address to which check reciepts should be sent.', 'wc-gateway-checkbookio' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'redirectURL' => array(
					'title'       => __( 'Redirect URL (ex: https://your-web.site/checkout/)', 'wc-gateway-checkbookio' ),
					'type'        => 'text',
					'description' => __( 'This value should be registered at the callback section on your checkbook developer account.', 'wc-gateway-checkbookio' ),
					'default'     => plugins_url( 'callback.php', __FILE__ ),
					'desc_tip'    => true,
				),
				'debugMode' => array(
					'title'       => __( 'Enable debug mode', 'wc-gateway-checkbookio' ),
					'type'        => 'checkbox',
					'description' => __( '(Disable in production!) Enable to see server responses.', 'wc-gateway-checkbookio' ),
					'default' => 'no',
					'desc_tip'    => true,
				),

			) );
		}

		/**
		* Create the UI for the payment fields. In this case the only payment field is the button to authenticate.
		*/
		public function payment_fields()
		{

			$oauth_url = sanitize_url($this->baseURL . "/oauth/authorize?client_id=" . $this->clientID . '&response_type=code&scope=check&redirect_uri=' . $this->redirectURL);
			?>
			<div id="txtHint">
				<?php
				if(!$_SESSION['auth_code'] == NULL)
				{
					echo '<p style="color:green;"> Authorization complete. You are now ready to make a payment via Checkbook. </p>
								<p>  <u>	<a id="authenticatecheckbook" href="javascript:openCheckbookModal(\''. esc_url( $oauth_url ) .'\')"> Sign In As Different User </a> </u> </p>';
				}
				else
				{
					echo '<u> <a id="authenticatecheckbook" href="javascript:openCheckbookModal(\''. esc_url( $oauth_url ) .'\')"> Pay with Checkbook </a> </u>';
				}
				?>
			</div>


			<?php
		}

		public function validate_fields()
		{
			if($_SESSION['auth_code'] == NULL)
			{
				wc_add_notice(  'Please press "Sign to Checkbook" to authorize payments. ', 'error' );
				return false;
			}
			else
			{
				return true;
			}
		}

		/**
		* Process the payment and return the result
		*
		* @param int $order_id
		* @return array
		*/
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			$data = "client_id=" . $this->clientID . "&grant_type=authorization_code&scope=check&code=" . $_SESSION['auth_code'] . "&redirect_uri=" . $this->redirectURL."&client_secret=" . $this->apiSecret;
			$response = wp_remote_post( $this->baseURL . "/web/v1/auth/oauth/token", array(
				'method' => 'POST',
				'timeout' => 30,
				'redirection' => 10,
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded'
				),
				'body' => $data,
				'cookies' => array()
				)
			);

			if ($this->debugMode == 'yes') { 
				wc_add_notice( "* DEBUG * Authorization response: " . esc_textarea( $response['body'] ) ); }

				
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				echo "Something went wrong: " . esc_textarea( $error_message );
			} else {
				$response = $response['body'];
				$formattedData = json_decode($response, true);
				if ($this->debugMode == 'yes') {
					// Check the response in your server log file
					error_log( print_r( esc_textarea( $response ), true) );
				}
				$bearerToken = $formattedData['access_token'];
			}

			$argdata = (array(
				'name' => sanitize_text_field( $this->checkRecipient ),
				'recipient' =>  $this->recipientEmail,
				'amount' => (float)$order->get_data()['total']
			));
			$data = json_encode($argdata);
			$response = wp_remote_post( $this->baseURL . "/v3/check/digital", array(
				'method' => 'POST',
				'timeout' => 30,
				'redirection' => 10,
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => array(
					'Authorization' => 'Bearer ' . $bearerToken,
					'Cache-Control' => 'no-cache',
					'Content-Type' => 'application/json',
				),
				'body' => $data,
				'cookies' => array()
			)
			);

			if ( is_wp_error( $response ) ) {
				echo "Something went wrong:" . esc_textarea( $response->get_error_message() );
			} else {
				$json_response = json_decode($response['body'], true);
				if(array_key_exists('id', $json_response))
				{
					$order->update_status( 'completed', __( 'Order Complete.', 'wc-gateway-checkbookio' ) );
					WC()->cart->empty_cart();
					session_destroy();
					return array(
						'result' 	=> 'success',
						'redirect'	=> $this->get_return_url($order)
					);
				}
				else
				{
					// There was an issue that resulted in the payment failing. Prevent the site from registering this as a completed transaction.
					if ($this->debugMode == 'yes') 
						{ wc_add_notice( " * DEBUG * Payment attempt response: " . esc_textarea( $response['body'] ) ); }
					else {
						wc_add_notice( __('Payment was not completed. Please, refresh the page and try again. (Error: ' . $json_response['error'] . ') ', 'checkbook') . esc_textarea( $error_message ), 'error' );
					}
					return;
				}
			}
		}
	}
}
