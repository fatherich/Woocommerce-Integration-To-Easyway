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
                $.each(regions, function(id, name) {
                    $('#easyway_region').append($('<option>', {
                        value: id,
                        text: name
                    }));
                });
            } else {
                alert('Failed to fetch regions.');
            }
        }
    });

    // Fetch cities when region is changed
    $('#easyway_region').change(function() {
        var region_id = $(this).val();
        $('#easyway_city').empty().append($('<option>', { value: '', text: '<?php _e('Select City', 'woocommerce'); ?>' }));

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
                        $.each(cities, function(id, name) {
                            $('#easyway_city').append($('<option>', {
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
    });
});
