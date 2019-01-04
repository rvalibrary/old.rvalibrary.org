<?php
/**
 * The template for displaying all single posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package RPL
 */

get_header(); ?>


<section id="content_area">


	    <!-- PAGE/BODY AREA
		================================================== -->


        <div class="container clearfix" id="body_area">

            <div class="row clearfix">


                <div class="col-sm-9 clearfix" id="page">
                <?php if ( function_exists('yoast_breadcrumb') ) {yoast_breadcrumb('<p id="post_breadcrumbs">','</p>');} ?>

                    <?php
                    while ( have_posts() ) : the_post();

                            // check if the post has a Post Thumbnail assigned to it.
                            get_template_part( 'template-parts/content', get_post_format() );



                            // If comments are open or we have at least one comment, load up the comment template.
//                            if ( comments_open() || get_comments_number() ) :
//                                    comments_template();
//                            endif;

                    endwhile; // End of the loop.
                    ?>


                    <div class="row" id="post_navigation">
											<?php if (is_singular('article')): ?>

												<div class="col-sm-5" id="left_post_nav">
                            <?php previous_post_link('%link', 'Previous Article'); ?>
                        </div>
                        <div class="col-sm-2 center">
														<a href="<?php echo get_site_url();?><?php echo '/rplnews';?>">News Home</a>
                        </div>
                        <div class="col-sm-5" id="right_post_nav">
                            <?php next_post_link('%link', 'Next Article'); ?>
                        </div>

											<?php else:?>
                        <div class="col-sm-5" id="left_post_nav">
                            <?php previous_post_link('%link', 'Previous Post', TRUE); ?>
                        </div>
                        <div class="col-sm-2 center">
														<a href="<?php echo get_permalink(get_option('page_for_posts'));?>" >Blog Home</a>
                        </div>
                        <div class="col-sm-5" id="right_post_nav">
                            <?php next_post_link('%link', 'Next Post', TRUE); ?>
                        </div>
											<?php endif?>

                    </div>

                </div><!-- end page col-->



          <?php get_sidebar('blog');   ?>

        </div> <!-- row -->
    </div><!-- container -->
</section><!-- content area -->


<?php
get_footer();
