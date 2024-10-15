<?php
/**
* Plugin Name: Woocoommerce To Easyway
* Description: Register Woocommerce Order To easyway delilyvery, via api.
* Version: 1.0
* Author: IKO
* Author URI: http://iko.ge
* License: MIT
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}




// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/easyway-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/easyway-ajax-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/easyway-admin-page.php';


function easyway_enqueue_admin_styles() {
    wp_enqueue_style('easyway-admin-css', plugin_dir_url(__FILE__) . 'assets/css/easyway-admin.css');
}
add_action('admin_enqueue_scripts', 'easyway_enqueue_admin_styles');



// Enqueue scripts and styles
add_action('wp_enqueue_scripts', 'easyway_enqueue_scripts');

function easyway_enqueue_scripts() {
    wp_enqueue_script('easyway-checkout-js', plugin_dir_url(__FILE__) . 'assets/js/easyway-checkout-fields.js', array('jquery'), null, true);
	
    
    // Pass the AJAX URL and translation strings to the JavaScript file
    wp_localize_script('easyway-checkout-js', 'easyway_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'choose_region' => __('Choose Region', 'woocommerce'), // Localized string for region placeholder
        'select_city' => __('Select City', 'woocommerce'),     // Localized string for city placeholder
    ));
}

// Enqueue admin scripts
add_action('admin_enqueue_scripts', 'easyway_admin_enqueue_scripts');

function easyway_admin_enqueue_scripts() {
    wp_enqueue_script('easyway-admin-js', plugin_dir_url(__FILE__) . 'assets/js/easyway-admin.js', array('jquery'), null, true);
    wp_localize_script('easyway-admin-js', 'easyway_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}

// Save custom checkout fields to order meta
add_action('woocommerce_checkout_create_order', 'save_easyway_checkout_fields', 10, 2);

function save_easyway_checkout_fields($order, $data) {
    // Get the selected region and city from the posted data
    if (isset($_POST['easyway_region'])) {
        $region = sanitize_text_field($_POST['easyway_region']);
        $order->update_meta_data('easyway_region', $region);
        error_log("Saved Region: " . $region); // Log the saved region
    }

    if (isset($_POST['easyway_city'])) {
        $city = sanitize_text_field($_POST['easyway_city']);
        $order->update_meta_data('easyway_city', $city);
        error_log("Saved City: " . $city); // Log the saved city
    }
}






