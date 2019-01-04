<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package RPL
 */

get_header(); ?>

	<div id="primary" class="content-area container archive_primary">
		<main id="main" class="site-main" role="main">

		<?php
		if ( have_posts() ) : ?>
                        
			<header class="" id="archive_page-header">
				<?php
					the_archive_title( '<h1 class="page-title">', '</h1>' );
					the_archive_description( '<div class="taxonomy-description">', '</div>' );
				?>
                            
                            
                            <hr>
			</header><!-- .page-header -->

			<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post();?>
                        <div class="row" id="archive_loop">
                            <div class="col-sm-12">
                                <a href="<?php echo get_permalink();?>"><h2><?php echo get_the_title();?></h2></a>
                                <div class="entry-meta">
                                    Posted by: <?php the_author_posts_link(); ?> on <?php the_time('m/d/y'); ?>
                                    &nbsp<i class="fa fa-tag" aria-hidden="true"></i>&nbsp<?php the_tags( ' ', ', ', '' ); ?>
                                </div><!-- .entry-meta -->
                            </div>
                        </div>
			<?php endwhile;
			the_posts_navigation();
		else :
			get_template_part( 'template-parts/content', 'none' );
		endif; ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
