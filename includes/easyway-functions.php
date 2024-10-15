<?php



// Add custom fields to the checkout
add_filter('woocommerce_checkout_fields', 'easyway_custom_checkout_fields');

function easyway_custom_checkout_fields($fields) {
    $fields['billing']['easyway_region'] = array(
        'type' => 'select',
        'label' => __('რეგიონი', 'woocommerce'),
        'options' => array('' => __('აირჩიეთ რეგიონი', 'woocommerce')),
        'required' => true,
    );

    $fields['billing']['easyway_city'] = array(
        'type' => 'select',
        'label' => __('ქალაქი', 'woocommerce'),
        'options' => array('' => __('აირჩიეთ ქალაქი', 'woocommerce')),
        'required' => true,
    );

    return $fields;
}

// Add button to admin order page
add_action('woocommerce_admin_order_data_after_order_details', 'easyway_add_register_button');

function easyway_add_register_button($order) {
    // Check if Easyway order ID already exists
    $easyway_order_id = $order->get_meta('easyway_order_id');
    
    // Display button if Easyway order ID is not set
    if (empty($easyway_order_id)) {
        echo '<button type="button" class="order_reg_button button button-primary easyway-register-order" data-order-id="' . esc_attr($order->get_id()) . '">Register Order to Easyway</button>';
    } else {
        echo '<button type="button" class="order_reg_button disabled button button-secondary" disabled>Order Registered (ID: ' . esc_html($easyway_order_id) . ')</button>';
    }
}  



// Function to fetch region name by ID
function get_easyway_region_name($region_id) {
    $api_url = 'https://easyway.ge/api/region';
    $api_user = get_option('easyway_api_user');
    $api_key = get_option('easyway_api_key');

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_user . ':' . $api_key,
        ),
    ));

    if (is_wp_error($response)) {
        return __('Unknown Region', 'woocommerce');
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $regions = isset($body['region']) ? $body['region'] : [];

    foreach ($regions as $region) {
        if ($region['id'] == $region_id) {
            return $region['name'];
        }
    }

    return __('Unknown Region', 'woocommerce');
}

// Function to fetch city name by ID and region ID
function get_easyway_city_name($region_id, $city_id) {
    $api_url = "https://easyway.ge/api/city/{$region_id}";
    $api_user = get_option('easyway_api_user');
    $api_key = get_option('easyway_api_key');

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_user . ':' . $api_key,
        ),
    ));

    if (is_wp_error($response)) {
        return __('Unknown City', 'woocommerce');
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $cities = isset($body['city']) ? $body['city'] : [];

    foreach ($cities as $city) {
        if ($city['id'] == $city_id) {
            return $city['name'];
        }
    }

    return __('Unknown City', 'woocommerce');
}



// Display Easyway region and city in the order details page under billing information
add_action('woocommerce_admin_order_data_after_billing_address', 'display_easyway_region_city_in_admin_order', 10, 1);

function display_easyway_region_city_in_admin_order($order) {
    // Retrieve the region and city IDs from the order meta
    $region_id = $order->get_meta('easyway_region');
    $city_id = $order->get_meta('easyway_city');

    // Fetch region and city names using IDs
    $region_name = get_easyway_region_name($region_id);
    $city_name = get_easyway_city_name($region_id, $city_id);

    // Display the region and city
    echo '<p><strong>' . __('რეგიონი', 'woocommerce') . ':</strong> ' . esc_html($region_name) . '</p>';
    echo '<p><strong>' . __('ქალაქი/უბანი', 'woocommerce') . ':</strong> ' . esc_html($city_name) . '</p>';
}

