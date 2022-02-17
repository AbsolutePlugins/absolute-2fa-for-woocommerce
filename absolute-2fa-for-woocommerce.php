<?php
/**
 * Plugin Name: Absolute 2fa For Woocommerce
 * Plugin URI: https://absoluteplugins.com/wordpress-plugins/absp-2fa-for-woocommerce/
 * Description: A <a href="https://wordpress.org/plugins/two-factor-authentication/">Two Factor Authentication</a> addon that will add 2fa settings page under WooCommerce's My Account Page.
 * Author: AbsolutePlugins
 * Author URI: https://absoluteplugins.com/
 * Version: 1.0.1
 *
 * RequiresWP: 5.5.0
 * Tested Upto: 5.9
 *
 * WC requires at least: 5.0.0
 * WC tested up to: 6.2
 *
 * @package Absp_2fa_For_Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	die();
}

add_action( 'plugins_loaded', 'abspWoo2fa_init' );

function abspWoo2fa_init() {
	if(
		class_exists( 'WooCommerce', false ) &&
		class_exists( 'Simba_Two_Factor_Authentication', false )
	) {
		add_filter( 'woocommerce_get_query_vars', function( $vars ) {
			$vars['2fa-settings'] = get_option( 'woocommerce_2fa_settings_endpoint', '2fa-settings' );
			return $vars;
		} );
		add_filter( 'woocommerce_settings_pages', function( $settings ) {

			$last = array_pop( $settings );

			$settings[] = [
				'title'    => __( '2FA Settings', 'absp-2fa-for-woocommerce' ),
				'desc'     => __( 'Endpoint for the "My account &rarr; 2FA Settings" page.', 'absp-2fa-for-woocommerce' ),
				'id'       => 'woocommerce_2fa_settings_endpoint',
				'type'     => 'text',
				'default'  => '2fa-settings',
				'desc_tip' => true,
			];

			$settings[] = $last;

			return $settings;
		} );
		add_filter( 'woocommerce_account_menu_items', function( $items ) {
			$logout = false;
			if ( isset( $items['customer-logout'] ) ) {
				$logout = $items['customer-logout'];
				unset( $items['customer-logout'] );
			}

			$items['2fa-settings'] = __( '2FA Settings', 'absp-2fa-for-woocommerce' );

			if ( $logout ) {
				$items['customer-logout'] = $logout;
			}

			return $items;
		} );
		add_filter( 'woocommerce_endpoint_2fa-settings_title', function() {
			return __( '2FA Settings', 'absp-2fa-for-woocommerce' );
		} );
		add_action( 'woocommerce_account_2fa-settings_endpoint', function() {
			echo do_shortcode( '[twofactor_user_settings]' );
		} );
	} else {
		add_action( 'admin_notices', 'abspWoo2fa_deps_missing' );
	}
}

function abspWoo2fa_deps_missing() {
	if ( ! class_exists( 'WooCommerce', false ) ) {
		return;
	}
	?>
	<div class="notice notice-warning notice-alt">
		<p><code>2fa For Woocommerce</code> needs <strong><a href="https://wordpress.org/plugins/two-factor-authentication/">Two Factor Authentication</a></strong> to be installed and active to work. Please install and activate <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=Two%20Factor%20Authentication%20By%20David%20Anderson&tab=search&type=term' ) ); ?>">Two Factor Authentication</a> by – David Anderson.</p>
	</div>
	<?php
}

function abspWoo2fa_flush_permalinks() {
	update_option( 'woocommerce_queue_flush_rewrite_rules', 'yes' );
}

register_activation_hook( __FILE__, 'abspWoo2fa_flush_permalinks' );
register_deactivation_hook( __FILE__, 'abspWoo2fa_flush_permalinks' );

// End of file absp-2fa-for-woocommerce.php.
