<?php
/**
* Template Name: News Posts
 */

get_header(); ?>

    <div id="primary" class="content-area container index_primary">
        <main id="main" class="site-main" role="main">


            <div class="row" id="index_title_row">
                <div class="col-sm-12" id="index_title">
                    <h1> Latest News </h1>
                    <span>What's happening at RPL?</span>
                </div>
            </div><!-- index_title_row -->

        <?php

        $args = array('post_type'=>'article');
        query_posts($args);

        if ( have_posts() ) :

                $loop_number = 1;
                /* Start the Loop */
                while ( have_posts() ) : the_post();
                    if ($loop_number == 1){?>

                        <?php if(has_post_thumbnail()):?>
                            <div class="first_index row first_index_row">
                                <div class="col-sm-8">
                                    <a href="<?php echo get_permalink();?>"><h2><?php echo get_the_title();?></h2></a>
                                    <div class="entry-meta">
                                        Posted by: <?php the_author_posts_link(); ?> on <?php the_time('m/d/y'); ?>
                                        &nbsp<i class="fa fa-tag" aria-hidden="true"></i>&nbsp<?php the_tags( ' ', ', ', '' ); ?>
                                    </div><!-- .entry-meta -->
                                    <?php the_excerpt(); ?>
                                </div>
                                <div class="col-sm-4" id="first_index_thumbnail">
									<img style="max-width: 300px;" src="<?php echo get_the_post_thumbnail_url(); ?>"/>
                                </div>
                            </div><!-- first_index -->

                        <?php else: ?>
                            <div class="row">
                                <div class="col-sm-12 first_index first_index_row">
                                    <a href="<?php echo get_permalink();?>"><h2><?php echo get_the_title();?></h2></a>
                                    <div class="entry-meta">
                                        Posted by: <?php the_author_posts_link(); ?> on <?php the_time('m/d/y'); ?>
                                        &nbsp<i class="fa fa-tag" aria-hidden="true"></i>&nbsp<?php the_tags( ' ', ', ', '' ); ?>
                                    </div><!-- .entry-meta -->

                                    <?php the_excerpt(); ?>

                                </div><!-- first_index -->
                            </div>
                        <?php endif ?>

                    <?php } else {?>

                        <?php if ($loop_number % 2 == 0){?>
                            <div class="row">
                        <?php }?>
                            <div class="col-sm-6 first_index first_index_row">

                                <?php if(has_post_thumbnail()):?>
                                    <div class="" id="index_small_post_content">
                                        <a href="<?php echo get_permalink();?>"><h2><?php echo get_the_title();?></h2></a>
                                        <div class="entry-meta">
                                            Posted by: <?php the_author_posts_link(); ?> on <?php the_time('m/d/y'); ?>
                                            &nbsp<i class="fa fa-tag" aria-hidden="true"></i>&nbsp<?php the_tags( ' ', ', ', '' ); ?>
                                        </div><!-- .entry-meta -->
                                    </div><!-- index_small_post_content -->
                                    <div class="index_content_div ">
                                        <a href="<?php the_permalink(); ?>"><div class="pull-right" id="index_small_post_image"
                                             style="background-image: url(<?php echo get_the_post_thumbnail_url(); ?>); background-size: cover;
                                             background-repeat: no-repeat; background-position-x: center; background-position-y: center;">
                                        </div></a><!-- index_small_post_image -->
                                        <?php the_excerpt(); ?>

                                    </div>
                                <?php else: ?>
                                    <div>
                                        <a href="<?php echo get_permalink();?>"><h2><?php echo get_the_title();?></h2></a>
                                        <div class="entry-meta">
                                            Posted by: <?php the_author_posts_link(); ?> on <?php the_time('m/d/y'); ?>
                                            &nbsp<i class="fa fa-tag" aria-hidden="true"></i>&nbsp<?php the_tags( ' ', ', ', '' ); ?>
                                        </div><!-- .entry-meta -->
                                        <?php the_excerpt(); ?>
                                    </div>

                                <?php endif?>

                            </div><!-- first_index -->
                        <?php if ($loop_number % 2 != 0){?>
                            </div><!--row -->
                        <?php }?> <!-- if loop_number % 2 != 0 -->
                    <?php } ?> <!-- if $loop_number == 1 -->

                <?php $loop_number++;
                endwhile;?>

            <?php

            if ($loop_number %2 != 0){
                echo "</div>";
            }?>

            <div class="row bottom_nav_row" id="index_nav_row">
                <div class="col-sm-6" id="index_previous">
                    <?php previous_posts_link();?>
                </div>
                <div class="col-sm-6" id="index_next">
                    <?php next_posts_link();?>
                </div>
            </div>


        <?php
        else :
                get_template_part( 'template-parts/content', 'none' );
        endif; ?>

        </main><!-- #main -->
    </div><!-- #primary -->

<?php
get_footer();
