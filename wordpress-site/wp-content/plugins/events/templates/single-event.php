<?php
/**
 * Single Event Template
 *
 * @package ATeam_Events_Pro
 */

get_header();

while ( have_posts() ) : the_post();
    $event_id    = get_the_ID();
    $event_date  = get_post_meta( $event_id, '_ateam_event_date', true );
    $end_date    = get_post_meta( $event_id, '_ateam_event_end_date', true );
    $start_time  = get_post_meta( $event_id, '_ateam_event_start_time', true );
    $end_time    = get_post_meta( $event_id, '_ateam_event_end_time', true );
    $is_all_day  = get_post_meta( $event_id, '_ateam_event_all_day', true );
    $is_multiday = get_post_meta( $event_id, '_ateam_event_multiday', true );
    $is_virtual  = get_post_meta( $event_id, '_ateam_event_virtual', true );
    $virtual_url = get_post_meta( $event_id, '_ateam_event_virtual_url', true );
    $venue       = get_post_meta( $event_id, '_ateam_event_venue', true );
    $address     = get_post_meta( $event_id, '_ateam_event_address', true );
    $city        = get_post_meta( $event_id, '_ateam_event_city', true );
    $state       = get_post_meta( $event_id, '_ateam_event_state', true );
    $country     = get_post_meta( $event_id, '_ateam_event_country', true );
    $zip         = get_post_meta( $event_id, '_ateam_event_zip', true );
    $date_format = get_option( 'ateam_events_date_format', 'F j, Y' );
    $time_format = get_option( 'ateam_events_time_format', 'g:i A' );
    $primary     = get_option( 'ateam_events_primary_color', '#6C63FF' );

    $categories = get_the_terms( $event_id, 'event_category' );

    // Build location string
    $location_parts = array_filter( array( $address, $city, $state, $zip, $country ) );
    $full_address   = implode( ', ', $location_parts );

    // Check event status
    $is_past = ( $event_date && $event_date < current_time( 'Y-m-d' ) );
?>

