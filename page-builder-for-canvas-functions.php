<?php

$health = 'ok';

if (!function_exists('check_main_heading')) {
    function check_main_heading() {
        global $health;
        if (!function_exists('woo_options_add') ) {
            function woo_options_add($options) {
                $cx_heading = array( 'name' => __('Canvas Extensions', 'pootlepress-canvas-extensions' ),
                    'icon' => 'favorite', 'type' => 'heading' );
                if (!in_array($cx_heading, $options))
                    $options[] = $cx_heading;
                return $options;
            }
        } else {	// another ( unknown ) child-theme or plugin has defined woo_options_add
            $health = 'ng';
        }
    }
}

add_action( 'admin_init', 'poo_commit_suicide' );

if(!function_exists('poo_commit_suicide')) {
    function poo_commit_suicide() {
        global $health;
        $pluginFile = str_replace('-functions', '', __FILE__);
        $plugin = plugin_basename($pluginFile);
        $plugin_data = get_plugin_data( $pluginFile, false );
        if ( $health == 'ng' && is_plugin_active($plugin) ) {
            deactivate_plugins( $plugin );
            wp_die( "ERROR: <strong>woo_options_add</strong> function already defined by another plugin. " .
                $plugin_data['Name']. " is unable to continue and has been deactivated. " .
                "<br /><br />Please contact PootlePress at <a href=\"mailto:support@pootlepress.com?subject=Woo_Options_Add Conflict\"> support@pootlepress.com</a> for additional information / assistance." .
                "<br /><br />Back to the WordPress <a href='".get_admin_url(null, 'plugins.php')."'>Plugins page</a>." );
        }
    }
}



