<?php
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="pxl-content-main" class="ateam-bandroom-main">
<?php while (have_posts()) : the_post();
    if (isset($GLOBALS['ateam_bandroom_builder']) && $GLOBALS['ateam_bandroom_builder'] instanceof ATeam_Bandroom_Builder) {
        $GLOBALS['ateam_bandroom_builder']->render_frontend();
    } else { the_content(); }
endwhile; ?>
</main>
<?php get_footer();
