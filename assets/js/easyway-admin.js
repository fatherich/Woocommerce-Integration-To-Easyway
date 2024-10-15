jQuery(document).ready(function($) {
    $('.easyway-register-order').on('click', function() {
        var order_id = $(this).data('order-id');

        if (confirm('Are you sure you want to register this order with Easyway?')) {
            $.ajax({
                url: easyway_ajax_obj.ajax_url,
                method: 'POST',
                data: {
                    action: 'easyway_register_order',
                    order_id: order_id,
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data);
                        $('.easyway-register-order[data-order-id="' + order_id + '"]').prop('disabled', true);
                        $('.easyway-register-order[data-order-id="' + order_id + '"]').text('Order Registered');
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred while trying to register the order.');
                }
            });
        }
    });
});
