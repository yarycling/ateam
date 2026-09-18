<?php
/**
 * Product Query Helper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATeam_Query {

	/**
	 * Get products based on arguments
	 * 
	 * @param array $args Query arguments
	 * @return WP_Query
	 */
	public static function get_products( $args = array() ) {
		$defaults = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$query_args = wp_parse_args( $args, $defaults );

		// Handle source types
		if ( isset( $args['source'] ) ) {
			switch ( $args['source'] ) {
				case 'bestselling':
					$query_args['meta_key'] = 'total_sales';
					$query_args['orderby']  = 'meta_value_num';
					break;
				case 'featured':
					$query_args['tax_query'][] = array(
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => 'featured',
						'operator' => 'IN',
					);
					break;
				case 'onsale':
					$product_ids_on_sale = wc_get_product_ids_on_sale();
					$query_args['post__in'] = array_merge( array( 0 ), $product_ids_on_sale );
					break;
			}
		}

		// Handle category filter
		if ( ! empty( $args['category'] ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $args['category'],
			);
		}

		return new WP_Query( $query_args );
	}

	/**
	 * Get product categories for tabs
	 */
	public static function get_categories( $slugs = array() ) {
		if ( empty( $slugs ) ) {
			return get_terms( array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
			) );
		}

		return get_terms( array(
			'taxonomy' => 'product_cat',
			'slug'     => $slugs,
		) );
	}
}
