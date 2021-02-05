<?php
/*
Plugin Name: Social Discounts
Plugin URI: 
Description: Dynamically creates WooCommerce discount coupons for users with an associated cookie that share the website shop.
Version: 1.0
Author: Keanu Almaguer
Author URI: http://virbuntu.com
License: GPLv2
*/

include 'SocialDiscountActivation.php'; // Table creation upon activation.
register_activation_hook( __FILE__, array('SocialDiscountActivation', 'wpsd_social_discount_activation') );

include 'SocialDiscountAdmin.php'; // Admin page settings.
include 'SocialDiscountAjax.php'; // Ajax Handler for cookie/coupon generation.


function wpsd_enqueue_scripts_front($hook) {
    wp_enqueue_script( // had to load local jQuery. Normal
        'wpsd-jquery-script', //jQuery wasn't working as expected.
        plugins_url('/js/wpsd-jquery.js', __FILE__),
        array('jquery'), false, true
    );
    wp_enqueue_script(
        'wpsd-toastr-js',
        'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js',
        array()
    );
    wp_enqueue_style(
        'wpsd-toastr-css',
        'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css',
        array()
    );
    wp_enqueue_script( // Custom jQuery to call the 
        'wpsd-ajax-script', // ajax request to ajax-admin.php
        plugins_url('/js/wpsd-create-cookie-coupon.js', __FILE__),
        array('jquery'), false, true
    );
    $title_nonce = wp_create_nonce('social_discount_nonce_item');
    wp_localize_script('wpsd-ajax-script', 'wpsd_coupon_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => $title_nonce,
    ));
    wp_enqueue_style(
        'wpsd-font-awesome', 
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', 
        array()
    );
    wp_enqueue_style(
        'wpsd-shortcode-style',
        plugins_url('/css/wpsd-shortcode-style.css', __FILE__),
        array()
    );
}
add_action('wp_enqueue_scripts', 'wpsd_enqueue_scripts_front');


function wpsd_social_discount_init() {
    $output = '<div class="wpsd-social-discount-block-container" style="background-color: #e8787899;padding: 15px;max-width: 100%;">';
    
    $output .= '<div class="wpsd-block-header" style="display: flex;justify-content: center;">';
    $output .=      '<h3 style="font-size: 1.8em;margin-top: 10px;">Get 10% OFF your <em>Entire</em> purchase!</h3>';
    $output .= '</div>';

    $output .= '<div class="wpsd-block-subheader">';
    $output .=      '<p style="display: flex;justify-content: center;margin-top: -10px;font-size: 1.2em;color: #333;">Share our page using one of the links below to get 10% OFF automatically added to your cart!</p>';
    $output .= '</div>';

    $output .= '<div class="btn__container" style="padding: 10px;">';
    $output .=      '<a onclick="wpsd_update_analytics(this.id);" target="_blank" id="wpsd-share-facebook" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Ffacebook.com%2Fzencaviar&amp;src=sdkpreparse" class="btn-f">';
    $output .=          '<i class="fa fa-facebook" style="padding-top: 5px;"></i>';
    $output .=          '<span>facebook</span>';
    $output .=      '</a>';
    $output .=      '<a onclick="wpsd_update_analytics(this.id);" target="_blank" id="wpsd-share-pinterest" href="//www.pinterest.com/pin/create/button/?url=https://zencaviar.com/shop/&media=http://zencaviar.com/wp-content/uploads/2020/07/crepe-caviar-orange-scaled.jpg&description=Authentic Sturgeon Caviar" data-pin-do="buttonPin" data-pin-config="beside" data-pin-color="red" data-pin-height="28" class="btn-p">';
    $output .=          '<i class="fa fa-pinterest" style="padding-top: 5px;"></i>';
    $output .=          '<span>pinterest</span>';
    $output .=      '</a>';
    $output .=      '<a onclick="wpsd_update_analytics(this.id);" target="_blank" id="wpsd-share-twitter" href="https://twitter.com/share?text=Came%20across%20authentic%20sturgeon%20caviar%2E%20They%20even%20have%20rare%20%23AlbinoCaviar%2E%20Visit%20ZenCaviar%2E&url=https://zencaviar.com/shop" class="btn-t">';
    $output .=          '<i class="fa fa-twitter" style="padding-top: 5px;"></i>';
    $output .=          '<span>twitter</span>';
    $output .=      '</a>';
    $output .= '</div>';

    $output .= '</div>';

    return $output;
}
add_shortcode('social_discounts', 'wpsd_social_discount_init');


function wpsd_auto_apply_coupon() {
    global $woocommerce;
    if (isset($_COOKIE['sd_social-discount-cookie'])) {
        $response = $woocommerce->cart->add_discount(
            sanitize_text_field( $_COOKIE['sd_social-discount-cookie'] )
        );
    }
}
add_action('woocommerce_before_cart_table', 'wpsd_auto_apply_coupon');
?>