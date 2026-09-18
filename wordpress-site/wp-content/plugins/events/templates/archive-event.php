<?php
/**
 * Archive Event Template
 *
 * @package ATeam_Events_Pro
 */

get_header();

$primary = get_option( 'ateam_events_primary_color', '#6C63FF' );
?>

<div class="ateam-archive-wrap" style="--ateam-primary: <?php echo esc_attr( $primary ); ?>;">

    <div class="ateam-archive-header">
        <div class="ateam-archive-header-inner">
            <h1 class="ateam-archive-title"><?php _e( 'Events', 'ateam-events-pro' ); ?></h1>
            <p class="ateam-archive-subtitle"><?php _e( 'Discover upcoming events and experiences', 'ateam-events-pro' ); ?></p>
        </div>
    </div>

    <div class="ateam-archive-content">
        <?php
        // Category filter chips
        $categories = get_terms( array(
            'taxonomy'   => 'event_category',
            'hide_empty' => true,
        ) );

        if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
        ?>
            <div class="ateam-filter-bar">
                <a href="<?php echo get_post_type_archive_link( 'ateam_event' ); ?>" class="ateam-filter-chip active"><?php _e( 'All Events', 'ateam-events-pro' ); ?></a>
                <?php foreach ( $categories as $cat ) : ?>
                    <a href="<?php echo get_term_link( $cat ); ?>" class="ateam-filter-chip"><?php echo esc_html( $cat->name ); ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ( have_posts() ) : ?>
            <div class="ateam-events-grid" data-columns="3">
                <?php while ( have_posts() ) : the_post();
                    $event_id   = get_the_ID();
                    $event_date = get_post_meta( $event_id, '_ateam_event_date', true );
                    $start_time = get_post_meta( $event_id, '_ateam_event_start_time', true );
                    $end_time   = get_post_meta( $event_id, '_ateam_event_end_time', true );
                    $venue      = get_post_meta( $event_id, '_ateam_event_venue', true );
                    $city       = get_post_meta( $event_id, '_ateam_event_city', true );
                    $is_all_day = get_post_meta( $event_id, '_ateam_event_all_day', true );
                    $is_virtual = get_post_meta( $event_id, '_ateam_event_virtual', true );
                    $date_format = get_option( 'ateam_events_date_format', 'F j, Y' );
                    $time_format = get_option( 'ateam_events_time_format', 'g:i A' );
                    $event_cats  = get_the_terms( $event_id, 'event_category' );
                ?>
                    <article class="ateam-event-card" id="ateam-event-<?php echo $event_id; ?>">
                        <a href="<?php the_permalink(); ?>" class="ateam-card-link">
                            <div class="ateam-card-image">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
                                <?php else : ?>
                                    <div class="ateam-card-placeholder">
                                        <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                                            <rect x="6" y="10" width="36" height="28" rx="4" stroke="currentColor" stroke-width="2"/>
                                            <line x1="6" y1="18" x2="42" y2="18" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>

                                <?php if ( $event_date ) : ?>
                                    <div class="ateam-card-date-badge">
                                        <span class="ateam-badge-month"><?php echo date_i18n( 'M', strtotime( $event_date ) ); ?></span>
                                        <span class="ateam-badge-day"><?php echo date_i18n( 'd', strtotime( $event_date ) ); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ( $is_virtual ) : ?>
                                    <span class="ateam-card-virtual-badge"><?php _e( 'VIRTUAL', 'ateam-events-pro' ); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="ateam-card-body">
                                <?php if ( $event_cats && ! is_wp_error( $event_cats ) ) : ?>
                                    <div class="ateam-card-categories">
                                        <?php foreach ( $event_cats as $cat ) : ?>
                                            <span class="ateam-card-category"><?php echo esc_html( $cat->name ); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <h3 class="ateam-card-title"><?php the_title(); ?></h3>

                                <div class="ateam-card-meta">
                                    <?php if ( $event_date ) : ?>
                                        <div class="ateam-card-meta-item">
                                            <span class="dashicons dashicons-calendar-alt"></span>
                                            <span><?php echo date_i18n( $date_format, strtotime( $event_date ) ); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ( $start_time && ! $is_all_day ) : ?>
                                        <div class="ateam-card-meta-item">
                                            <span class="dashicons dashicons-clock"></span>
                                            <span><?php echo date_i18n( $time_format, strtotime( $start_time ) ); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ( $venue || $city ) : ?>
                                        <div class="ateam-card-meta-item">
                                            <span class="dashicons dashicons-location"></span>
                                            <span><?php echo esc_html( implode( ', ', array_filter( array( $venue, $city ) ) ) ); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ( has_excerpt() ) : ?>
                                    <p class="ateam-card-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 18 ); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="ateam-pagination">
                <?php
                echo paginate_links( array(
                    'prev_text' => '<span class="dashicons dashicons-arrow-left-alt2"></span>',
                    'next_text' => '<span class="dashicons dashicons-arrow-right-alt2"></span>',
                    'type'      => 'list',
                ) );
                ?>
            </div>

        <?php else : ?>
            <div class="ateam-events-empty">
                <div class="ateam-events-empty-icon">
                    <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
                        <rect x="12" y="16" width="56" height="48" rx="8" stroke="currentColor" stroke-width="3" fill="none"/>
                        <line x1="12" y1="30" x2="68" y2="30" stroke="currentColor" stroke-width="3"/>
                        <circle cx="40" cy="46" r="8" stroke="currentColor" stroke-width="2.5" fill="none"/>
                        <line x1="46" y1="52" x2="52" y2="58" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <h3><?php _e( 'No Events Found', 'ateam-events-pro' ); ?></h3>
                <p><?php _e( 'Check back soon for exciting events!', 'ateam-events-pro' ); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
