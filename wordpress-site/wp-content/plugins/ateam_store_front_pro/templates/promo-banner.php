<?php
/**
 * Promo Banner Template
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php 
$promo_style = "background-image: linear-gradient(to right, rgba(0,0,0,0.8), rgba(0,0,0,0.4))";
if ( ! empty( $data['image'] ) ) {
    $promo_style .= ", url('" . esc_url( $data['image'] ) . "')";
}
$promo_class = ! empty( $data['image'] ) ? 'ateam-has-bg' : 'ateam-transparent-bg';
?>
<section class="ateam-promo-section <?php echo $promo_class; ?>" style="<?php echo $promo_style; ?>; background-size: cover; background-position: center;">
    <div class="ateam-promo-content">
        <h2 class="ateam-promo-title"><?php echo esc_html( $data['title'] ); ?></h2>
        <a href="<?php echo esc_url( $data['link'] ); ?>" class="ateam-btn ateam-btn-secondary">
            <?php echo esc_html( $data['cta'] ); ?>
        </a>
    </div>
</section>
