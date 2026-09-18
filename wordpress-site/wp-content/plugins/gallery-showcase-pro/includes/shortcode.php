<?php

/**
 * Frontend rendering HTML code.
 * Main gallery_showcase shortcode file.
 * @since Gallery Showcase Pro 1.0
 */
if (!defined('ABSPATH')) {
    exit();
}

/**
 * Add Shortcode gallery_showcase
 *
 * @version 1.0.0
 * @since   1.0.0
 * @return  HTML
 */
add_shortcode('gallery_showcase', 'gspwp_gallery_showcase');
if (!function_exists('gspwp_gallery_showcase')) {

    function gspwp_gallery_showcase($atts) {
        extract($atts = shortcode_atts(
                array(
            'layout' => '',
                ), $atts, 'gallery_showcase'));
        ob_start();

        if (!isset($atts['layout'])) {
            esc_html_e('You have to insert a Layout name in the shortcode', 'gallery-showcase-pro');
        } elseif (isset($atts['layout']) && $atts['layout'] == '') {
            esc_html_e('You have to insert a Layout name in the shortcode', 'gallery-showcase-pro');
        } else {
            global $wp_query;
            $argc = array(
                'name' => $layout,
                'post_type' => 'gs_layouts',
                'post_status' => 'publish',
                'posts_per_page' => 1
            );

            $post_data = get_posts($argc);

            if (!empty($post_data)) {
                foreach ($post_data as $post) {
                    setup_postdata($post);
                    $gspwp_options = get_post_meta($post->ID, 'gs_optoins', true);
                }

                if (!empty($gspwp_options)) {
                    $post_layout_id = $post_data[0]->ID;
                    $display_arrow = $display_navigation = $gspwp_slider_class = $gspwp_arrow_style = '';
                    $gspwp_layout = (isset($gspwp_options['layout_type']) && $gspwp_options['layout_type'] != '') ? $gspwp_options['layout_type'] . '_layout' : 'gird_layout';
                    $gspwp_clm_desktop = (isset($gspwp_options['column_desktop']) && $gspwp_options['column_desktop'] != '') ? $gspwp_options['column_desktop'] : '';
                    $gspwp_clm_small_desktop = (isset($gspwp_options['column_small_desktop']) && $gspwp_options['column_small_desktop'] != '') ? $gspwp_options['column_small_desktop'] : '';
                    $gspwp_clm_tablet = (isset($gspwp_options['column_tablet']) && $gspwp_options['column_tablet'] != '') ? $gspwp_options['column_tablet'] : '';
                    $gspwp_clm_mobile = (isset($gspwp_options['column_mobile']) && $gspwp_options['column_mobile'] != '') ? $gspwp_options['column_mobile'] : '';
                    $post_thumbnail = (isset($gspwp_options['image_size']) && $gspwp_options['image_size'] != '') ? $gspwp_options['image_size'] : 'full';
                    $layout_effect_group = (isset($gspwp_options['layout_effect_group']) && $gspwp_options['layout_effect_group'] != '') ? $gspwp_options['layout_effect_group'] : '';
                    $layout_effect = (isset($gspwp_options['layout_effect']) && $gspwp_options['layout_effect'] != '') ? $gspwp_options['layout_effect'] : '';
                    $layout_animation = (isset($gspwp_options['layout_animation']) && $gspwp_options['layout_animation'] != '') ? $gspwp_options['layout_animation'] : '';
                    $gspwp_animate = ($layout_animation != '') ? 'gspwp_animate' : '';
                    $display_arrow = (isset($gspwp_options['display_arrow'])) ? $gspwp_options['display_arrow'] : 1;
                    $gspwp_arrow_style = (isset($gspwp_options['gs_arrow_style']) && $gspwp_options['gs_arrow_style'] != '') ? $gspwp_options['gs_arrow_style'] : 'style-1';
                    $display_navigation = (isset($gspwp_options['display_navigation'])) ? $gspwp_options['display_navigation'] : 1;
                    $gspwp_navigation_style = (isset($gspwp_options['gs_navigation_style']) && $gspwp_options['gs_navigation_style'] != '') ? $gspwp_options['gs_navigation_style'] : 'style-1';

                    $gspwp_clm = 'gspwp-clm gspwp-clm-desktop-' . $gspwp_clm_desktop . ' gspwp-clm-small-desktop-' . $gspwp_clm_small_desktop . ' gspwp-clm-tablet-' . $gspwp_clm_tablet . ' gspwp-clm-mobile-' . $gspwp_clm_mobile;

                    $gspwp_gallery_image = get_post_meta($post->ID, 'gs_gallery_images', true);
                    $gspwp_gallery_details = get_post_meta($post->ID, 'gs_gallery_details', true);

                    // dynamic post img set array create
                    if(isset($gspwp_options['dynamic_post_content']) && $gspwp_options['dynamic_post_content'] == 0 ) {
                        
                        $custom_post_type = (isset($gspwp_options['custom_post_type'])) ? $gspwp_options['custom_post_type'] : 'post';
                        
                        $args = array(  
                            'post_type' => $custom_post_type,
                            'post_status' => 'publish',
                            'posts_per_page' => $gspwp_options['set_post_limit'], 
                            'orderby' => 'title', 
                            'order' => 'ASC',
                        );
                        
                        $query = new WP_Query( $args ); 
                        
                        $dynamic_post_feature_img_array = array();
                        $dynamic_post_id = array();
                        
                        while ( $query->have_posts() ) : $query->the_post(); 
                        
                        $get_featured_img_id = get_post_thumbnail_id( get_the_ID() );
                            
                            // attachment img id push in array
                            array_push($dynamic_post_feature_img_array, $get_featured_img_id );
                            
                        endwhile;    

                        wp_reset_postdata(); 

                        // dynamic gs_gallery_image
                        $gspwp_gallery_image = implode(',', $dynamic_post_feature_img_array);
                        
                    }

                    if ($gspwp_layout == 'slider_layout') {
                        if ($display_arrow != 1) {
                            $gspwp_slider_class .= ' gspwp-arrow-' . $gspwp_arrow_style;
                        }
                        if ($display_navigation != 1) {
                            $gspwp_slider_class .= ' gspwp-navigation-' . $gspwp_navigation_style;
                        }
                    }                    
                    
                    $gspwp_class = 'layout-cover ' . $layout . ' ' . $gspwp_layout . $gspwp_slider_class . ' ' . $post_layout_id;
                    // front end show HTMl - Filter
                    if( isset($gspwp_options['want_category_filter']) && $gspwp_options['want_category_filter'] == 0 &&  isset($gspwp_options['dynamic_post_content'])  && $gspwp_options['dynamic_post_content'] == 0  ){ //category filter false

                        // category filter true
                        if ($gspwp_gallery_image) {

                            $flow_root_grid = ($gspwp_layout == 'grid_layout') ? 'display: flow-root;' : '';
                            
                            echo '<div class="gspwp-contents-cover '.esc_attr($atts['layout']).'" id='.esc_attr($atts['layout']).' style="'. esc_attr($flow_root_grid) .'">';
                                gspwp_add_dynamic_style($gspwp_options, $layout);
                                $gallery_images = explode(',', $gspwp_gallery_image);

                                // filter post details
                                $custom_post_type = $gspwp_options['custom_post_type'];
                                $filter_category_name = $gspwp_options['custom_post_type_taxonomies'];

                                ?>
                                <nav class="filter-nav">
                                    <a href="javascript:void(0)" class="nav-item active" data-filter="all" data-shortcode="<?php echo esc_attr( $atts['layout'] ); ?>" data-layout-type="<?php echo esc_attr( $gspwp_layout ); ?>" ><?php echo esc_html_e('All', 'gallery-showcase-pro' );  ?></a>
                                    <?php
                                        $filter_listing = get_terms( array(
                                            'taxonomy' => $filter_category_name,
                                            'hide_empty' => true,
                                        ) );
                                    
                                        foreach ($filter_listing as $filter_options) {
                                            echo '<a href="javascript:void(0)" class="nav-item" data-filter="' . esc_attr( $filter_options->slug ) . '" data-shortcode="' . esc_attr( $atts['layout'] ) . '" data-layout-type="' . esc_attr( $gspwp_layout ) . '" >' . esc_html( $filter_options->name) . '</a>';
                                        }
                                    ?>
                                </nav> 

                                <div class="<?php echo esc_attr( $gspwp_class) ; ?>" data-post_slider_id="<?php echo esc_attr( $post_layout_id ); ?>" data-clm_desktop="<?php echo esc_attr( $gspwp_clm_desktop ); ?>" data-clm_small_desktop="<?php echo esc_attr( $gspwp_clm_small_desktop ); ?>" data-clm_tablet="<?php echo esc_attr( $gspwp_clm_tablet ); ?>" data-clm_mobile="<?php echo esc_attr( $gspwp_clm_mobile ); ?>">
                                        
                                    <?php
                                    $cat_args = array(
                                        'taxonomy' => $filter_category_name,
                                        'orderby' => 'name',
                                        'order' => 'ASC',
                                        'hide_empty' => false
                                    );

                                    $taxonomy_array = get_terms($cat_args);
                                    $post_cat_master_array = array();
                                    if(!empty($taxonomy_array)) {
                                        foreach ($taxonomy_array as $taxonomy) {
                                            $term_id = $taxonomy->term_id;
                                            $cat_name = $taxonomy->name;
                                            $tax_post_args = array(
                                                'post_type' => $custom_post_type,
                                                'post_status' => 'publish',
                                                'posts_per_page' => $gspwp_options['set_post_limit'],
                                                'order' => 'DESC',
                                                'tax_query' => array(
                                                    array(
                                                        'taxonomy' => $filter_category_name,
                                                        'field' => 'term_id',
                                                        'terms' => $term_id
                                                    )
                                                )
                                            );
                                            
                                            $category_latest_post = new WP_Query($tax_post_args);

                                            while ( $category_latest_post->have_posts() ) : $category_latest_post->the_post(); 
                                            
                                                // set featured img id                         
                                                $value = get_post_thumbnail_id( get_the_ID() );
                                            
                                                $post_data = get_post($value);
                                                if ($post_thumbnail == 'custom') {
                                                    $width = isset($gspwp_options['image_width']) ? $gspwp_options['image_width'] : 1200;
                                                    $height = isset($gspwp_options['image_height']) ? $gspwp_options['image_height'] : 800;
                                                    $crop = isset($gspwp_options['image-hard-crop']) && $gspwp_options['image-hard-crop'] == 1 ? true : false;
                                                    $url = wp_get_attachment_url($value);
                                                    $resizedImage = gspwp_image_resize($url, $width, $height, $crop);
                                                    $url = $resizedImage["url"];
                                                } else {
                                                    $url = wp_get_attachment_image_src( $value, $post_thumbnail, '' );
                                                }
                
                                                $title = isset($gspwp_gallery_details[$value]['title']) ? $gspwp_gallery_details[$value]['title'] : $post_data->post_title;
                                                $desc = isset($gspwp_gallery_details[$value]['desc']) ? $gspwp_gallery_details[$value]['desc'] : $post_data->post_content;
                
                                                $display_title = isset($gspwp_options['display_title']) ? $gspwp_options['display_title'] : 0;
                                                $display_content = isset($gspwp_options['display_content']) ? $gspwp_options['display_content'] : 0;
                
                                                // post category
                                                $post_category_array = get_the_terms( get_the_ID(), $filter_category_name );   

                                                $post_category_string = array();
                                                foreach ($post_category_array as $assign_category) {
                                                        
                                                    array_push($post_category_string , $assign_category->slug );
                                                    
                                                }
                                                $post_category_string = implode(" ",$post_category_string);                                               
                                                $post_category = ( !empty($post_category_array)) ? $post_category_string : "";                                                
                                                
                                                if(!in_array(get_the_ID() , $post_cat_master_array)) {                                               
                                                    ?>
                                                    <div class="gspwp-content <?php echo esc_attr( $layout_effect_group ) . ' ' . esc_attr( $layout_effect ) . ' ' . esc_attr( $gspwp_clm ) . ' ' . esc_attr( $gspwp_animate ) . ' ' . esc_attr( $post_category ) ; ?>" data-effect="<?php echo esc_attr( $layout_animation ); ?>" data-id="<?php echo esc_attr( $post->ID ); ?>" data-layout="<?php echo esc_attr( $layout ); ?>" height = '<?php if(isset($resizedImage["height"])){ echo  esc_attr( $resizedImage["height"] ); } ?>' width = '<?php if(isset($resizedImage["width"])){ echo  esc_attr( $resizedImage["width"] ); } ?>'  >
                                                        <?php 
                                                            $img_size_height = "";
                                                            if(isset($resizedImage["height"]) && $post_thumbnail == "custom" ){ 
                                                                $img_size_height = esc_html__( 'height :', 'gallery-showcase-pro' ) . esc_html( $resizedImage["height"]) . esc_html__( 'px' , 'gallery-showcase-pro' );
                                                            }
                                                        ?>
                                                        <figure  style="<?php echo esc_attr($img_size_height); ?>">
                                                            <?php
                                                                if ($post_thumbnail == 'custom') {
                                                                    ?>
                                                                        <a href="<?php echo esc_url( $url ); ?>" data-fancybox="group" data-type="image" rel="example_group" data-caption="<?php echo esc_attr( $title ); ?>">
                                                                    <?php
                                                                    echo '<img src="' . esc_url( $resizedImage["url"] )  . '" height="' . esc_attr( $resizedImage["height"] ) . '" title="' . esc_attr( $title ) . '" data-url="' . esc_url( $url ) . '"/>';
                                                                } else {
                                                                    ?>
                                                                        <a href="<?php echo esc_url( $url[0] ); ?>" data-fancybox="group" data-type="image" rel="example_group" data-caption="<?php echo esc_attr( $title ); ?>">
                                                                    <?php
                                                                    echo '<img src="' . esc_url( $url[0] ) . '" title="' . esc_attr( $title ) . '" data-url="' . esc_url( $url[0] ) . '"/>';
                                                                }
                                                                ?>
                                                                <figcaption>
                                                                    <div>
                                                                        <?php
                                                                        if ($display_title != 1 && $title != '') {
                                                                            echo '<h2>' . esc_html($title)  . '</h2>';
                                                                        }
                    
                                                                        if ($display_content != 1 && $desc != '') {
                                                                            echo '<p>' .  wp_kses_post($desc) . '</p>';
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                </figcaption>
                                                            </a>
                                                        </figure>
                                                    </div>
                                                    <?php
                                                        // get used array
                                                        array_push($post_cat_master_array , get_the_ID() );
                                                }
                                            endwhile;
                                            wp_reset_postdata( );
                                        }
                                    }else{
                                        echo  '<p class="notice-danger">' . esc_html__('Please assign post to this category.', 'gallery-showcase-pro') . '</p>';
                                        ?> <style> <?php echo '#'.esc_attr($atts['layout'])." .filter-nav" ?> { display :none; } </style> <?php
                                    }
                                    ?> 
                                </div> 
                                    <!--element to hold filtered out items-->
                                    <div class="hide hold-filter-item" style="display:none;"></div> 
                                
                                <?php
                            echo '</div>';

                        } else {
                            echo  '<p class="notice-danger">' . esc_html__('No images found.', 'gallery-showcase-pro') . '</p>';
            
                            ?>
                                <style> <?php echo '#'.esc_attr($atts['layout'])." .filter-nav" ?> { display :none; } </style>
                            <?php
                        }
                        
                    }else{                      

                        if ($gspwp_gallery_image) {
                            $flow_root_grid = ($gspwp_layout == 'grid_layout') ? 'display: flow-root;' : '';
                        
                            echo '<div class="gspwp-contents-cover" style="'.esc_attr($flow_root_grid).'">';
                            gspwp_add_dynamic_style($gspwp_options, $layout);
                            $gallery_images = explode(',', $gspwp_gallery_image);
                            
                            ?>
                            <div class="<?php echo esc_attr( $gspwp_class ); ?>" data-post_slider_id="<?php echo esc_attr( $post_layout_id ); ?>" data-clm_desktop="<?php echo esc_attr( $gspwp_clm_desktop ); ?>" data-clm_small_desktop="<?php echo esc_attr( $gspwp_clm_small_desktop ); ?>" data-clm_tablet="<?php echo esc_attr( $gspwp_clm_tablet ); ?>" data-clm_mobile="<?php echo esc_attr( $gspwp_clm_mobile ); ?>">
                                <?php
                                foreach ($gallery_images as $value) {
                                    $post_data = get_post($value);
                                    if ($post_thumbnail == 'custom') {
                                        $width = isset($gspwp_options['image_width']) ? $gspwp_options['image_width'] : 1200;
                                        $height = isset($gspwp_options['image_height']) ? $gspwp_options['image_height'] : 800;
                                        $crop = isset($gspwp_options['image-hard-crop']) && $gspwp_options['image-hard-crop'] == 1 ? true : false;
                                        $url = wp_get_attachment_url($value);
                                        $resizedImage = gspwp_image_resize($url, $width, $height, $crop);
                                        $url = $resizedImage["url"];
                                    } else {
                                        $url = wp_get_attachment_image_src( $value, $post_thumbnail, '' );
                                    }

                                    $title = isset($gspwp_gallery_details[$value]['title']) ? $gspwp_gallery_details[$value]['title'] : $post_data->post_title;
                                    $desc = isset($gspwp_gallery_details[$value]['desc']) ? $gspwp_gallery_details[$value]['desc'] : $post_data->post_content;

                                    $display_title = isset($gspwp_options['display_title']) ? $gspwp_options['display_title'] : 0;
                                    $display_content = isset($gspwp_options['display_content']) ? $gspwp_options['display_content'] : 0;
                                    ?>
                                    <div class="gspwp-content <?php echo esc_attr( $layout_effect_group ) . ' ' . esc_attr( $layout_effect ) . ' ' . esc_attr( $gspwp_clm ) . ' ' . esc_attr( $gspwp_animate ); ?>" data-effect="<?php echo esc_attr( $layout_animation ); ?>" data-id="<?php echo esc_attr( $post->ID ); ?>" data-layout="<?php echo esc_attr( $layout ); ?>" height = '<?php if(isset($resizedImage["height"])){ echo  esc_attr( $resizedImage["height"] ); } ?>' width = '<?php if(isset($resizedImage["width"])){ echo  esc_attr( $resizedImage["width"] ); } ?>'  >
                                        <figure>
                                            <?php
                                                if ($post_thumbnail == 'custom') {
                                                    ?>
                                                        <a href="<?php echo esc_url( $url ); ?>" data-fancybox="group" data-type="image" rel="example_group" data-caption="<?php echo esc_attr( $title ); ?>">
                                                    <?php
                                                    echo '<img src="' . esc_url( $resizedImage["url"] )  . '" height="' . esc_attr( $resizedImage["height"] ) . '" title="' . esc_attr( $title ) . '" data-url="' . esc_url( $url ) . '"/>';
                                                } else {
                                                    ?>
                                                        <a href="<?php echo esc_url( $url[0] ); ?>" data-fancybox="group" data-type="image" rel="example_group" data-caption="<?php echo esc_attr( $title ); ?>">
                                                    <?php
                                                    echo '<img src="' . esc_url( $url[0] ) . '" title="' . esc_attr( $title ) . '" data-url="' . esc_url( $url[0] ) . '"/>';
                                                }
                                                ?>
                                                <figcaption>
                                                    <div>
                                                        <?php
                                                        if ($display_title != 1 && $title != '') {
                                                            echo '<h2>' .  esc_html($title) . '</h2>';
                                                        }

                                                        if ($display_content != 1 && $desc != '') {
                                                            echo '<p>' .  wp_kses_post($desc) . '</p>';
                                                        }
                                                        ?>
                                                    </div>
                                                </figcaption>
                                            </a>
                                        </figure>
                                    </div>
                                    <?php
                                }
                                ?> </div> <?php
                            echo '</div>';
                        }else{
                            echo  '<p class="notice-danger">' . esc_html__('No images found.', 'gallery-showcase-pro') . '</p>';
                        }
                    }

                    wp_reset_query();
                } else {
                    echo  '<p class="notice-info">' . esc_html__('You have to insert a valid Layout name in the shortcode', 'gallery-showcase-pro') . '</p>';
                }
            } else {
                echo  '<p class="notice-info">' . esc_html__('You have to insert a valid Layout name in the shortcode', 'gallery-showcase-pro') . '</p>';
            }
        }

        $data = ob_get_clean();
        return $data;
    }

}