jQuery(document).ready(function($) {
    // Clear the region and city dropdowns initially
    var regionSelect = $('select[name="easyway_region"]');
    var citySelect = $('select[name="easyway_city"]');

    // Initialize region dropdown with a placeholder option
    regionSelect.empty().append('<option value="">' + easyway_ajax_obj.choose_region + '</option>');

    // Initialize city dropdown with a placeholder option
    citySelect.empty().append('<option value="">' + easyway_ajax_obj.select_city + '</option>');

    // Fetch regions when the page loads
    $.ajax({
        url: easyway_ajax_obj.ajax_url,
        method: 'POST',
        data: {
            action: 'get_easyway_regions',
        },
        success: function(response) {
            if (response.success) {
                var regions = response.data;
                regionSelect.empty(); // Clear previous options

                // Add "Choose Region" as the first option
                regionSelect.append('<option value="">' + easyway_ajax_obj.choose_region + '</option>');

                // Populate the region dropdown
                $.each(regions, function(id, name) {
                    regionSelect.append('<option value="' + id + '">' + name + '</option>');
                });
            } else {
                alert('Error: ' + response.data);
            }
        },
        error: function() {
            alert('Failed to load regions.');
        }
    });

    // Fetch cities based on selected region
    regionSelect.on('change', function() {
        var region_id = $(this).val();

        if (region_id) {
            $.ajax({
                url: easyway_ajax_obj.ajax_url,
                method: 'POST',
                data: {
                    action: 'get_easyway_cities',
                    region_id: region_id,
                },
                success: function(response) {
                    if (response.success) {
                        var cities = response.data;
                        citySelect.empty(); // Clear previous options

                        // Add placeholder for city selection
                        citySelect.append('<option value="">' + easyway_ajax_obj.select_city + '</option>');

                        // Populate the city dropdown
                        $.each(cities, function(id, name) {
                            citySelect.append('<option value="' + id + '">' + name + '</option>');
                        });
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Failed to load cities.');
                }
            });
        } else {
            // Clear the city dropdown if no region is selected
            citySelect.empty().append('<option value="">' + easyway_ajax_obj.select_city + '</option>');
        }
    });
});
