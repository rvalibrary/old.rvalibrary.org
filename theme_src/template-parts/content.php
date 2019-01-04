<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package RPL
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		<?php
			if ( is_single() ) {
				the_title( '<h1 class="entry-title">', '</h1>' );
			} else {
				the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
			}

		if ( 'post' === get_post_type() || 'article' === get_post_type()) : ?>



		<div class="entry-meta">
			Posted by: <?php the_author_posts_link(); ?> on <?php the_time('m/d/y'); ?>
                        &nbsp<i class="fa fa-tag" aria-hidden="true"></i><?php the_tags( ' ', ', ', '' ); ?>


		</div><!-- .entry-meta -->
		<?php
		endif; ?>


            <!-- AddToAny BEGIN -->
                <div class="a2a_kit a2a_kit_size_24 a2a_default_style" id="post_social_buttons">
                    <a class="a2a_button_email"></a>
                    <a class="a2a_button_facebook"></a>
                    <a class="a2a_button_twitter"></a>
                    <a class="a2a_button_pinterest"></a>
                    <a class="a2a_button_tumblr"></a>
                    <a class="a2a_button_google_plus"></a>
                </div>
                <script async src="https://static.addtoany.com/menu/page.js"></script>
            <!-- AddToAny END -->



                <hr>

	</header><!-- .entry-header -->

	<div class="entry-content">

		<?php
			the_content( sprintf(
				/* translators: %s: Name of current post. */
				wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'rpl' ), array( 'span' => array( 'class' => array() ) ) ),
				the_title( '<span class="screen-reader-text">"', '"</span>', false )
			) );

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'rpl' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->


</article><!-- #post-## -->
