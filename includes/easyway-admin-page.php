<?php
// Add the settings menu
add_action('admin_menu', 'easyway_admin_menu');

function easyway_admin_menu() {
    add_menu_page(
        'Easyway Settings',
        'Easyway Settings',
        'manage_options',
        'easyway-settings',
        'easyway_settings_page'
    );
}

// Register settings
add_action('admin_init', 'easyway_register_settings');

function easyway_register_settings() {
    register_setting('easyway_options_group', 'easyway_api_user');
    register_setting('easyway_options_group', 'easyway_api_key');
    register_setting('easyway_options_group', 'easyway_sender_organization');
    register_setting('easyway_options_group', 'easyway_sender_region');
    register_setting('easyway_options_group', 'easyway_sender_city');
    register_setting('easyway_options_group', 'easyway_sender_address');
    register_setting('easyway_options_group', 'easyway_sender_legal_form');
    register_setting('easyway_options_group', 'easyway_sender_tax_code');
    register_setting('easyway_options_group', 'easyway_sender_phone');
    register_setting('easyway_options_group', 'easyway_sender_email');
}

function easyway_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('easyway_options_group');
            do_settings_sections('easyway-settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API User</th>
                    <td><input type="text" name="easyway_api_user" value="<?php echo esc_attr(get_option('easyway_api_user')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="easyway_api_key" value="<?php echo esc_attr(get_option('easyway_api_key')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sender Organization</th>
                    <td><input type="text" name="easyway_sender_organization" value="<?php echo esc_attr(get_option('easyway_sender_organization')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sender Region</th>
                    <td>
                        <select name="easyway_sender_region" id="easyway_sender_region">
                            <option value=""><?php _e('Select Region', 'woocommerce'); ?></option>
                            <?php
                            $saved_region = get_option('easyway_sender_region');
                            ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sender City</th>
                    <td>
                        <select name="easyway_sender_city" id="easyway_sender_city">
                            <option value=""><?php _e('Select City', 'woocommerce'); ?></option>
                            <?php
                            $saved_city = get_option('easyway_sender_city');
                            ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sender Address</th>
                    <td><input type="text" name="easyway_sender_address" value="<?php echo esc_attr(get_option('easyway_sender_address')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sender Legal Form ID</th>
                    <td>
                        <select name="easyway_sender_legal_form">
                            <option value="1" <?php selected(1, get_option('easyway_sender_legal_form')); ?>><?php _e('ფიზიკური პირი', 'woocommerce'); ?></option>
                            <option value="2" <?php selected(2, get_option('easyway_sender_legal_form')); ?>><?php _e('შპს', 'woocommerce'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sender Tax Code</th>
                    <td><input type="text" name="easyway_sender_tax_code" value="<?php echo esc_attr(get_option('easyway_sender_tax_code')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sender Phone</th>
                    <td><input type="text" name="easyway_sender_phone" value="<?php echo esc_attr(get_option('easyway_sender_phone')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sender Email</th>
                    <td><input type="email" name="easyway_sender_email" value="<?php echo esc_attr(get_option('easyway_sender_email')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
        jQuery(document).ready(function($) {
            // Fetch regions on page load
            $.ajax({
                url: easyway_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_easyway_regions',
                },
                success: function(response) {
                    if (response.success) {
                        var regions = response.data;
                        var savedRegion = "<?php echo esc_js($saved_region); ?>";
                        $.each(regions, function(id, name) {
                            var isSelected = (id == savedRegion) ? ' selected' : '';
                            $('#easyway_sender_region').append($('<option' + isSelected + '>', {
                                value: id,
                                text: name
                            }));
                        });

                        // Fetch cities for the saved region
                        if (savedRegion) {
                            fetchCities(savedRegion);
                        }
                    } else {
                        alert('Failed to fetch regions.');
                    }
                }
            });

            // Fetch cities when region is changed
            $('#easyway_sender_region').change(function() {
                var region_id = $(this).val();
                fetchCities(region_id);
            });

            // Function to fetch cities based on region
            function fetchCities(region_id) {
                $('#easyway_sender_city').empty().append($('<option>', { value: '', text: '<?php _e('Select City', 'woocommerce'); ?>' }));
                if (region_id) {
                    $.ajax({
                        url: easyway_ajax_obj.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'get_easyway_cities',
                            region_id: region_id,
                        },
                        success: function(response) {
                            if (response.success) {
                                var cities = response.data;
                                var savedCity = "<?php echo esc_js($saved_city); ?>";
                                $.each(cities, function(id, name) {
                                    var isSelected = (id == savedCity) ? ' selected' : '';
                                    $('#easyway_sender_city').append($('<option' + isSelected + '>', {
                                        value: id,
                                        text: name
                                    }));
                                });
                            } else {
                                alert('Failed to fetch cities.');
                            }
                        }
                    });
                }
            }
        });
    </script>
    <?php
}
