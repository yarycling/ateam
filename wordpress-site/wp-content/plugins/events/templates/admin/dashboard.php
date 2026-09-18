<?php
/**
 * Admin Dashboard Template
 *
 * @package ATeam_Events_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap ateam-dashboard-wrap">

    <!-- Header -->
    <div class="ateam-dash-header">
        <div class="ateam-dash-brand">
            <div class="ateam-dash-logo">
                <span class="ateam-logo-glyph">⚡</span>
            </div>
            <div class="ateam-dash-brand-text">
                <h1><?php _e( 'ATEAM EVENTS PRO', 'ateam-events-pro' ); ?></h1>
                <p><?php _e( 'Your professional events management dashboard', 'ateam-events-pro' ); ?></p>
            </div>
        </div>
        <div class="ateam-dash-actions">
            <a href="<?php echo admin_url( 'post-new.php?post_type=ateam_event' ); ?>" class="ateam-btn ateam-btn-primary">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php _e( 'Create Event', 'ateam-events-pro' ); ?>
            </a>
            <a href="<?php echo admin_url( 'edit.php?post_type=ateam_event&page=ateam-events-settings' ); ?>" class="ateam-btn ateam-btn-secondary">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php _e( 'Settings', 'ateam-events-pro' ); ?>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="ateam-stats-grid">
        <div class="ateam-stat-card ateam-stat-total">
            <div class="ateam-stat-icon">
                <span class="dashicons dashicons-calendar-alt"></span>
            </div>
            <div class="ateam-stat-content">
                <span class="ateam-stat-number"><?php echo intval( $published ); ?></span>
                <span class="ateam-stat-label"><?php _e( 'Published Events', 'ateam-events-pro' ); ?></span>
            </div>
        </div>

        <div class="ateam-stat-card ateam-stat-upcoming">
            <div class="ateam-stat-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="ateam-stat-content">
                <span class="ateam-stat-number"><?php echo intval( $upcoming->found_posts ); ?></span>
                <span class="ateam-stat-label"><?php _e( 'Upcoming Events', 'ateam-events-pro' ); ?></span>
            </div>
        </div>

        <div class="ateam-stat-card ateam-stat-past">
            <div class="ateam-stat-icon">
                <span class="dashicons dashicons-backup"></span>
            </div>
            <div class="ateam-stat-content">
                <span class="ateam-stat-number"><?php echo intval( $past_count ); ?></span>
                <span class="ateam-stat-label"><?php _e( 'Past Events', 'ateam-events-pro' ); ?></span>
            </div>
        </div>

        <div class="ateam-stat-card ateam-stat-draft">
            <div class="ateam-stat-icon">
                <span class="dashicons dashicons-edit-page"></span>
            </div>
            <div class="ateam-stat-content">
                <span class="ateam-stat-number"><?php echo intval( $draft ); ?></span>
                <span class="ateam-stat-label"><?php _e( 'Drafts', 'ateam-events-pro' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="ateam-dash-content">

        <!-- Upcoming Events List -->
        <div class="ateam-dash-card ateam-dash-upcoming">
            <div class="ateam-dash-card-header">
                <h2>
                    <span class="dashicons dashicons-megaphone"></span>
                    <?php _e( 'Upcoming Events', 'ateam-events-pro' ); ?>
                </h2>
                <a href="<?php echo admin_url( 'edit.php?post_type=ateam_event' ); ?>" class="ateam-link">
                    <?php _e( 'View All →', 'ateam-events-pro' ); ?>
                </a>
            </div>
            <div class="ateam-dash-card-body">
                <?php if ( $upcoming->have_posts() ) : ?>
                    <div class="ateam-upcoming-list">
                        <?php while ( $upcoming->have_posts() ) : $upcoming->the_post();
                            $event_date = get_post_meta( get_the_ID(), '_ateam_event_date', true );
                            $start_time = get_post_meta( get_the_ID(), '_ateam_event_start_time', true );
                            $venue      = get_post_meta( get_the_ID(), '_ateam_event_venue', true );
                            ?>
                            <a href="<?php echo get_edit_post_link(); ?>" class="ateam-upcoming-item">
                                <div class="ateam-upcoming-date-block">
                                    <span class="ateam-u-month"><?php echo date_i18n( 'M', strtotime( $event_date ) ); ?></span>
                                    <span class="ateam-u-day"><?php echo date_i18n( 'd', strtotime( $event_date ) ); ?></span>
                                </div>
                                <div class="ateam-upcoming-info">
                                    <strong class="ateam-upcoming-title"><?php the_title(); ?></strong>
                                    <div class="ateam-upcoming-meta">
                                        <?php if ( $start_time ) : ?>
                                            <span><span class="dashicons dashicons-clock"></span> <?php echo date_i18n( 'g:i A', strtotime( $start_time ) ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $venue ) : ?>
                                            <span><span class="dashicons dashicons-location"></span> <?php echo esc_html( $venue ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="ateam-upcoming-thumb">
                                    <?php if ( has_post_thumbnail() ) : ?>
                                        <?php the_post_thumbnail( 'thumbnail' ); ?>
                                    <?php else : ?>
                                        <span class="dashicons dashicons-format-image"></span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                <?php else : ?>
                    <div class="ateam-empty-state">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <p><?php _e( 'No upcoming events. Create your first event!', 'ateam-events-pro' ); ?></p>
                        <a href="<?php echo admin_url( 'post-new.php?post_type=ateam_event' ); ?>" class="ateam-btn ateam-btn-primary ateam-btn-sm">
                            <?php _e( 'Create Event', 'ateam-events-pro' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Info Sidebar -->
        <div class="ateam-dash-card ateam-dash-quickinfo">
            <div class="ateam-dash-card-header">
                <h2>
                    <span class="dashicons dashicons-shortcode"></span>
                    <?php _e( 'Quick Start', 'ateam-events-pro' ); ?>
                </h2>
            </div>
            <div class="ateam-dash-card-body">
                <div class="ateam-quickinfo-item">
                    <h4><?php _e( 'Events Grid', 'ateam-events-pro' ); ?></h4>
                    <p><?php _e( 'Add a beautiful events grid to any page:', 'ateam-events-pro' ); ?></p>
                    <code class="ateam-code-block">[ateam_events]</code>
                </div>

                <div class="ateam-quickinfo-item">
                    <h4><?php _e( 'Events List', 'ateam-events-pro' ); ?></h4>
                    <p><?php _e( 'Display events in a compact list:', 'ateam-events-pro' ); ?></p>
                    <code class="ateam-code-block">[ateam_events_list]</code>
                </div>

                <div class="ateam-quickinfo-item">
                    <h4><?php _e( 'Calendar View', 'ateam-events-pro' ); ?></h4>
                    <p><?php _e( 'Show a monthly calendar:', 'ateam-events-pro' ); ?></p>
                    <code class="ateam-code-block">[ateam_events_calendar]</code>
                </div>

                <div class="ateam-quickinfo-item">
                    <h4><?php _e( 'With Parameters', 'ateam-events-pro' ); ?></h4>
                    <code class="ateam-code-block">[ateam_events count="6" columns="3" show_past="yes"]</code>
                </div>
            </div>
        </div>

    </div>

    <?php wp_reset_postdata(); ?>
</div>
