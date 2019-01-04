<?php
/**
 * Template part for displaying a message that posts cannot be found.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package RPL
 */

?>

<section class="no-results not-found">
	<header class="page-header" id = "search_page-header">
		<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'rpl' ); ?></h1>
                <hr>
	</header><!-- .page-header -->

	<div class="page-content" id = "none_page-content">
		<?php
		if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

			<p><?php printf( wp_kses( __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'rpl' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( admin_url( 'post-new.php' ) ) ); ?></p>

		<?php elseif ( is_search() ) : ?>

			<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'rpl' ); ?></p>
			
                            
                        <div class="none_search_bar"><?php get_search_form();?></div>

		<?php else : ?>

			<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'rpl' ); ?></p>
			
				<div class="none_search_bar"><?php get_search_form();?></div>

		<?php endif; ?>
	</div><!-- .page-content -->
</section><!-- .no-results -->
