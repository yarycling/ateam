<?php
/**
 * Event Meta Boxes
 *
 * @package ATeam_Events_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ATeam_Events_Meta {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_ateam_event', array( $this, 'save_meta' ), 10, 2 );
        add_action( 'deleted_post', array( $this, 'invalidate_cache' ) );
    }

    /**
     * Register meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'ateam_event_details',
            __( '📅 Event Details', 'ateam-events-pro' ),
            array( $this, 'render_event_details_metabox' ),
            'ateam_event',
            'normal',
            'high'
        );

        add_meta_box(
            'ateam_event_location',
            __( '📍 Event Location', 'ateam-events-pro' ),
            array( $this, 'render_location_metabox' ),
            'ateam_event',
            'normal',
            'high'
        );
    }

    /**
     * Render Event Details meta box
     */
    public function render_event_details_metabox( $post ) {
        wp_nonce_field( 'ateam_events_meta_nonce', 'ateam_events_nonce_field' );

        $event_date  = get_post_meta( $post->ID, '_ateam_event_date', true );
        $end_date    = get_post_meta( $post->ID, '_ateam_event_end_date', true );
        $start_time  = get_post_meta( $post->ID, '_ateam_event_start_time', true );
        $end_time    = get_post_meta( $post->ID, '_ateam_event_end_time', true );
        $is_all_day  = get_post_meta( $post->ID, '_ateam_event_all_day', true );
        $is_multiday = get_post_meta( $post->ID, '_ateam_event_multiday', true );
        ?>
        <div class="ateam-meta-wrapper">
            <div class="ateam-meta-row">
                <div class="ateam-meta-field ateam-meta-half">
                    <label for="ateam_event_date">
                        <span class="ateam-field-icon dashicons dashicons-calendar-alt"></span>
                        <?php _e( 'Event Date', 'ateam-events-pro' ); ?> <span class="ateam-required">*</span>
                    </label>
                    <input
                        type="date"
                        id="ateam_event_date"
                        name="ateam_event_date"
                        value="<?php echo esc_attr( $event_date ); ?>"
                        class="ateam-input"
                        required
                    />
                </div>
                <div class="ateam-meta-field ateam-meta-half" id="ateam-end-date-field" style="<?php echo $is_multiday ? '' : 'display:none;'; ?>">
                    <label for="ateam_event_end_date">
                        <span class="ateam-field-icon dashicons dashicons-calendar"></span>
                        <?php _e( 'End Date', 'ateam-events-pro' ); ?>
                    </label>
                    <input
                        type="date"
                        id="ateam_event_end_date"
                        name="ateam_event_end_date"
                        value="<?php echo esc_attr( $end_date ); ?>"
                        class="ateam-input"
                    />
                </div>
            </div>

            <div class="ateam-meta-row ateam-checkbox-row">
                <label class="ateam-toggle">
                    <input type="checkbox" name="ateam_event_multiday" value="1" <?php checked( $is_multiday, '1' ); ?> id="ateam_event_multiday" />
                    <span class="ateam-toggle-slider"></span>
                    <span class="ateam-toggle-label"><?php _e( 'Multi-day event', 'ateam-events-pro' ); ?></span>
                </label>
                <label class="ateam-toggle">
                    <input type="checkbox" name="ateam_event_all_day" value="1" <?php checked( $is_all_day, '1' ); ?> id="ateam_event_all_day" />
                    <span class="ateam-toggle-slider"></span>
                    <span class="ateam-toggle-label"><?php _e( 'All day event', 'ateam-events-pro' ); ?></span>
                </label>
            </div>

            <div class="ateam-meta-row" id="ateam-time-fields" style="<?php echo $is_all_day ? 'display:none;' : ''; ?>">
                <div class="ateam-meta-field ateam-meta-half">
                    <label for="ateam_event_start_time">
                        <span class="ateam-field-icon dashicons dashicons-clock"></span>
                        <?php _e( 'Start Time', 'ateam-events-pro' ); ?>
                    </label>
                    <input
                        type="time"
                        id="ateam_event_start_time"
                        name="ateam_event_start_time"
                        value="<?php echo esc_attr( $start_time ); ?>"
                        class="ateam-input"
                    />
                </div>
                <div class="ateam-meta-field ateam-meta-half">
                    <label for="ateam_event_end_time">
                        <span class="ateam-field-icon dashicons dashicons-clock"></span>
                        <?php _e( 'End Time', 'ateam-events-pro' ); ?>
                    </label>
                    <input
                        type="time"
                        id="ateam_event_end_time"
                        name="ateam_event_end_time"
                        value="<?php echo esc_attr( $end_time ); ?>"
                        class="ateam-input"
                    />
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Location meta box
     */
    public function render_location_metabox( $post ) {
        $venue   = get_post_meta( $post->ID, '_ateam_event_venue', true );
        $address = get_post_meta( $post->ID, '_ateam_event_address', true );
        $city    = get_post_meta( $post->ID, '_ateam_event_city', true );
        $state   = get_post_meta( $post->ID, '_ateam_event_state', true );
        $country = get_post_meta( $post->ID, '_ateam_event_country', true );
        $zip     = get_post_meta( $post->ID, '_ateam_event_zip', true );
        $is_virtual = get_post_meta( $post->ID, '_ateam_event_virtual', true );
        $virtual_url = get_post_meta( $post->ID, '_ateam_event_virtual_url', true );
        $gcal_id     = get_post_meta( $post->ID, '_ateam_event_gcal_id', true );
        $meet_link   = get_post_meta( $post->ID, '_ateam_event_gcal_meet_link', true );

        ?>
        <div class="ateam-meta-wrapper">
            <div class="ateam-meta-row ateam-checkbox-row">
                <label class="ateam-toggle">
                    <input type="checkbox" name="ateam_event_virtual" value="1" <?php checked( $is_virtual, '1' ); ?> id="ateam_event_virtual" />
                    <span class="ateam-toggle-slider"></span>
                    <span class="ateam-toggle-label"><?php _e( 'Virtual / Online Event', 'ateam-events-pro' ); ?></span>
                </label>
            </div>

            <div id="ateam-virtual-fields" style="<?php echo $is_virtual ? '' : 'display:none;'; ?>">
                <div class="ateam-meta-row">
                    <div class="ateam-meta-field ateam-meta-full">
                        <label for="ateam_event_virtual_url">
                            <span class="ateam-field-icon dashicons dashicons-admin-links"></span>
                            <?php _e( 'Virtual Event URL', 'ateam-events-pro' ); ?>
                        </label>
                        <input
                            type="url"
                            id="ateam_event_virtual_url"
                            name="ateam_event_virtual_url"
                            value="<?php echo esc_url( $virtual_url ); ?>"
                            class="ateam-input"
                            placeholder="https://zoom.us/j/example"
                        />
                    </div>
                </div>

                <?php if ( ! empty( $meet_link ) || ! empty( $gcal_id ) ) : ?>
                <div class="ateam-meta-row">
                    <div class="ateam-meta-field ateam-meta-full">
                        <label for="ateam_event_gcal_meet_link">
                            <span class="ateam-field-icon dashicons dashicons-video-alt3"></span>
                            <?php _e( 'Google Meet Link', 'ateam-events-pro' ); ?>
                        </label>
                        <input
                            type="url"
                            id="ateam_event_gcal_meet_link"
                            name="ateam_event_gcal_meet_link"
                            value="<?php echo esc_url( $meet_link ); ?>"
                            class="ateam-input"
                            readonly
                        />
                        <p class="description"><?php _e( 'Automatically synced from Google Calendar.', 'ateam-events-pro' ); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( ! empty( $gcal_id ) ) : ?>
                <div class="ateam-meta-row">
                    <div class="ateam-meta-field ateam-meta-full">
                        <label><?php _e( 'Google Calendar ID', 'ateam-events-pro' ); ?></label>
                        <input type="text" value="<?php echo esc_attr( $gcal_id ); ?>" class="ateam-input" readonly />
                    </div>
                </div>
                <?php endif; ?>
            </div>


            <div id="ateam-location-fields" style="<?php echo $is_virtual ? 'display:none;' : ''; ?>">
                <div class="ateam-meta-row">
                    <div class="ateam-meta-field ateam-meta-full">
                        <label for="ateam_event_venue">
                            <span class="ateam-field-icon dashicons dashicons-building"></span>
                            <?php _e( 'Venue Name', 'ateam-events-pro' ); ?>
                        </label>
                        <input
                            type="text"
                            id="ateam_event_venue"
                            name="ateam_event_venue"
                            value="<?php echo esc_attr( $venue ); ?>"
                            class="ateam-input"
                            placeholder="<?php esc_attr_e( 'e.g. Convention Center', 'ateam-events-pro' ); ?>"
                        />
                    </div>
                </div>

                <div class="ateam-meta-row">
                    <div class="ateam-meta-field ateam-meta-full">
                        <label for="ateam_event_address">
                            <span class="ateam-field-icon dashicons dashicons-location"></span>
                            <?php _e( 'Street Address', 'ateam-events-pro' ); ?>
                        </label>
                        <input
                            type="text"
                            id="ateam_event_address"
                            name="ateam_event_address"
                            value="<?php echo esc_attr( $address ); ?>"
                            class="ateam-input"
                            placeholder="<?php esc_attr_e( '123 Main Street', 'ateam-events-pro' ); ?>"
                        />
                    </div>
                </div>

                <div class="ateam-meta-row">
                    <div class="ateam-meta-field ateam-meta-third">
                        <label for="ateam_event_city">
                            <?php _e( 'City', 'ateam-events-pro' ); ?>
                        </label>
                        <input
                            type="text"
                            id="ateam_event_city"
                            name="ateam_event_city"
                            value="<?php echo esc_attr( $city ); ?>"
                            class="ateam-input"
                        />
                    </div>
                    <div class="ateam-meta-field ateam-meta-third">
                        <label for="ateam_event_state">
                            <?php _e( 'State / Province', 'ateam-events-pro' ); ?>
                        </label>
                        <input
                            type="text"
                            id="ateam_event_state"
                            name="ateam_event_state"
                            value="<?php echo esc_attr( $state ); ?>"
                            class="ateam-input"
                        />
                    </div>
                    <div class="ateam-meta-field ateam-meta-third">
                        <label for="ateam_event_zip">
                            <?php _e( 'ZIP / Postal Code', 'ateam-events-pro' ); ?>
                        </label>
                        <input
                            type="text"
                            id="ateam_event_zip"
                            name="ateam_event_zip"
                            value="<?php echo esc_attr( $zip ); ?>"
                            class="ateam-input"
                        />
                    </div>
                </div>

                <div class="ateam-meta-row">
                    <div class="ateam-meta-field ateam-meta-full">
                        <label for="ateam_event_country">
                            <?php _e( 'Country', 'ateam-events-pro' ); ?>
                        </label>
                        <input
                            type="text"
                            id="ateam_event_country"
                            name="ateam_event_country"
                            value="<?php echo esc_attr( $country ); ?>"
                            class="ateam-input"
                        />
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save meta data
     */
    public function save_meta( $post_id, $post ) {
        // Verify nonce
        if ( ! isset( $_POST['ateam_events_nonce_field'] ) ||
             ! wp_verify_nonce( $_POST['ateam_events_nonce_field'], 'ateam_events_meta_nonce' ) ) {
            return;
        }

        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Text fields to save
        $text_fields = array(
            'ateam_event_date'        => '_ateam_event_date',
            'ateam_event_end_date'    => '_ateam_event_end_date',
            'ateam_event_start_time'  => '_ateam_event_start_time',
            'ateam_event_end_time'    => '_ateam_event_end_time',
            'ateam_event_venue'       => '_ateam_event_venue',
            'ateam_event_address'     => '_ateam_event_address',
            'ateam_event_city'        => '_ateam_event_city',
            'ateam_event_state'       => '_ateam_event_state',
            'ateam_event_country'     => '_ateam_event_country',
            'ateam_event_zip'         => '_ateam_event_zip',
        );

        foreach ( $text_fields as $post_key => $meta_key ) {
            if ( isset( $_POST[ $post_key ] ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $post_key ] ) );
            }
        }

        // URL fields
        if ( isset( $_POST['ateam_event_virtual_url'] ) ) {
            update_post_meta( $post_id, '_ateam_event_virtual_url', esc_url_raw( $_POST['ateam_event_virtual_url'] ) );
        }

        if ( isset( $_POST['ateam_event_gcal_meet_link'] ) ) {
            update_post_meta( $post_id, '_ateam_event_gcal_meet_link', esc_url_raw( $_POST['ateam_event_gcal_meet_link'] ) );
        }


        // Checkbox fields
        $checkboxes = array(
            'ateam_event_all_day'  => '_ateam_event_all_day',
            'ateam_event_multiday' => '_ateam_event_multiday',
            'ateam_event_virtual'  => '_ateam_event_virtual',
        );

        foreach ( $checkboxes as $post_key => $meta_key ) {
            $value = isset( $_POST[ $post_key ] ) ? '1' : '0';
            update_post_meta( $post_id, $meta_key, $value );
        }

        $this->invalidate_cache( $post_id );
    }

    /**
     * Invalidate transient caches
     */
    public function invalidate_cache( $post_id ) {
        if ( get_post_type( $post_id ) === 'ateam_event' ) {
            update_option( 'ateam_events_cache_version', time() );
        }
    }
}
