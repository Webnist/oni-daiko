<?php
/*
Template Name : Default
Description : 
Author : 
Author URI : 
Version:  1.0
*/
$oni_posts = get_oni_daiko_posts(); ?>
<div class="oni_daiko_post">
    <h2><?php echo oni_daiko_title(); ?><span><a href="<?php feed_url(); ?>"><?php _e('[RSS]','oni_daiko'); ?></a></span></h2>
    <?php foreach ( $oni_posts as $post ) { switch_to_blog( $post->blog_id ); unset( $post->blog_id ); setup_postdata( $post ); ?>
        <div <?php post_class(); ?>>
            <h2 class="entry-title"><a href="<?php echo get_permalink($post->ID); ?>" rel="bookmark"><?php echo get_the_title($post->ID); ?></a></h2>
            <div class="entry-meta">
				<?php the_date(); ?>
            </div><!-- .entry-meta -->
            <div class="entry-summary">
				<?php the_excerpt(); ?>
                <?php if ( has_post_thumbnail() ) { ?>
                <p class="thumb"><a href="<?php echo get_permalink($post->ID); ?>"><?php echo get_the_post_thumbnail( $post->ID, 'thumbnail' ); ?></a></p>
                <?php } ?>
            </div>
            <div class="entry-blog-meta">
                <a href="<?php echo home_url( '/' ); ?>" title="<?php printf( __( 'Permanent Link to %s', 'megumi'), get_option('blogname')); ?>"><?php echo get_option('blogname'); ?></a>
            </div>
        </div><!-- .entry-summary -->
    <?php restore_current_blog(); } ?>
</div>
<hr />
