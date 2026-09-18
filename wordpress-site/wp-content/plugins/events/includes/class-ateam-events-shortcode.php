<?php
/**
 * Frontend Shortcodes
 *
 * @package ATeam_Events_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ATeam_Events_Shortcode {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'ateam_events', array( $this, 'render_events_grid' ) );
        add_shortcode( 'ateam_events_list', array( $this, 'render_events_list' ) );
        add_shortcode( 'ateam_events_calendar', array( $this, 'render_events_calendar' ) );
        add_shortcode( 'ateam_single_event', array( $this, 'render_single_event' ) );
        add_shortcode( 'ateam_events_hero_slider', array( $this, 'render_hero_slider' ) );

        // Single event template
    }

    /**
     * Events Grid Shortcode
     * Usage: [ateam_events count="9" category="" columns="3" show_past="no" layout="grid"]
     */
    public function render_events_grid( $atts ) {
        $atts = shortcode_atts( array(
            'count'   => get_option( 'ateam_events_events_per_page', 12 ),
            'columns' => 3,
            'category'=> '',
            'show_past'=> 'no',
            'layout'    => 'grid',
            'orderby'   => 'event_date',
            'order'     => 'ASC',
        ), $atts, 'ateam_events' );

        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $current_month = isset( $_GET['event_month'] ) ? sanitize_text_field( $_GET['event_month'] ) : '';

        $cache_version = get_option( 'ateam_events_cache_version', '1' );
        $cache_key     = 'ateam_grid_v3_' . md5( wp_json_encode( $atts ) . $paged . $current_month . $cache_version );
        $cached_html   = get_transient( $cache_key );

        if ( false !== $cached_html ) {
            return $cached_html;
        }

        $args = array(
            'post_type'      => 'ateam_event',
            'posts_per_page' => intval( $atts['count'] ),
            'paged'          => $paged,
            'post_status'    => 'publish',
            'meta_key'       => '_ateam_event_date',
            'orderby'        => 'meta_value',
            'order'          => strtoupper( $atts['order'] ),
        );

        // Filter by category
        if ( ! empty( $atts['category'] ) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'event_category',
                    'field'    => 'slug',
                    'terms'    => array_map( 'trim', explode( ',', $atts['category'] ) ),
                ),
            );
        }

        // Filter out past events
        $meta_query = array( 'relation' => 'AND' );

        if ( ! empty( $current_month ) ) {
            $start_of_month = date( 'Y-m-d', strtotime( $current_month . '-01' ) );
            $end_of_month = date( 'Y-m-t', strtotime( $current_month . '-01' ) );
            
            $meta_query[] = array(
                'key'     => '_ateam_event_date',
                'value'   => array( $start_of_month, $end_of_month ),
                'compare' => 'BETWEEN',
                'type'    => 'DATE',
            );
        } elseif ( 'no' === $atts['show_past'] ) {
            $meta_query[] = array(
                'key'     => '_ateam_event_date',
                'value'   => current_time( 'Y-m-d' ),
                'compare' => '>=',
                'type'    => 'DATE',
            );
        }
        
        if ( count( $meta_query ) > 1 ) {
            $args['meta_query'] = $meta_query;
        }

        global $wpdb;
        $months = $wpdb->get_col("
            SELECT DISTINCT DATE_FORMAT(pm.meta_value, '%Y-%m') 
            FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = '_ateam_event_date'
            AND p.post_type = 'ateam_event'
            AND p.post_status = 'publish'
            ORDER BY pm.meta_value ASC
        ");

        $events = new WP_Query( $args );

        ob_start();

        if ( $events->have_posts() || ! empty( $current_month ) ) :
            $primary = get_option( 'ateam_events_primary_color', '#6C63FF' );
            global $post;
            $base_url = $post ? get_permalink( $post->ID ) : home_url( $_SERVER['REQUEST_URI'] );
            // Strip any existing query args from base URL just in case
            $base_url = strtok( $base_url, '?' );
            ?>
            <div class="ateam-events-container" data-columns="<?php echo esc_attr( $atts['columns'] ); ?>">
                <?php if ( ! empty( $months ) ) : ?>
                    <div class="ateam-month-filter">
                        <a href="<?php echo esc_url( $base_url ); ?>" class="ateam-month-chip <?php echo empty( $current_month ) ? 'active' : ''; ?>">
                            <?php _e( 'All', 'ateam-events-pro' ); ?>
                        </a>
                        <?php foreach ( $months as $month_val ) : 
                            if ( empty($month_val) ) continue;
                            $month_label = date_i18n( 'M Y', strtotime( $month_val . '-01' ) );
                            $is_active = ( $current_month === $month_val ) ? 'active' : '';
                            $url = add_query_arg( 'event_month', $month_val, $base_url );
                            ?>
                            <a href="<?php echo esc_url( $url ); ?>" class="ateam-month-chip <?php echo esc_attr( $is_active ); ?>">
                                <?php echo esc_html( $month_label ); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ( $events->have_posts() ) : ?>
                <div class="ateam-events-grid">
                    <?php while ( $events->have_posts() ) : $events->the_post();
                        $this->render_event_card( get_the_ID() );
                    endwhile; ?>
                </div>
                <?php if ( $events->max_num_pages > 1 ) : ?>
                    <div class="ateam-pagination-wrap">
                        <?php
                        $big = 999999999; // need an unlikely integer
                        echo paginate_links( array(
                            'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                            'format'    => '?paged=%#%',
                            'current'   => max( 1, get_query_var( 'paged' ) ),
                            'total'     => $events->max_num_pages,
                            'prev_text' => __( '&laquo; Prev', 'ateam-events-pro' ),
                            'next_text' => __( 'Next &raquo;', 'ateam-events-pro' ),
                        ) );
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php else : ?>
                    <div class="ateam-events-empty">
                        <div class="ateam-events-empty-icon">
                            <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
                                <rect x="12" y="16" width="56" height="48" rx="8" stroke="currentColor" stroke-width="3" fill="none"/>
                                <line x1="12" y1="30" x2="68" y2="30" stroke="currentColor" stroke-width="3"/>
                            </svg>
                        </div>
                        <h3><?php _e( 'No Events Found', 'ateam-events-pro' ); ?></h3>
                        <p><?php _e( 'There are no events for the selected month.', 'ateam-events-pro' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        else :
            ?>
            <div class="ateam-events-empty">
                <div class="ateam-events-empty-icon">
                    <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
                        <rect x="12" y="16" width="56" height="48" rx="8" stroke="currentColor" stroke-width="3" fill="none"/>
                        <line x1="12" y1="30" x2="68" y2="30" stroke="currentColor" stroke-width="3"/>
                        <line x1="28" y1="10" x2="28" y2="22" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        <line x1="52" y1="10" x2="52" y2="22" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        <circle cx="40" cy="46" r="8" stroke="currentColor" stroke-width="2.5" fill="none"/>
                        <line x1="46" y1="52" x2="52" y2="58" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <h3><?php _e( 'No Upcoming Events', 'ateam-events-pro' ); ?></h3>
                <p><?php _e( 'Check back soon for exciting events!', 'ateam-events-pro' ); ?></p>
            </div>
            <?php
        endif;

        wp_reset_postdata();

        $html = ob_get_clean();
        set_transient( $cache_key, $html, 12 * HOUR_IN_SECONDS );

        return $html;
    }

    /**
     * Events List Shortcode
     * Usage: [ateam_events_list count="10" show_past="no"]
     */
    public function render_events_list( $atts ) {
        $atts = shortcode_atts( array(
            'count'     => 10,
            'category'  => '',
            'show_past' => 'no',
        ), $atts, 'ateam_events_list' );

        $args = array(
            'post_type'      => 'ateam_event',
            'posts_per_page' => intval( $atts['count'] ),
            'post_status'    => 'publish',
            'meta_key'       => '_ateam_event_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
        );

        if ( 'no' === $atts['show_past'] ) {
            $args['meta_query'] = array(
                array(
                    'key'     => '_ateam_event_date',
                    'value'   => current_time( 'Y-m-d' ),
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            );
        }

        $events = new WP_Query( $args );
        ob_start();

        if ( $events->have_posts() ) :
            ?>
            <div class="ateam-events-list-container">
                <?php while ( $events->have_posts() ) : $events->the_post();
                    $event_id   = get_the_ID();
                    $event_date = get_post_meta( $event_id, '_ateam_event_date', true );
                    $start_time = get_post_meta( $event_id, '_ateam_event_start_time', true );
                    $venue      = get_post_meta( $event_id, '_ateam_event_venue', true );
                    $address    = get_post_meta( $event_id, '_ateam_event_address', true );
                    $is_all_day = get_post_meta( $event_id, '_ateam_event_all_day', true );
                    ?>
                    <a href="<?php the_permalink(); ?>" class="ateam-event-list-item">
                        <div class="ateam-event-list-date">
                            <span class="ateam-list-month"><?php echo date_i18n( 'M', strtotime( $event_date ) ); ?></span>
                            <span class="ateam-list-day"><?php echo date_i18n( 'd', strtotime( $event_date ) ); ?></span>
                        </div>
                        <div class="ateam-event-list-info">
                            <h4 class="ateam-event-list-title"><?php the_title(); ?></h4>
                            <div class="ateam-event-list-meta">
                                <?php if ( $start_time && ! $is_all_day ) : ?>
                                    <span class="ateam-list-time">
                                        <span class="dashicons dashicons-clock"></span>
                                        <?php echo date_i18n( get_option( 'ateam_events_time_format', 'g:i A' ), strtotime( $start_time ) ); ?>
                                    </span>
                                <?php elseif ( $is_all_day ) : ?>
                                    <span class="ateam-list-time">
                                        <span class="dashicons dashicons-clock"></span>
                                        <?php _e( 'All Day', 'ateam-events-pro' ); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ( $venue ) : ?>
                                    <span class="ateam-list-venue">
                                        <span class="dashicons dashicons-location"></span>
                                        <?php echo esc_html( $venue ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="ateam-event-list-thumb">
                                <?php the_post_thumbnail( 'thumbnail' ); ?>
                            </div>
                        <?php else : 
                            $default_img_id = get_option( 'ateam_events_default_image' );
                            if ( $default_img_id ) : ?>
                            <div class="ateam-event-list-thumb">
                                <?php echo wp_get_attachment_image( $default_img_id, 'thumbnail' ); ?>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>

                    </a>
                <?php endwhile; ?>
            </div>
            <?php
        else :
            ?>
            <div class="ateam-events-empty">
                <p><?php _e( 'No upcoming events at this time.', 'ateam-events-pro' ); ?></p>
            </div>
            <?php
        endif;

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Events Calendar Shortcode (simple monthly grid)
     * Usage: [ateam_events_calendar]
     */
    public function render_events_calendar( $atts ) {
        $atts = shortcode_atts( array(
            'month' => current_time( 'n' ),
            'year'  => current_time( 'Y' ),
        ), $atts, 'ateam_events_calendar' );

        $month = intval( $atts['month'] );
        $year  = intval( $atts['year'] );

        // Fetch events for this month
        $first_day = sprintf( '%04d-%02d-01', $year, $month );
        $last_day  = date( 'Y-m-t', strtotime( $first_day ) );

        $events = new WP_Query( array(
            'post_type'      => 'ateam_event',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => '_ateam_event_date',
                    'value'   => array( $first_day, $last_day ),
                    'compare' => 'BETWEEN',
                    'type'    => 'DATE',
                ),
            ),
        ) );

        // Build events array by day
        $events_by_day = array();
        while ( $events->have_posts() ) {
            $events->the_post();
            $event_date = get_post_meta( get_the_ID(), '_ateam_event_date', true );
            $day = intval( date( 'j', strtotime( $event_date ) ) );
            if ( ! isset( $events_by_day[ $day ] ) ) {
                $events_by_day[ $day ] = array();
            }
            $events_by_day[ $day ][] = array(
                'title' => get_the_title(),
                'url'   => get_permalink(),
                'id'    => get_the_ID(),
            );
        }
        wp_reset_postdata();

        // Build calendar
        $days_in_month = date( 't', strtotime( $first_day ) );
        $start_day     = date( 'w', strtotime( $first_day ) ); // 0=Sun
        $month_name    = date_i18n( 'F Y', strtotime( $first_day ) );

        // Prev / next month links
        $prev_month = $month - 1;
        $prev_year  = $year;
        if ( $prev_month < 1 ) { $prev_month = 12; $prev_year--; }
        $next_month = $month + 1;
        $next_year  = $year;
        if ( $next_month > 12 ) { $next_month = 1; $next_year++; }

        ob_start();
        ?>
        <div class="ateam-events-calendar-wrap">
            <div class="ateam-calendar-header">
                <button class="ateam-cal-nav ateam-cal-prev" data-month="<?php echo $prev_month; ?>" data-year="<?php echo $prev_year; ?>">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <h3 class="ateam-cal-title"><?php echo esc_html( $month_name ); ?></h3>
                <button class="ateam-cal-nav ateam-cal-next" data-month="<?php echo $next_month; ?>" data-year="<?php echo $next_year; ?>">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>

            <table class="ateam-calendar-table">
                <thead>
                    <tr>
                        <th><?php _e( 'Sun', 'ateam-events-pro' ); ?></th>
                        <th><?php _e( 'Mon', 'ateam-events-pro' ); ?></th>
                        <th><?php _e( 'Tue', 'ateam-events-pro' ); ?></th>
                        <th><?php _e( 'Wed', 'ateam-events-pro' ); ?></th>
                        <th><?php _e( 'Thu', 'ateam-events-pro' ); ?></th>
                        <th><?php _e( 'Fri', 'ateam-events-pro' ); ?></th>
                        <th><?php _e( 'Sat', 'ateam-events-pro' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $day_count = 1;
                    $today     = intval( current_time( 'j' ) );
                    $cur_month = intval( current_time( 'n' ) );
                    $cur_year  = intval( current_time( 'Y' ) );

                    for ( $row = 0; $row < 6; $row++ ) {
                        if ( $day_count > $days_in_month ) break;
                        echo '<tr>';
                        for ( $col = 0; $col < 7; $col++ ) {
                            if ( ( $row === 0 && $col < $start_day ) || $day_count > $days_in_month ) {
                                echo '<td class="ateam-cal-empty"></td>';
                            } else {
                                $is_today = ( $day_count === $today && $month === $cur_month && $year === $cur_year );
                                $has_events = isset( $events_by_day[ $day_count ] );
                                $classes = 'ateam-cal-day';
                                if ( $is_today ) $classes .= ' ateam-cal-today';
                                if ( $has_events ) $classes .= ' ateam-cal-has-events';

                                echo '<td class="' . $classes . '">';
                                echo '<span class="ateam-cal-day-num">' . $day_count . '</span>';
                                if ( $has_events ) {
                                    echo '<div class="ateam-cal-events">';
                                    foreach ( $events_by_day[ $day_count ] as $evt ) {
                                        echo '<a href="' . esc_url( $evt['url'] ) . '" class="ateam-cal-event-dot" title="' . esc_attr( $evt['title'] ) . '">' . esc_html( wp_trim_words( $evt['title'], 3 ) ) . '</a>';
                                    }
                                    echo '</div>';
                                }
                                echo '</td>';
                                $day_count++;
                            }
                        }
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Hero Slider Shortcode
     * Usage: [ateam_events_hero_slider count="5" category=""]
     */
    public function render_hero_slider( $atts ) {
        $atts = shortcode_atts( array(
            'count'    => 5,
            'category' => '',
        ), $atts, 'ateam_events_hero_slider' );

        $args = array(
            'post_type'      => 'ateam_event',
            'posts_per_page' => intval( $atts['count'] ),
            'post_status'    => 'publish',
            'meta_key'       => '_ateam_event_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => array(
                array(
                    'key'     => '_ateam_event_date',
                    'value'   => current_time( 'Y-m-d' ),
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            ),
        );

        if ( ! empty( $atts['category'] ) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'event_category',
                    'field'    => 'slug',
                    'terms'    => array_map( 'trim', explode( ',', $atts['category'] ) ),
                ),
            );
        }

        $events = new WP_Query( $args );

        ob_start();

        if ( $events->have_posts() ) :
            ?>
            <div class="ateam-hero-slider-wrap">
                <div class="ateam-hero-slider-container">
                    <div class="ateam-hero-slider-track">
                        <?php while ( $events->have_posts() ) : $events->the_post();
                            $event_id    = get_the_ID();
                            $event_date  = get_post_meta( $event_id, '_ateam_event_date', true );
                            $start_time  = get_post_meta( $event_id, '_ateam_event_start_time', true );
                            $venue       = get_post_meta( $event_id, '_ateam_event_venue', true );
                            $is_all_day  = get_post_meta( $event_id, '_ateam_event_all_day', true );
                            $time_format = get_option( 'ateam_events_time_format', 'g:i A' );
                            ?>
                            <div class="ateam-hero-slide">
                                <a href="<?php the_permalink(); ?>" class="ateam-hero-slide-link">
                                    <div class="ateam-hero-slide-image">
                                        <?php if ( has_post_thumbnail() ) : ?>
                                            <?php the_post_thumbnail( 'full' ); ?>
                                        <?php else : 
                                            $default_img_id = get_option( 'ateam_events_default_image' );
                                            if ( $default_img_id ) : ?>
                                                <?php echo wp_get_attachment_image( $default_img_id, 'full' ); ?>
                                            <?php else : ?>
                                                <div class="ateam-hero-slide-placeholder"></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <div class="ateam-hero-slide-overlay"></div>
                                    </div>

                                    <div class="ateam-hero-slide-content">
                                        <div class="ateam-hero-slide-actions">
                                            <span class="ateam-hero-action-btn"><span class="dashicons dashicons-controls-play"></span></span>
                                            <span class="ateam-hero-action-btn"><span class="dashicons dashicons-heart"></span></span>
                                        </div>
                                        <h2 class="ateam-hero-slide-title"><?php the_title(); ?></h2>
                                        <div class="ateam-hero-slide-meta">
                                            <span class="ateam-hero-slide-date">
                                                <?php 
                                                $today = current_time( 'Y-m-d' );
                                                if ( $event_date === $today ) {
                                                    _e( 'TODAY', 'ateam-events-pro' );
                                                } else {
                                                    echo date_i18n( 'l, F j', strtotime( $event_date ) );
                                                }
                                                if ( $start_time && ! $is_all_day ) {
                                                    echo ' | ' . date_i18n( $time_format, strtotime( $start_time ) );
                                                }
                                                ?>
                                            </span>
                                            <?php if ( $venue ) : ?>
                                                <span class="ateam-hero-slide-venue"><?php echo esc_html( $venue ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <button class="ateam-hero-slider-nav ateam-nav-prev"><span class="dashicons dashicons-arrow-left-alt2"></span></button>
                <button class="ateam-hero-slider-nav ateam-nav-next"><span class="dashicons dashicons-arrow-right-alt2"></span></button>

                <!-- Thumbnail Pagination -->
                <div class="ateam-hero-slider-thumbnails">
                    <?php 
                    $thumb_index = 0;
                    while ( $events->have_posts() ) : $events->the_post();
                        $thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
                        if ( ! $thumb_url ) {
                            $default_img_id = get_option( 'ateam_events_default_image' );
                            if ( $default_img_id ) {
                                $thumb_url = wp_get_attachment_image_url( $default_img_id, 'thumbnail' );
                            } else {
                                $thumb_url = ATEAM_EVENTS_PRO_PLUGIN_URL . 'assets/images/placeholder.jpg';
                            }
                        }
                        ?>
                        <button class="ateam-hero-thumb <?php echo $thumb_index === 0 ? 'active' : ''; ?>" data-index="<?php echo $thumb_index; ?>">
                            <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>">
                        </button>
                        <?php 
                        $thumb_index++;
                    endwhile; 
                    ?>
                </div>
            </div>
            <?php
        endif;

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Render an individual event card
     */
    private function render_event_card( $event_id ) {
        $event_date  = get_post_meta( $event_id, '_ateam_event_date', true );
        $start_time  = get_post_meta( $event_id, '_ateam_event_start_time', true );
        $end_time    = get_post_meta( $event_id, '_ateam_event_end_time', true );
        $venue       = get_post_meta( $event_id, '_ateam_event_venue', true );
        $city        = get_post_meta( $event_id, '_ateam_event_city', true );
        $is_all_day  = get_post_meta( $event_id, '_ateam_event_all_day', true );
        $is_virtual  = get_post_meta( $event_id, '_ateam_event_virtual', true );
        $date_format = get_option( 'ateam_events_date_format', 'F j, Y' );
        $time_format = get_option( 'ateam_events_time_format', 'g:i A' );

        $categories = get_the_terms( $event_id, 'event_category' );
        ?>
        <article class="ateam-event-card" id="ateam-event-<?php echo $event_id; ?>">
            <a href="<?php echo get_permalink( $event_id ); ?>" class="ateam-card-link">
                <div class="ateam-card-image">
                    <?php if ( has_post_thumbnail( $event_id ) ) : ?>
                        <?php echo get_the_post_thumbnail( $event_id, 'medium_large', array( 'loading' => 'lazy' ) ); ?>
                    <?php else : 
                        $default_img_id = get_option( 'ateam_events_default_image' );
                        if ( $default_img_id ) : ?>
                            <?php echo wp_get_attachment_image( $default_img_id, 'medium_large', array( 'loading' => 'lazy' ) ); ?>
                        <?php else : ?>
                            <div class="ateam-card-placeholder">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                                    <rect x="6" y="10" width="36" height="28" rx="4" stroke="currentColor" stroke-width="2"/>
                                    <line x1="6" y1="18" x2="42" y2="18" stroke="currentColor" stroke-width="2"/>
                                    <line x1="16" y1="6" x2="16" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="32" y1="6" x2="32" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                        <?php endif; ?>
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
                    <?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
                        <div class="ateam-card-categories">
                            <?php foreach ( $categories as $cat ) : ?>
                                <span class="ateam-card-category"><?php echo esc_html( $cat->name ); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h3 class="ateam-card-title"><?php echo get_the_title( $event_id ); ?></h3>

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
                                <span>
                                    <?php echo date_i18n( $time_format, strtotime( $start_time ) ); ?>
                                    <?php if ( $end_time ) : ?>
                                        – <?php echo date_i18n( $time_format, strtotime( $end_time ) ); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php elseif ( $is_all_day ) : ?>
                            <div class="ateam-card-meta-item">
                                <span class="dashicons dashicons-clock"></span>
                                <span><?php _e( 'All Day', 'ateam-events-pro' ); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ( $venue || $city ) : ?>
                            <div class="ateam-card-meta-item">
                                <span class="dashicons dashicons-location"></span>
                                <span>
                                    <?php
                                    $location_parts = array_filter( array( $venue, $city ) );
                                    echo esc_html( implode( ', ', $location_parts ) );
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ( has_excerpt( $event_id ) ) : ?>
                        <p class="ateam-card-excerpt"><?php echo wp_trim_words( get_the_excerpt( $event_id ), 18 ); ?></p>
                    <?php endif; ?>
                </div>
            </a>
        </article>
        <?php
    }

    /**
     * Single Event Template
     * Usage: [ateam_single_event id="123"]
     */
    public function render_single_event( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts, 'ateam_single_event' );

        if ( ! $atts['id'] ) {
            return '';
        }

        ob_start();
        $this->render_event_card( intval( $atts['id'] ) );
        return ob_get_clean();
    }

    /**
     * Override single event template
     */
    public function single_event_template( $template ) {
        global $post;

        if ( 'ateam_event' === $post->post_type ) {
            $custom = ATEAM_EVENTS_PRO_PLUGIN_DIR . 'templates/single-event.php';
            if ( file_exists( $custom ) ) {
                return $custom;
            }
        }

        return $template;
    }

    /**
     * Override archive event template
     */
    public function archive_event_template( $template ) {
        if ( is_post_type_archive( 'ateam_event' ) ) {
            $custom = ATEAM_EVENTS_PRO_PLUGIN_DIR . 'templates/archive-event.php';
            if ( file_exists( $custom ) ) {
                return $custom;
            }
        }

        return $template;
    }

}
