<?php 

class SocialDiscountAdmin {
    function __construct() {
        $this->cookie_name = 'sd_social-discount-cookie';
        /*
         * Register 'wp_settings_init' to the 'admin_init' hook
         */
        add_action('admin_menu', array($this, 'wpsd_admin_page'));
        add_action('wp_enqueue_scripts', array($this, 'wpsd_enqueue_scripts'));
        // add_action('wp_ajax_wpsd_generate_coupon_init', array($this, 'wpsd_generate_coupon_code'));
    }

    // Register a new admin page for "Social Discounts" page.
    function wpsd_admin_page() {
        add_menu_page(
            __('Social Discounts Settings', 'textdomain'),
            __('Social Discounts', 'textdomain'),
            'manage_options',
            'wpsd-social-discounts-admin',
            array($this, 'wpsd_social_discounts_admin_page'),
            plugins_url('percentage-icon.png', __FILE__),
            65
        );
    }

    function wpsd_social_discounts_admin_page() {
        ?>
        <h1>Social Discounts</h1>
        <h4>
            Here you can fine tune the control over the discounts people can get 
            by sharing your website.
        </h4>
        <br /><br />

        <?php $this->wpsd_generate_coupon_table(); ?>
        
        <!-- <button class="button button-primary" 
            type="button" name="wpsd-generate-code"
            onclick="wpsd_generate_coupon()"
        >
            Generate Coupon
        </button> -->

        <?php
    }

    // Enqueue jQuery & Javascript
    function wpsd_enqueue_scripts($hook) {
        wp_enqueue_script( // had to load local jQuery. Normal
            'wpsd-jquery-script', //jQuery wasn't working as expected.
            plugins_url('/js/wpsd-jquery.js', __FILE__),
            array('jquery'), false, true
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
    }

    function wpsd_generate_coupon_table() {
        $wpsd_args = array(
            'numberposts'   => -1, // get all
            'post_type' => 'shop_coupon',
            'meta_key'  => 'is_social_discount',
        );
        $wpsd_posts = get_posts($wpsd_args);
        //print_r($wpsd_posts);
        
        if (!empty($wpsd_posts)) {
            $wpsd_post_array = json_encode($wpsd_posts);

            ?>
            <div style="padding:0 20px 0 0;">
                <table class="wp-list-table widefat fixed">
                    <thead>
                        <tr>
                            <th style="width:80px; font-weight:bold;">ID</th>
                            <th style="width:300px;font-weight:bold;">Title</th>
                            <th style="font-weight:bold;">Coupon Amount</th>
                            <th style="font-weight:bold;">Usage Limit</th>
                            <th style="font-weight:bold;">Times Used</th>
                            <th style="font-weight:bold;">Expiration Date</th>
                        </tr>
                    </thead>
            <?php

            foreach ($wpsd_posts as $p) {
                $p_coupon_amt  = get_post_meta($p->ID, 'coupon_amount');
                $p_usage_limit = get_post_meta($p->ID, 'usage_limit');
                $p_usage_count = get_post_meta($p->ID, 'usage_count');
                $p_expiration  = get_post_meta($p->ID, 'date_expires');
                //print_r(get_post_meta($p->ID));

                ?>
                <tr>
                    <td style="width: 80px;" class="wpsd-post"><?php echo $p->ID; ?></td>
                    <td style="width: 80px;" class="wpsd-post">
                        <a href="<?php echo get_permalink($p->ID); ?>">
                            <?php echo $p->post_title ?>
                        </a>
                    </td>
                    <td style="width: 80px;" class="wpsd-post"><?php echo $p_coupon_amt[0]; ?>% off</td>
                    <td style="width: 80px;" class="wpsd-post"><?php echo $p_usage_limit[0]; ?></td>
                    <td style="width: 80px;" class="wpsd-post"><?php echo $p_usage_count[0]; ?>/<?php echo $p_usage_limit[0]; ?></td>
                    <td style="width: 80px;" class="wpsd-post"><?php echo $p_expiration[0]; ?></td>
                </tr>
                <?php
            }
            ?>
                </table>
                <br /><br />
            <?php
        } else {
            echo '<h3>No Social Discount Coupons Found</h3><br /><br />';
        }
        wp_reset_postdata();
    }
}

new SocialDiscountAdmin();

?>