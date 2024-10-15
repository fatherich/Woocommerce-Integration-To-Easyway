<?php
// AJAX handler to fetch regions from Easyway API
add_action('wp_ajax_get_easyway_regions', 'get_easyway_regions');
add_action('wp_ajax_nopriv_get_easyway_regions', 'get_easyway_regions');

function get_easyway_regions() {
    $api_url = 'https://easyway.ge/api/region';
    $api_user = get_option('easyway_api_user'); // Fetch API User
    $api_key = get_option('easyway_api_key');   // Fetch API Key

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_user . ':' . $api_key,
        ),
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('Error fetching regions.');
    }

    $regions = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($regions['region'])) {
        $options = array();
        foreach ($regions['region'] as $region) {
            $options[$region['id']] = $region['name'];
        }
        wp_send_json_success($options);
    } else {
        wp_send_json_error('No regions found.');
    }
}

// AJAX handler to fetch cities based on selected region
add_action('wp_ajax_get_easyway_cities', 'get_easyway_cities');
add_action('wp_ajax_nopriv_get_easyway_cities', 'get_easyway_cities');

function get_easyway_cities() {
    if (!isset($_POST['region_id'])) {
        wp_send_json_error('No region ID provided.');
    }

    $region_id = intval($_POST['region_id']);
    $api_url = "https://easyway.ge/api/city/{$region_id}";
    $api_user = get_option('easyway_api_user');
    $api_key = get_option('easyway_api_key');

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_user . ':' . $api_key,
        ),
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('Error fetching cities.');
    }

    $cities = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($cities['city'])) {
        $options = array();
        foreach ($cities['city'] as $city) {
            $options[$city['id']] = $city['name'];
        }
        wp_send_json_success($options);
    } else {
        wp_send_json_error('No cities found.');
    }
}

// AJAX handler to register order with Easyway
add_action('wp_ajax_easyway_register_order', 'easyway_register_order');

function easyway_register_order() {
    // Get the order ID from the AJAX request
    if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
        wp_send_json_error('Order ID is missing.');
        wp_die();
    }

    $order_id = intval($_POST['order_id']);
    $order = wc_get_order($order_id);

    if (!$order) {
        wp_send_json_error('Invalid order ID.');
        wp_die();
    }

    if ($order->get_meta('easyway_order_id')) {
        wp_send_json_error('Order is already registered with Easyway.');
        wp_die();
    }

    $payment_method = $order->get_payment_method();
    $pay_method = ($payment_method === 'cod') ? 'cash' : 'cashless';
    $cgd = ($pay_method === 'cash') ? $order->get_total() : '';

    $region_id = $order->get_meta('easyway_region');
    $city_id = $order->get_meta('easyway_city');

    $api_url = 'https://easyway.ge/api/order/insert';
    $api_user = get_option('easyway_api_user');
    $api_key = get_option('easyway_api_key');

    $sender_name = get_option('easyway_sender_organization');
    $sender_region_id = get_option('easyway_sender_region');
    $sender_city_id = get_option('easyway_sender_city');
    $sender_address = get_option('easyway_sender_address');
    $sender_legal_form_id = get_option('easyway_sender_legal_form');
    $sender_tax_code = get_option('easyway_sender_tax_code');
    $sender_phone = get_option('easyway_sender_phone');
    $sender_email = get_option('easyway_sender_email');

    $data = array(
        'sender_name' => $sender_name,
        'sender_region_id' => $sender_region_id,
        'sender_city_id' => $sender_city_id,
        'sender_address' => $sender_address,
        'sender_legal_form_id' => $sender_legal_form_id,
        'sender_tax_code' => $sender_tax_code,
        'sender_phone' => $sender_phone,
        'sender_email' => $sender_email,
        'receiver_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        'receiver_region_id' => $region_id,
        'receiver_city_id' => $city_id,
        'receiver_address' => $order->get_billing_address_1(),
        'receiver_legal_form_id' => 1,
        'receiver_tax_code' => '15151354561',
        'receiver_phone' => $order->get_billing_phone(),
        'receiver_email' => $order->get_billing_email(),
        'package_id' => 2,
        'payer' => 'sender',
        'pay_method' => $pay_method,
        'cgd' => $cgd,
        'comment' => $order->get_customer_note(),
    );

    $response = wp_remote_post($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_user . ':' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode($data),
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('Failed to register order with Easyway: ' . $response->get_error_message());
    }

    $response_body = wp_remote_retrieve_body($response);
    $result = json_decode($response_body, true);

    if (isset($result['status']) && $result['status'] === 'CREATED') {
        if (isset($result['order_id'])) {
            $easyway_order_id = sanitize_text_field($result['order_id']);
            $order->update_meta_data('easyway_order_id', $easyway_order_id);
            $order->save();
            $order->update_status('completed', 'Order successfully registered with Easyway.');
            wp_send_json_success('Order successfully registered with Easyway. Easyway Order ID: ' . $easyway_order_id);
        } else {
            wp_send_json_error('Order ID is missing in the response.');
        }
    } else {
        wp_send_json_error('Failed to register order with Easyway: ' . (isset($result['message']) ? $result['message'] : 'Unknown error.'));
    }

    wp_die();
}