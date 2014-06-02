<?php

if ( ! function_exists( 'woo_widget_tabs_popular' ) ) {
    function woo_widget_tabs_popular( $posts = 5, $size = 45, $days = null ) {
        global $post;

        if ( $days ) {
            global $popular_days;
            $popular_days = $days;

            // Register the filtering function
            add_filter( 'posts_where', 'woo_filter_where' );
        }

        $popular = get_posts( array( 'suppress_filters' => false, 'ignore_sticky_posts' => 1, 'orderby' => 'comment_count', 'numberposts' => $posts ) );
        foreach($popular as $post) :
            setup_postdata($post);
            ?>
            <li>
                <?php if ($size <> 0) woo_image( 'height=' . $size . '&width=' . $size . '&class=thumbnail&single=true' ); ?>
                <a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                <span class="meta"><?php the_time( get_option( 'date_format' ) ); ?></span>
                <div class="fix"></div>
            </li>
        <?php endforeach;

        wp_reset_postdata();
    }
}

if ( ! function_exists( 'woo_widget_tabs_latest' ) ) {
    function woo_widget_tabs_latest( $posts = 5, $size = 45 ) {
        global $post;
        $latest = get_posts( array( 'suppress_filters' => false, 'ignore_sticky_posts' => 1, 'orderby' => 'post_date', 'order' => 'desc', 'numberposts' => $posts ) );
        foreach($latest as $post) :
            setup_postdata($post);
            ?>
            <li>
                <?php if ( $size != 0 ) woo_image( 'height=' . $size . '&width=' . $size . '&class=thumbnail&single=true' ); ?>
                <a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                <span class="meta"><?php the_time( get_option( 'date_format' ) ); ?></span>
                <div class="fix"></div>
            </li>
        <?php endforeach;

        wp_reset_postdata();
    }
}

if ( ! function_exists( 'woo_widget_tabs_comments' ) ) {
    function woo_widget_tabs_comments( $posts = 5, $size = 35 ) {
        global $wpdb;

        $comments = get_comments( array( 'number' => $posts, 'status' => 'approve', 'post_status' => 'publish' ) );
        if ( $comments ) {
            foreach ( (array) $comments as $comment) {
                $post = get_post( $comment->comment_post_ID );
                ?>
                <li class="recentcomments">
                    <?php if ( $size > 0 ) echo get_avatar( $comment, $size ); ?>
                    <a href="<?php echo get_comment_link( $comment->comment_ID ); ?>" title="<?php echo wp_filter_nohtml_kses( $comment->comment_author ); ?> <?php echo esc_attr_x( 'on', 'comment topic', 'woothemes' ); ?> <?php echo esc_attr( $post->post_title ); ?>"><?php echo wp_filter_nohtml_kses($comment->comment_author); ?>: <?php echo stripslashes( substr( wp_filter_nohtml_kses( $comment->comment_content ), 0, 50 ) ); ?>...</a>
                    <div class="fix"></div>
                </li>
            <?php
            }
        }


        wp_reset_postdata();
    }
}