<?php
/**
 * Product Grid Template
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php 
$section_style = '';
if ( ! empty( $bg_image ) ) {
    $section_style = "style=\"background-image: linear-gradient(to bottom, rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('" . esc_url( $bg_image ) . "'); background-size: cover; background-position: center;\"";
}
?>
<section class="ateam-product-section <?php echo esc_attr( isset($extra_class) ? $extra_class : '' ); ?>" <?php echo $section_style; ?>>
    <div class="ateam-section-header">
        <h2 class="ateam-section-title"><?php echo esc_html( $title ); ?></h2>
        
        <?php if ( ! empty( $categories ) ) : ?>
            <div class="ateam-category-tabs">
                <button class="ateam-tab-btn active" data-category="all"><?php _e( 'ALL', 'ateam-storefront' ); ?></button>
                <?php foreach ( $categories as $cat ) : ?>
                    <button class="ateam-tab-btn" data-category="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( strtoupper( $cat->name ) ); ?></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="ateam-product-grid" data-columns="<?php echo esc_attr( $atts['columns'] ); ?>">
        <?php if ( $products->have_posts() ) : ?>
            <?php while ( $products->have_posts() ) : $products->the_post(); ?>
                <?php include ATEAM_STOREFRONT_PATH . 'templates/product-card.php'; ?>
            <?php endwhile; wp_reset_postdata(); ?>
        <?php else : ?>
            <p><?php _e( 'No products found.', 'ateam-storefront' ); ?></p>
        <?php endif; ?>
    </div>
</section>
