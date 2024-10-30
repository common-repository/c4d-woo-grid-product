<?php
/*
Plugin Name: C4D Woocommerce Grid Product
Plugin URI: http://coffee4dev.com/
Description: Create carousel slider for product/category
Author: Coffee4dev.com
Author URI: http://coffee4dev.com/
Text Domain: c4d-edd-dt
Version: 2.0.0
*/

define('C4DWGP_PLUGIN_URI', plugins_url('', __FILE__));

add_action('wp_ajax_c4d_woo_gp', 'c4d_woo_gp_ajax');
add_action('wp_ajax_nopriv_c4d_woo_gp', 'c4d_woo_gp_ajax');
add_action( 'wp_enqueue_scripts', 'c4d_woo_gp_safely_add_stylesheet_to_frontsite');
add_shortcode('c4d_woo_gp', 'c4d_woo_gp');
add_filter( 'plugin_row_meta', 'c4d_woo_gp_plugin_row_meta', 10, 2 );

function c4d_woo_gp_plugin_row_meta( $links, $file ) {
    if ( strpos( $file, basename(__FILE__) ) !== false ) {
        $new_links = array(
            'visit' => '<a href="http://coffee4dev.com">Visit Plugin Site</<a>',
            'forum' => '<a href="http://coffee4dev.com/forums/">Forum</<a>',
            'premium' => '<a href="http://coffee4dev.com">Premium Support</<a>'
        );
        
        $links = array_merge( $links, $new_links );
    }
    
    return $links;
}

function c4d_woo_gp_safely_add_stylesheet_to_frontsite( $page ) {
	if(!defined('C4DPLUGINMANAGER')) {
		wp_enqueue_style( 'c4d-woo-gp-frontsite-style', C4DWGP_PLUGIN_URI.'/assets/default.css' );
		wp_enqueue_script( 'c4d-woo-gp-frontsite-plugin-js', C4DWGP_PLUGIN_URI.'/assets/default.js', array( 'jquery' ), false, true ); 
	}
	wp_localize_script( 'jquery', 'c4d_woo_gp',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
function c4d_woo_gp_ajax($params = array()) {
	$default = array(
		'loadmore' => 0,
		'loadmore_text' => 'More Product',
		'cols' => 4,
		'category' => '',
	);
	$params = array_merge($default, $params);
	$ajax = false;
	try {
		if (isset($_REQUEST['c4dajaxgp'])) {
			$params['category'] = isset($_REQUEST['category']) ? esc_sql($_REQUEST['category']) : '';
			$params['count'] = isset($_REQUEST['count']) ? esc_sql($_REQUEST['count']) : 3;

			if (isset($_REQUEST['loadmore']) && $_REQUEST['loadmore'] == 1) {
				if (isset($_REQUEST['page'])) {
					$params['page'] = esc_sql($_REQUEST['page']);
				}
			}
		}
		
		$args = array(
	        'posts_per_page' 	=> isset($params['count']) ? esc_sql($params['count']) : 10 ,
	        'paged'				=> isset($params['page']) ? esc_sql($params['page']) : 0,
	        'post_type' 		=> 'product',
	        'orderby'   		=> 'date',
        	'order'     		=> 'desc',
	        'post_status'       => 'publish',
	        'tax_query'     	=> array(
		        array(
		            'taxonomy'  => 'product_cat',
		            'field'     => 'id', 
		            'terms'     => explode(',', esc_sql($params['category']))
		        )
		    )
		);

		if (isset($params['order'])) {
			$orderby = $params['order'];
	    	if ($orderby == 'best_selling_products') {
		    		$args = array_merge($args, array(
		    		'meta_key'            => 'total_sales',
					'orderby'             => 'meta_value_num'
		    	));
	    	}

	    	if ($orderby == 'best-selling') {
				$args = array_merge($args, array('meta_key' => 'total_sales', 'orderby' => 'meta_value_num'));
			}

			if ($orderby == 'top-rated') {
				$args = array_merge($args, array('meta_key' => '_wc_average_rating', 'orderby' => 'meta_value_num'));
			}
		}
	   
	   	$q = new WP_Query( $args );
		
		if (!$q->have_posts()) {
			$html = '<div class="c4d-woo-gp__noti">'.esc_html__('No products!', 'c4d-woo-gp').'</div>';
			throw new Exception($html);
		}

		ob_start();
		$template = get_template_part('c4d-woo-grid-product/templates/default');
		if ($template && file_exists($template)) {
			require $template;
		} else {
			require dirname(__FILE__). '/templates/default.php';
		}
		$html = ob_get_contents();
		$html = do_shortcode($html);
		ob_end_clean();
		
		woocommerce_reset_loop();
		wp_reset_postdata();

		throw new Exception($html);
	} catch(Exception $e) {
		if (isset($_REQUEST['c4dajaxgp'])) {
			echo $e->getMessage(); wp_die();
		}
		return $e->getMessage();
	}
}

function c4d_woo_gp ($params) {
	$html = c4d_woo_gp_ajax($params);
	return $html;
}