//
// Override Canvas woo_image, to add "enable" checking for Pootle Post Loop Widget
//
if ( !function_exists('woo_image') ) {
    function woo_image($args) {

        /* ------------------------------------------------------------------------- */
        /* SET VARIABLES */
        /* ------------------------------------------------------------------------- */

        global $post;
        global $woo_options;

        //Defaults
        $key = 'image';
        $width = null;
        $height = null;
        $class = '';
        $quality = 90;
        $id = null;
        $link = 'src';
        $repeat = 1;
        $offset = 0;
        $before = '';
        $after = '';
        $single = false;
        $force = false;
        $return = false;
        $is_auto_image = false;
        $src = '';
        $meta = '';
        $alignment = '';
        $size = '';
        $noheight = '';

        $alt = '';
        $img_link = '';

        $attachment_id = array();
        $attachment_src = array();

        if ( ! is_array( $args ) )
            parse_str( $args, $args );

        extract( $args );

        $enable = get_option('pp_pb_post_loop_thumbnail_enable', '1') == true;
        if (!$enable) {
            if ($return) {
                return '';
            } else {
                return;
            }
        }

        // Set post ID
        if ( empty( $id ) ) {
            $id = $post->ID;
        }

        $thumb_id = esc_html( get_post_meta( $id, '_thumbnail_id', true ) );

        // Set alignment
        if ( $alignment == '' )
            $alignment = esc_html( get_post_meta( $id, '_image_alignment', true ) );

        // Get standard sizes
        if ( ! $width && ! $height ) {
            $width = '100';
            $height = '100';
        }

        // Cast $width and $height to integer
        $width = intval( $width );
        $height = intval( $height );

        /* ------------------------------------------------------------------------- */
        /* FIND IMAGE TO USE */
        /* ------------------------------------------------------------------------- */

        // When a custom image is sent through
        if ( $src != '' ) {
            $custom_field = esc_url( $src );
            $link = 'img';

            // WP 2.9 Post Thumbnail support
        } elseif ( get_option( 'woo_post_image_support' ) == 'true' && ! empty( $thumb_id ) ) {

            if ( get_option( 'woo_pis_resize' ) == 'true' ) {

                if ( 0 == $height ) {
                    $img_data = wp_get_attachment_image_src( $thumb_id, array( intval( $width ), 9999 ) );
                    $height = $img_data[2];
                }

                // Dynamically resize the post thumbnail
                $vt_crop = get_option( 'woo_pis_hard_crop' );
                if ($vt_crop == 'true' ) $vt_crop = true; else $vt_crop = false;
                $vt_image = vt_resize( $thumb_id, '', $width, $height, $vt_crop );

                // Set fields for output
                $custom_field = esc_url( $vt_image['url'] );
                $width = $vt_image['width'];
                $height = $vt_image['height'];

            } else {
                // Use predefined size string
                if ( $size )
                    $thumb_size = $size;
                else
                    $thumb_size = array( $width, $height );

                $img_link = get_the_post_thumbnail( $id, $thumb_size, array( 'class' => 'woo-image ' . esc_attr( $class ) ) );
            }

            // Grab the image from custom field
        } else {
            $custom_field = esc_url( get_post_meta( $id, $key, true ) );
        }

        // Automatic Image Thumbs - get first image from post attachment
        if ( empty( $custom_field ) && get_option( 'woo_auto_img' ) == 'true' && empty( $img_link ) && ! ( is_singular() && in_the_loop() && $link == 'src' ) ) {

            if( $offset >= 1 )
                $repeat = $repeat + $offset;

            $attachments = get_children( array(	'post_parent' => $id,
                    'numberposts' => $repeat,
                    'post_type' => 'attachment',
                    'post_mime_type' => 'image',
                    'order' => 'DESC',
                    'orderby' => 'menu_order date')
            );

            // Search for and get the post attachment
            if ( ! empty( $attachments ) ) {
                $counter = -1;
                foreach ( $attachments as $att_id => $attachment ) {
                    $counter++;
                    if ( $counter < $offset )
                        continue;

                    if ( get_option( 'woo_post_image_support' ) == 'true' && get_option( 'woo_pis_resize' ) == 'true' ) {
                        // Dynamically resize the post thumbnail
                        $vt_crop = get_option( 'woo_pis_hard_crop' );
                        if ( $vt_crop == 'true' ) $vt_crop = true; else $vt_crop = false;
                        $vt_image = vt_resize( $att_id, '', $width, $height, $vt_crop );

                        // Set fields for output
                        $custom_field = esc_url( $vt_image['url'] );
                        $width = $vt_image['width'];
                        $height = $vt_image['height'];
                    } else {
                        $src = wp_get_attachment_image_src( $att_id, 'large', true );
                        $custom_field = esc_url( $src[0] );
                        $attachment_id[] = $att_id;
                        $src_arr[] = $custom_field;
                    }
                    $thumb_id = $att_id;
                    $is_auto_image = true;
                }

                // Get the first img tag from content
            } else {

                $first_img = '';
                $post = get_post( $id );
                ob_start();
                ob_end_clean();
                $output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches );
                if ( !empty($matches[1][0]) ) {

                    // Save Image URL
                    $custom_field = esc_url( $matches[1][0] );

                    // Search for ALT tag
                    $output = preg_match_all( '/<img.+alt=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches );
                    if ( !empty($matches[1][0]) ) {
                        $alt = esc_attr( $matches[1][0] );
                    }
                }

            }

        }

        // Check if there is YouTube embed
        if ( empty( $custom_field ) && empty( $img_link ) ) {
            $embed = esc_html( get_post_meta( $id, 'embed', true ) );
            if ( $embed )
                $custom_field = esc_url( woo_get_video_image( $embed ) );
        }

        // Return if there is no attachment or custom field set
        if ( empty( $custom_field ) && empty( $img_link ) ) {

            // Check if default placeholder image is uploaded
            // $placeholder = get_option( 'framework_woo_default_image' );
            $placeholder = WF()->get_placeholder_image_url();
            if ( $placeholder && !(is_singular() && in_the_loop()) ) {
                $custom_field = esc_url( $placeholder );

                // Resize the placeholder if
                if ( get_option( 'woo_post_image_support' ) == 'true' && get_option( 'woo_pis_resize' ) == 'true' ) {
                    // Dynamically resize the post thumbnail
                    $vt_crop = get_option( 'woo_pis_hard_crop' );
                    if ($vt_crop == 'true' ) $vt_crop = true; else $vt_crop = false;
                    $vt_image = vt_resize( '', $placeholder, $width, $height, $vt_crop );

                    // Set fields for output
                    $custom_field = esc_url( $vt_image['url'] );
                    $width = $vt_image['width'];
                    $height = $vt_image['height'];
                }
            } else {
                return;
            }
        }

        if(empty( $src_arr ) && empty( $img_link ) ) { $src_arr[] = $custom_field; }

        /* ------------------------------------------------------------------------- */
        /* BEGIN OUTPUT */
        /* ------------------------------------------------------------------------- */

        $output = '';

        // Set output height and width
        $set_width = ' width="' . esc_attr( $width ) . '" ';
        $set_height = '';

        if ( ! $noheight && 0 < $height )
            $set_height = ' height="' . esc_attr( $height ) . '" ';

        // Set standard class
        if ( $class ) $class = 'woo-image ' . esc_attr( $class ); else $class = 'woo-image';

        // Do check to verify if images are smaller then specified.
        if($force == true){ $set_width = ''; $set_height = ''; }

        // WP Post Thumbnail
        if( ! empty( $img_link ) ) {

            if( $link == 'img' ) {  // Output the image without anchors
                $output .= wp_kses_post( $before );
                $output .= $img_link;
                $output .= wp_kses_post( $after );
            } elseif( $link == 'url' ) {  // Output the large image
                $src = wp_get_attachment_image_src( $thumb_id, 'large', true );
                $custom_field = esc_url( $src[0] );
                $output .= $custom_field;
            } else {  // Default - output with link
                if ( ( is_single() || is_page() ) && $single == false ) {
                    $rel = 'rel="lightbox"';
                    $href = false;
                } else {
                    $href = get_permalink( $id );
                    $rel = '';
                }

                $title = 'title="' . esc_attr( get_the_title( $id ) ) .'"';

                $output .= wp_kses_post( $before );
                if($href == false){
                    $output .= $img_link;
                } else {
                    $output .= '<a ' . $title . ' href="' . esc_url( $href ) . '" '. $rel .'>' . $img_link . '</a>';
                }

                $output .= wp_kses_post( $after );
            }
        }

        // Use thumb.php to resize. Skip if image has been natively resized with vt_resize. Make sure thumb.php exists on purpose in a child theme.
        elseif ( get_option( 'woo_resize') == 'true' && empty( $vt_image['url'] )/* && file_exists( get_stylesheet_directory_uri() . '/thumb.php' )*/ ) {

            foreach( $src_arr as $key => $custom_field ) {

                // Clean the image URL
                $href = esc_url( $custom_field );
                $custom_field = cleanSource( $custom_field );

                // Check if WPMU and set correct path AND that image isn't external
                if ( function_exists( 'get_current_site') ) {
                    get_current_site();
                    //global $blog_id; Breaks with WP3 MS
                    if ( !$blog_id ) {
                        global $current_blog;
                        $blog_id = $current_blog->blog_id;
                    }
                    if ( isset($blog_id) && $blog_id > 0 ) {
                        $imageParts = explode( 'files/', $custom_field );
                        if ( isset( $imageParts[1] ) )
                            $custom_field = '/blogs.dir/' . $blog_id . '/files/' . $imageParts[1];
                    }
                }

                //Set the ID to the Attachment's ID if it is an attachment
                if($is_auto_image == true){
                    $quick_id = $attachment_id[$key];
                } else {
                    $quick_id = $id;
                }

                //Set custom meta
                if ($meta) {
                    $alt = $meta;
                    $title = 'title="' . esc_attr( $meta ) . '"';
                } else {
                    if ( ( $alt != '' ) || ! ( $alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) ) ) {
                        $alt = esc_attr( get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
                    } else {
                        $alt = esc_attr( get_the_title( $quick_id ) );
                    }
                    $title = 'title="'. esc_attr( get_the_title( $quick_id ) ) .'"';
                }

                // Set alignment parameter
                if ( $alignment != '' )
                    $alignment = '&amp;a=' . urlencode( $alignment );

                $img_url = esc_url( get_template_directory_uri() . '/functions/thumb.php?src=' . $custom_field . '&amp;w=' . $width . '&amp;h=' . $height . '&amp;zc=1&amp;q=' . $quality . $alignment );
                $img_link = '<img src="' . $img_url . '" alt="' . esc_attr( $alt ) . '" class="' . esc_attr( stripslashes( $class ) ) . '" ' . $set_width . $set_height . ' />';

                if( $link == 'img' ) {  // Just output the image
                    $output .= wp_kses_post( $before );
                    $output .= $img_link;
                    $output .= wp_kses_post( $after );

                } elseif( $link == 'url' ) {  // Output the image without anchors

                    if($is_auto_image == true){
                        $src = wp_get_attachment_image_src($thumb_id, 'large', true);
                        $custom_field = esc_url( $src[0] );
                    }
                    $output .= $href;

                } else {  // Default - output with link

                    if ( ( is_single() || is_page() ) && $single == false ) {
                        $rel = 'rel="lightbox"';
                    } else {
                        $href = get_permalink( $id );
                        $rel = '';
                    }

                    $output .= wp_kses_post( $before );
                    $output .= '<a ' . $title . ' href="' . esc_url( $href ) . '" ' . $rel . '>' . $img_link . '</a>';
                    $output .= wp_kses_post( $after );
                }
            }

            // No dynamic resizing
        } else {
            foreach( $src_arr as $key => $custom_field ) {

                //Set the ID to the Attachment's ID if it is an attachment
                if( $is_auto_image == true && isset( $attachment_id[$key] ) ){
                    $quick_id = $attachment_id[$key];
                } else {
                    $quick_id = $id;
                }

                //Set custom meta
                if ($meta) {
                    $alt = esc_attr( $meta );
                    $title = 'title="'. esc_attr( $meta ) .'"';
                } else {
                    if ( empty( $alt ) ) $alt = esc_attr( get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
                    $title = 'title="'. esc_attr( get_the_title( $quick_id ) ) .'"';
                }

                if ( empty( $alt ) ) {
                    $alt = esc_attr( get_post( $thumb_id )->post_excerpt ); // If not, Use the Caption
                }

                if ( empty( $alt ) ) {
                    $alt = esc_attr( get_post( $thumb_id )->post_title ); // Finally, use the title
                }

                $img_link =  '<img src="'. esc_url( $custom_field ) . '" alt="' . esc_attr( $alt ) . '" ' . $set_width . $set_height . $title . ' class="' . esc_attr( stripslashes( $class ) ) . '" />';

                if ( $link == 'img' ) {  // Just output the image
                    $output .= wp_kses_post( $before );
                    $output .= $img_link;
                    $output .= wp_kses_post( $after );

                } elseif( $link == 'url' ) {  // Output the URL to original image
                    if ( $vt_image['url'] || $is_auto_image ) {
                        $src = wp_get_attachment_image_src( $thumb_id, 'full', true );
                        $custom_field = esc_url( $src[0] );
                    }
                    $output .= $custom_field;

                } else {  // Default - output with link

                    if ( ( is_single() || is_page() ) && $single == false ) {

                        // Link to the large image if single post
                        if ( $vt_image['url'] || $is_auto_image ) {
                            $src = wp_get_attachment_image_src( $thumb_id, 'full', true );
                            $custom_field = esc_url( $src[0] );
                        }

                        $href = $custom_field;
                        $rel = 'rel="lightbox"';
                    } else {
                        $href = get_permalink( $id );
                        $rel = '';
                    }

                    $output .= wp_kses_post( $before );
                    $output .= '<a href="' . esc_url( $href ) . '" ' . $rel . ' ' . $title . '>' . $img_link . '</a>';
                    $output .= wp_kses_post( $after );
                }
            }
        }

        // Remove no height attribute - IE fix when no height is set
        $output = str_replace( 'height=""', '', $output );
        $output = str_replace( 'height="0"', '', $output );

        // Return or echo the output
        if ( $return == TRUE )
            return $output;
        else
            echo $output; // Done

    }
}