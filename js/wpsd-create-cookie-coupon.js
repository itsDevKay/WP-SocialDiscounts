function wpsd_generate_coupon() {
    $.post(wpsd_coupon_obj.ajax_url, {
        _ajax_nonce: wpsd_coupon_obj.nonce,
        action: "wpsd_generate_coupon_init",
        title: this.value
    }, function(data) {
        console.log(data);
        if (data === 'success') {
            setTimeout(function() {
                toastr.success("Your discount will be automatically added to your checkout.", "Thanks For Sharing!");
            }, 3000);
        } else {
            setTimeout(function() {
                toastr.info("You already have a discount waiting for you at checkout. Don't worry, it's not going anywhere!");
            }, 3000);
        }
        return 'Response: ' + data;
    });
}

function wpsd_send_click_to_database(sm) {
    // send ajax request to update click by 1
    return null;
}

async function wpsd_update_analytics(id) {
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-bottom-full-width",
        "preventDuplicates": false,
        "showDuration": "60000",
        "hideDuration": "60000",
        "timeOut": "60000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    // split id to get the name of social media that was clicked.
    var social_media = id.split('wpsd-share-');
    // send social media name that was click to database to be updated.
    wpsd_send_click_to_database(social_media[1]);
    // generate cookie coupon combo
    var response = wpsd_generate_coupon();
    return response;
}
