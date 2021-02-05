<?php
class SocialDiscountActivation {
    public static function wpsd_social_discount_activation() {
        // get access to global database
        global $wpdb;
        // create table on website to hold the variable data for the plugin table
        SocialDiscountActivation::wpsd_social_discount_create_table( $wpdb->get_blog_prefix() );
    }

    function wpsd_social_discount_create_table( $prefix ) {
        // DEFAULT values
        $header_text    = 'Get 10% OFF your Entire purchase';
        $subheader_text = 'Share our page using one of the links below to get 10% OFF automatically added to your cart!';
        $bg_color       = '#f1aeae';
        $text_color     = '#333';
        $share_page     = get_site_url();
        // Prepare SQL Query to create database table
        $creation_query = (
            'CREATE TABLE IF NOT EXISTS ' . 
            $prefix . 'social_discount_custom_settings (
                `wpsd_id` int(20) NOT NULL AUTO_INCREMENT,
                `wpsd_background_color` text NOT NULL DEFAULT ' . $bg_color . ',
                `wpsd_text_color` text NOT NULL DEFAULT ' . $text_color . ',
                `wpsd_banner_header` text NOT NULL DEFAULT ' . $header_text . 
                '`wpsd_banner_subheader` text NOT NULL DEFAULT ' . $subheader_text . 
                '`wpsd_share_page` text NOT NULL DEFAULT ' . $share_page . 
                'PRIMARY KEY (`wpsd_id`)
            );'
        );

        global $wpdb;
        $wpdb->query( $creation_query );
    }
}
?>