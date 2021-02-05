<?php
class SocialDiscountAjax {
    function __construct() {
        $this->cookie_name = 'sd_social-discount-cookie';
        add_action('wp_ajax_wpsd_generate_coupon_init', array($this, 'wpsd_generate_coupon_code'));
	}

    // Ajax Handler
    function wpsd_generate_coupon_code() {
        // verify nonce 
        check_ajax_referer('social_discount_nonce_item');
        $returnValue;

        // Check browser for 'sd_social-discount-cookie' cookie
        if(!isset($_COOKIE[$this->cookie_name])) {
            // generate a cookie value
            $cookie_value = $this->generateRandomString();
            $coupon_array = $this->wpsd_get_coupon_titles();
            $this->wpsd_check_cookie_generate_coupon($cookie_value, $coupon_array);
            $returnValue = 'success';
        } else {
            // if cookie exists, verify that it's inside of the database as a coupon->post_title
            $read_browser_cookie = $_COOKIE[$this->cookie_name];

            // find cookie in database
            $coupon_query_args = array( // search for coupons with specified
                'post_type'     => 'shop_coupon', // meta key only.
                'meta_key'      => 'is_social_discount',
                'numberposts'   => 500
            );
            $get_coupons = get_posts($coupon_query_args);
            $coupon_array = array();
            foreach ($get_coupons as $c) {
                $coupon_array[$c->ID] = $c->post_title;
            }
            $shop_coupon_id = (array_search($read_browser_cookie, $coupon_array));

            // get metadata and search for `date_expires` 
            $coupon_expiration_meta = get_post_meta($shop_coupon_id, 'date_expires');
            
            // is `date_expires` the same as todays date?
            $todays_date = new DateTime();
            $todays_date = $todays_date->format('Y-m-d');
            // if yes then 
            if (strtotime($todays_date) >= strtotime($coupon_expiration_meta[0])) {
                unset($_COOKIE[$this->cookie_name]);
                $new_cookie_value = $this->generateRandomString();
                $new_expiry_date  = $this->wpsd_set_expiry_date();
                $this->wpsd_setCookieCouponCombo($this->cookie_name, $new_cookie_value, $new_expiry_date);
                $returnValue = 'success';
            } else {
                $returnValue = 'failed';
            }
        }
        // handle ajax
        echo '';
        wp_die($returnValue,'No Qualifications Met', array('response'=>$returnValue)); // all ajax handlers die when finished
    }

    function wpsd_get_coupon_titles() {
        // query post_type `shop_coupon` for that cookie value
        $coupon_query_args = array( // search for coupons with specified
            'numberposts'   => -1, // get all
            'post_type'     => 'shop_coupon', // meta key only.
            'meta_key'      => 'is_social_discount',
        );
        $get_coupons = get_posts($coupon_query_args, $nopaging = true);
        $coupon_array = array();
        foreach ($get_coupons as $c) {
            $coupon_array[$c->ID] = $c->post_title;
        }
        // $index = (array_search('whoop', array_values($coupon_array)));
        // echo $index; // returns [Place in array]
        // $key = (array_search('whoop', $coupon_array));
        // echo $key; // returns [ID]
        //echo $this->cookie_name;
        return $coupon_array;
    }

    function wpsd_check_cookie_generate_coupon($cookie_value, $coupon_array) {
        $expiry_date  = $this->wpsd_set_expiry_date();
        // is COUPON IN DATABASE?
        if (!in_array($cookie_value, $coupon_array)) {
            // NOT IN DATABASE - Set cookie 
            $this->wpsd_setCookieCouponCombo($this->cookie_name, $cookie_value, $expiry_date);
        } else { // FOUND IN DATABASE - coupon exists, regenerate and loop.
            $new_cookie_value = $this->generateRandomString();
            $new_coupon_array = $this->wpsd_get_coupon_titles();
            $this->wpsd_check_cookie_generate_coupon($new_cookie_value, $new_coupon_array);
        }
    }

    function wpsd_setCookieCouponCombo($cookie_name, $cookie_value, $expiry_date) {
        // $url = site_url('/');
        setcookie($this->cookie_name, $cookie_value, [
            'expires' => time() + (3600 * 24 * 7),
            'path' => '/',
            // 'domain' => $url,
            'secure' => false,
            'httponly' => false,
            'samesite' => 'strict',
        ]);
		
        // Insert new `shop_coupon` post with a `post_title` of $cookie_value
        wp_insert_post( 
            array(
                'post_title'      => $cookie_value,
                'post_status'     => 'publish',
                'post_type'       => 'shop_coupon',
                'comment_status'  => 'closed',
                'ping_status'     => 'closed',
                'meta_input'      => array(
                    'is_social_discount'     => 1,
                    'discount_type'          => 'percent',
                    'coupon_amount'          => 10,
                    'individual_use'         => 'yes',
                    'usage_limit'            => 1,
                    'usage_limit_per_user'   => 1,
                    'limit_usage_to_x_items' => 0,
                    'usage_count'            => 0,
                    'date_expires'           => $expiry_date,
                    'free_shipping'          => 'no',
                    'exclude_sale_items'     => 'yes'
                )
            )
        );
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
      
      // 7 day expire date from creation. formatted for meta_key in WP database.
    function wpsd_set_expiry_date() {
        $date = new DateTime(); // Y-m-d
        $date->add(new DateInterval('P7D'));
        return $date->format('Y-m-d') . "\n";
    }
}

new SocialDiscountAjax();
?>