<article class="ateam-single-event" id="ateam-event-<?php echo $event_id; ?>">

    <!-- Hero Section -->
    <div class="ateam-event-hero" style="--ateam-primary: <?php echo esc_attr( $primary ); ?>;">
        <?php if ( has_post_thumbnail() ) : ?>
            <div class="ateam-hero-image">
                <?php the_post_thumbnail( 'full' ); ?>
                <div class="ateam-hero-overlay"></div>
            </div>
        <?php else : 
            $default_img_id = get_option( 'ateam_events_default_image' );
            if ( $default_img_id ) : ?>
            <div class="ateam-hero-image">
                <?php echo wp_get_attachment_image( $default_img_id, 'full' ); ?>
                <div class="ateam-hero-overlay"></div>
            </div>
            <?php else : ?>
            <div class="ateam-hero-gradient"></div>
            <?php endif; ?>
        <?php endif; ?>


        <div class="ateam-hero-content">
            <div class="ateam-hero-inner">
                <?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
                    <div class="ateam-event-categories">
                        <?php foreach ( $categories as $cat ) : ?>
                            <span class="ateam-event-cat-tag"><?php echo esc_html( $cat->name ); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h1 class="ateam-event-title"><?php the_title(); ?></h1>

                <div class="ateam-event-hero-meta">
                    <?php if ( $event_date ) : ?>
                        <div class="ateam-hero-meta-item">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <span>
                                <?php echo date_i18n( $date_format, strtotime( $event_date ) ); ?>
                                <?php if ( $is_multiday && $end_date ) : ?>
                                    – <?php echo date_i18n( $date_format, strtotime( $end_date ) ); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if ( $start_time && ! $is_all_day ) : ?>
                        <div class="ateam-hero-meta-item">
                            <span class="dashicons dashicons-clock"></span>
                            <span>
                                <?php echo date_i18n( $time_format, strtotime( $start_time ) ); ?>
                                <?php if ( $end_time ) : ?>
                                    – <?php echo date_i18n( $time_format, strtotime( $end_time ) ); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php elseif ( $is_all_day ) : ?>
                        <div class="ateam-hero-meta-item">
                            <span class="dashicons dashicons-clock"></span>
                            <span><?php _e( 'All Day Event', 'ateam-events-pro' ); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ( $venue ) : ?>
                        <div class="ateam-hero-meta-item">
                            <span class="dashicons dashicons-location"></span>
                            <span><?php echo esc_html( $venue ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ( $is_past ) : ?>
                    <div class="ateam-event-past-badge"><?php _e( 'This event has ended', 'ateam-events-pro' ); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Event Details -->
    <div class="ateam-event-details-wrap">
        <div class="ateam-event-main">

            <!-- Description -->
            <div class="ateam-event-section ateam-event-description">
                <h2><?php _e( 'About This Event', 'ateam-events-pro' ); ?></h2>
                <div class="ateam-event-content">
                    <?php the_content(); ?>
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <aside class="ateam-event-sidebar">

            <!-- Date & Time Card -->
            <div class="ateam-sidebar-card">
                <div class="ateam-sidebar-card-header">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <h3><?php _e( 'Date & Time', 'ateam-events-pro' ); ?></h3>
                </div>
                <div class="ateam-sidebar-card-body">
                    <?php if ( $event_date ) : ?>
                        <div class="ateam-detail-row">
                            <strong><?php _e( 'Date', 'ateam-events-pro' ); ?></strong>
                            <span>
                                <?php echo date_i18n( $date_format, strtotime( $event_date ) ); ?>
                                <?php if ( $is_multiday && $end_date ) : ?>
                                    <br><?php _e( 'to', 'ateam-events-pro' ); ?> <?php echo date_i18n( $date_format, strtotime( $end_date ) ); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! $is_all_day && $start_time ) : ?>
                        <div class="ateam-detail-row">
                            <strong><?php _e( 'Time', 'ateam-events-pro' ); ?></strong>
                            <span>
                                <?php echo date_i18n( $time_format, strtotime( $start_time ) ); ?>
                                <?php if ( $end_time ) : ?>
                                    – <?php echo date_i18n( $time_format, strtotime( $end_time ) ); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if ( $is_all_day ) : ?>
                        <div class="ateam-detail-row">
                            <strong><?php _e( 'Duration', 'ateam-events-pro' ); ?></strong>
                            <span><?php _e( 'All Day', 'ateam-events-pro' ); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Add to Calendar -->
                    <?php if ( $event_date && ! $is_past ) : ?>
                        <div class="ateam-add-to-cal">
                            <button class="ateam-btn ateam-btn-outline ateam-btn-sm ateam-add-cal-btn"
                                data-title="<?php echo esc_attr( get_the_title() ); ?>"
                                data-date="<?php echo esc_attr( $event_date ); ?>"
                                data-start="<?php echo esc_attr( $start_time ); ?>"
                                data-end="<?php echo esc_attr( $end_time ); ?>"
                                data-location="<?php echo esc_attr( $full_address ); ?>"
                                data-description="<?php echo esc_attr( wp_strip_all_tags( get_the_excerpt() ) ); ?>">
                                <span class="dashicons dashicons-plus-alt"></span>
                                <?php _e( 'Add to Calendar', 'ateam-events-pro' ); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Location Card -->
            <?php if ( $venue || $full_address || $is_virtual ) : ?>
                <div class="ateam-sidebar-card">
                    <div class="ateam-sidebar-card-header">
                        <span class="dashicons dashicons-location"></span>
                        <h3><?php echo $is_virtual ? __( 'Virtual Event', 'ateam-events-pro' ) : __( 'Location', 'ateam-events-pro' ); ?></h3>
                    </div>
                    <div class="ateam-sidebar-card-body">
                        <?php if ( $is_virtual ) : ?>
                            <p><?php _e( 'This is a virtual / online event.', 'ateam-events-pro' ); ?></p>
                            <?php if ( $virtual_url && ! $is_past ) : ?>
                                <a href="<?php echo esc_url( $virtual_url ); ?>" class="ateam-btn ateam-btn-primary ateam-btn-sm" target="_blank" rel="noopener">
                                    <span class="dashicons dashicons-video-alt3"></span>
                                    <?php _e( 'Join Event', 'ateam-events-pro' ); ?>
                                </a>
                            <?php endif; ?>
                        <?php else : ?>
                            <?php if ( $venue ) : ?>
                                <div class="ateam-detail-row">
                                    <strong><?php _e( 'Venue', 'ateam-events-pro' ); ?></strong>
                                    <span><?php echo esc_html( $venue ); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ( $full_address ) : ?>
                                <div class="ateam-detail-row">
                                    <strong><?php _e( 'Address', 'ateam-events-pro' ); ?></strong>
                                    <span><?php echo esc_html( $full_address ); ?></span>
                                </div>
                                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode( $full_address ); ?>" class="ateam-btn ateam-btn-outline ateam-btn-sm" target="_blank" rel="noopener">
                                    <span class="dashicons dashicons-location-alt"></span>
                                    <?php _e( 'Get Directions', 'ateam-events-pro' ); ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Share Card -->
            <div class="ateam-sidebar-card">
                <div class="ateam-sidebar-card-header">
                    <span class="dashicons dashicons-share"></span>
                    <h3><?php _e( 'Share Event', 'ateam-events-pro' ); ?></h3>
                </div>
                <div class="ateam-sidebar-card-body">
                    <div class="ateam-share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>"
                           class="ateam-share-btn ateam-share-fb" target="_blank" rel="noopener" title="Share on Facebook">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode( get_the_title() ); ?>&url=<?php echo urlencode( get_permalink() ); ?>"
                           class="ateam-share-btn ateam-share-tw" target="_blank" rel="noopener" title="Share on Twitter">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode( get_the_title() . ' ' . get_permalink() ); ?>"
                           class="ateam-share-btn ateam-share-wa" target="_blank" rel="noopener" title="Share on WhatsApp">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        <button class="ateam-share-btn ateam-share-copy" title="Copy Link" data-url="<?php echo esc_url( get_permalink() ); ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</article>

<?php
endwhile;

get_footer();
