<?php
/**
 * Rentals page template override.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="pxl-content-main" class="ateam-rentals-main">
    <?php
    while (have_posts()) {
        the_post();
        if (isset($GLOBALS['ateam_rentals_builder']) && $GLOBALS['ateam_rentals_builder'] instanceof ATeam_Rentals_Builder) {
            $GLOBALS['ateam_rentals_builder']->render_frontend();
        } else {
            the_content();
        }
    }
    ?>
</main>
<?php
get_footer();

