<?php
 /**
 * Plugin Name: WooCommerce Advanced Product Labels
 * Plugin URI: http://berocket.com/product/woocommerce-advanced-product-labels
 * Description: Promote your products! Show “Free Shipping” or other special attributes with your products.
 * Version: 1.1.11
 * Author: BeRocket
 * Requires at least: 4.0
 * Author URI: http://berocket.com
 * Text Domain: BeRocket_products_label_domain
 * Domain Path: /languages/
 * WC tested up to: 3.4.6
 */
define( "BeRocket_products_label_version", '1.1.11' );
define( "BeRocket_products_label_file", __FILE__ );
include_once('main.php');

if( ! function_exists('BeRocket_generate_sales_2018') ) {
    function BeRocket_generate_sales_2018($data = array()) {
        if( time() < strtotime('-7 days', $data['end']) ) {
            $close_text = 'hide this for 7 days';
            $nothankswidth = 115;
        } else {
            $close_text = 'not interested';
            $nothankswidth = 90;
        }
        $data = array_merge(array(
            'righthtml'  => '<a class="berocket_no_thanks">'.$close_text.'</a>',
            'rightwidth'  => ($nothankswidth+20),
            'nothankswidth'  => $nothankswidth,
            'contentwidth'  => 400,
            'subscribe'  => false,
            'priority'  => 15,
            'height'  => 50,
            'repeat'  => '+7 days',
            'repeatcount'  => 3,
            'image'  => array(
                'local' => plugin_dir_url( __FILE__ ) . 'images/44p_sale.jpg',
            ),
        ), $data);
        new berocket_admin_notices($data);
    }
    BeRocket_generate_sales_2018(array(
        'start'         => 1529532000,
        'end'           => 1530392400,
        'name'          => 'SALE_LABELS_2018',
        'for_plugin'    => array('id' => 18, 'version' => '2.0', 'onlyfree' => true),
        'html'          => 'Save <strong>$20</strong> with <strong>Premium Product Labels</strong> today!
     &nbsp; <span>Get your <strong class="red">44% discount</strong> now!</span>
     <a class="berocket_button" href="https://berocket.com/product/woocommerce-advanced-product-labels" target="_blank">Save $20</a>',
    ));
    BeRocket_generate_sales_2018(array(
        'start'         => 1530396000,
        'end'           => 1531256400,
        'name'          => 'SALE_MIN_MAX_2018',
        'for_plugin'    => array('id' => 9, 'version' => '2.0', 'onlyfree' => true),
        'html'          => 'Save <strong>$20</strong> with <strong>Premium Min/Max Quantity</strong> today!
     &nbsp; <span>Get your <strong class="red">44% discount</strong> now!</span>
     <a class="berocket_button" href="https://berocket.com/product/woocommerce-minmax-quantity" target="_blank">Save $20</a>',
    ));
    BeRocket_generate_sales_2018(array(
        'start'         => 1531260000,
        'end'           => 1532120400,
        'name'          => 'SALE_LOAD_MORE_2018',
        'for_plugin'    => array('id' => 3, 'version' => '2.0', 'onlyfree' => true),
        'html'          => 'Save <strong>$20</strong> with <strong>Premium Load More Products</strong> today!
     &nbsp; <span>Get your <strong class="red">44% discount</strong> now!</span>
     <a class="berocket_button" href="https://berocket.com/product/woocommerce-load-more-products" target="_blank">Save $20</a>',
    ));
}
