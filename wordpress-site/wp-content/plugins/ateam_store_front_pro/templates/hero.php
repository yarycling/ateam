<?php
/**
 * Hero Banner Template
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<section class="ateam-hero" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.5), rgba(0,0,0,0.8)), url('<?php echo esc_url( $data['image'] ); ?>');">
    <div class="ateam-hero-content">
        <h1 class="ateam-hero-title"><?php echo esc_html( $data['title'] ); ?></h1>
        <?php if ( $data['subtitle'] ) : ?>
            <p class="ateam-hero-subtitle"><?php echo esc_html( $data['subtitle'] ); ?></p>
        <?php endif; ?>
        <a href="<?php echo esc_url( $data['link'] ); ?>" class="ateam-btn ateam-btn-primary">
            <?php echo esc_html( $data['cta'] ); ?>
        </a>
    </div>
</section>
