<?php
/**
 * Plugin Name: Order Export For WooCommerce to CSV
 * Plugin URI:
 * Description: Export orders from WooCommerce to CSV.
 * Author: Iryna Berezhna
 * Author URI:
 * Version: 1.0.3
 * Text Domain: woo-order-export-csv
 * Domain Path:
 * WC tested up to: 8.5
 *
 * @package     woo-order-export-csv
 * @author      Iryna Berezhna
 * @Category    Plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	include 'functions.php';
	include 'admin/order-export-admin.php';
	include 'includes/order-export.php';
}