<?php 
defined('ABSPATH') or die('No direct access');

// Remove Product ordering from Shop page
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

// Remove Product product count from Shop page
function woocommerce_result_count() {
        return;
}

if( !function_exists('ventcamp_setup_woocommerce') ) {
	function ventcamp_setup_woocommerce(){
		add_theme_support( 'woocommerce' );
	}
}
add_action( 'after_setup_theme', 'ventcamp_setup_woocommerce' );

// Redirect to checkout after tickets is added to cart
if( !function_exists('ventcamp_redirect_to_checkout') ) {
	function ventcamp_redirect_to_checkout() {
		$checkout_url = WC()->cart->get_cart_url();
		return $checkout_url;
	}
}
add_filter ('woocommerce_add_to_cart_redirect', 'ventcamp_redirect_to_checkout');

// Remove unused product types
if( !function_exists('ventcamp_product_types') ) {
	function ventcamp_product_types( $product_types ) {
		$product_types['simple'] =  esc_html__('Ticket', 'ventcamp');
		// unset( $product_types['simple'] );
		unset( $product_types['grouped'] );
		unset( $product_types['variable'] );
		unset( $product_types['external'] );
	 
		return $product_types;
	}
}
add_filter( 'product_type_selector', 'ventcamp_product_types', 10, 2 );

// Add content wrappers
if( !function_exists('ventcamp_woocommerce_containers_start') ) {
	function ventcamp_woocommerce_containers_start(){
		echo '<div class="container">';
		echo '<div class="content col-md-offset-2 col-md-8">';
	}
}
add_action( 'woocommerce_before_main_content', 'ventcamp_woocommerce_containers_start' );

// Add content wrappers closings
if( !function_exists('ventcamp_woocommerce_containers_end') ) {
	function ventcamp_woocommerce_containers_end(){
		echo '</div>';
		echo '</div>';
	}
}
add_action( 'woocommerce_after_main_content', 'ventcamp_woocommerce_containers_end' );
