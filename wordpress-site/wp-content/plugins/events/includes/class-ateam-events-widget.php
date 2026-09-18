<?php
/**
 * WordPress Widget for Upcoming Events
 *
 * @package ATeam_Events_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ATeam_Events_Widget {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
    }

    public function register_widgets() {
        register_widget( 'ATeam_Upcoming_Events_Widget' );
    }
}

/**
 * Upcoming Events Widget
 */
class ATeam_Upcoming_Events_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'ateam_upcoming_events',
            __( 'ATeam Upcoming Events', 'ateam-events-pro' ),
            array(
                'description' => __( 'Display upcoming events in a sidebar widget.', 'ateam-events-pro' ),
                'classname'   => 'ateam-upcoming-events-widget',
            )
        );
    }

    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : __( 'Upcoming Events', 'ateam-events-pro' ) );
        $count = isset( $instance['count'] ) ? intval( $instance['count'] ) : 5;

        $events = new WP_Query( array(
            'post_type'      => 'ateam_event',
            'posts_per_page' => $count,
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
        ) );

        echo $args['before_widget'];

        if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        if ( $events->have_posts() ) :
            echo '<ul class="ateam-widget-events-list">';
            while ( $events->have_posts() ) : $events->the_post();
                $event_date = get_post_meta( get_the_ID(), '_ateam_event_date', true );
                $venue      = get_post_meta( get_the_ID(), '_ateam_event_venue', true );
                ?>
                <li class="ateam-widget-event-item">
                    <a href="<?php the_permalink(); ?>">
                        <div class="ateam-widget-event-date">
                            <span class="ateam-w-month"><?php echo date_i18n( 'M', strtotime( $event_date ) ); ?></span>
                            <span class="ateam-w-day"><?php echo date_i18n( 'd', strtotime( $event_date ) ); ?></span>
                        </div>
                        <div class="ateam-widget-event-info">
                            <strong><?php the_title(); ?></strong>
                            <?php if ( $venue ) : ?>
                                <small><?php echo esc_html( $venue ); ?></small>
                            <?php endif; ?>
                        </div>
                    </a>
                </li>
                <?php
            endwhile;
            echo '</ul>';
        else :
            echo '<p class="ateam-widget-no-events">' . __( 'No upcoming events.', 'ateam-events-pro' ) . '</p>';
        endif;

        wp_reset_postdata();

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title = isset( $instance['title'] ) ? $instance['title'] : __( 'Upcoming Events', 'ateam-events-pro' );
        $count = isset( $instance['count'] ) ? intval( $instance['count'] ) : 5;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ateam-events-pro' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Number of events:', 'ateam-events-pro' ); ?></label>
            <input class="tiny-text" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="number" min="1" max="20" value="<?php echo esc_attr( $count ); ?>" />
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance          = array();
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        $instance['count'] = intval( $new_instance['count'] );
        return $instance;
    }
}
