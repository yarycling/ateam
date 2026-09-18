<?php
/**
 * Product Card Template
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $product;
$product = wc_get_product( get_the_ID() );
$price = $product->get_price_html();
$is_featured = $product->is_featured();
$is_on_sale = $product->is_on_sale();
?>
<div class="ateam-product-card">
    <div class="ateam-product-image">
        <?php 
        $options = get_option( 'ateam_storefront_settings' );
        if ( ! empty( $options['show_badges'] ) ) : 
            if ( $is_on_sale ) : ?>
                <span class="ateam-badge sale"><?php _e( 'SALE', 'ateam-storefront' ); ?></span>
            <?php elseif ( $is_featured ) : ?>
                <span class="ateam-badge featured"><?php _e( 'BESTSELLER', 'ateam-storefront' ); ?></span>
            <?php endif; 
        endif; ?>
        
        <a href="<?php the_permalink(); ?>">
            <?php the_post_thumbnail( 'woocommerce_thumbnail' ); ?>
        </a>
        
        <div class="ateam-product-actions">
            <a href="?add-to-cart=<?php echo esc_attr( get_the_ID() ); ?>" class="ateam-add-to-cart"><?php _e( 'ADD TO CART', 'ateam-storefront' ); ?></a>
        </div>
    </div>
    <div class="ateam-product-info">
        <h3 class="ateam-product-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <div class="ateam-product-price"><?php echo $price; ?></div>
    </div>
</div>
