<?php
/**
 * Template part for displaying page content in page.php.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package RPL
 */
$thumbnail_url      =   wp_get_attachment_url( get_post_thumbnail_id ($post -> ID));

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    

    
	<header class="entry-header">
            
            <div class="row" id="page_header_custom">
                <div class="col-sm-5" id="page_header_title">
                    <?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>
                </div>
                
                <?php if( has_post_thumbnail() ) {?>
                    <div class="col-sm-7" id="page_header_image" style = "background: url('<?php echo $thumbnail_url ?>') no-repeat; background-size: cover;"></div>
                <?php } else { ?>
                    <div class="col-sm-7" id="page_header_image" style = "background: url('http://maxlongton.com/wp-content/themes/rpl/assets/img/cnt_pana_General2.jpg')"></div>
                <?php } ?>

            </div>
	</header><!-- .entry-header -->
        
        
        <?php

            wp_nav_menu ( array(
                'theme_location'    => 'general_info',
                'container'         => 'div',
                'depth'             => 1,
                'menu_class'        => 'general_info_nav sub_nav')
            );
        ?>
        
        
        

	<div class="entry-content">
		<?php
			the_content();

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'rpl' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php
			edit_post_link(
				sprintf(
					/* translators: %s: Name of current post */
					esc_html__( 'Edit %s', 'rpl' ),
					the_title( '<span class="screen-reader-text">"', '"</span>', false )
				),
				'<span class="edit-link">',
				'</span>'
			);
		?>
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->