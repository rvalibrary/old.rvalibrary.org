    



<!-- PUSHER POSTS DEPRICATED -->

<?php $postslist = get_posts('numberposts=6&order=DESC&orderby=rand&category_name=pusherpost');?>
<?php $staticpostlist = get_posts('numberposts=1&order=DESC&orderby=rand&category_name=staticpusherpost');?>

<div class="row important_articles"> 
</div> <!-- important_articles -->




<div class="col-sm-6 article_col">

        <?php if(empty($staticpostlist)) {
            $post = $postslist[0];
        } else {
            $post = $staticpostlist[0];
        }
        setup_postdata($post); 
        ?>

        <div class="article_text">
            <div id="article1_imagewrapper">
                <a href ="<?php the_permalink(); ?>"><?php if ( has_post_thumbnail() ) {
                     the_post_thumbnail();
                }  ?></a>
            </div><!-- article1_imagewrapper -->
            <a href="<?php the_permalink(); ?>"><h4><?php the_title(); ?></h4></a>
            <?php the_excerpt(); ?>
            <div class="read_more"><a href ="<?php the_permalink(); ?>">read more &raquo;</a></div>

        </div>


    </div><!-- col-sm-6 -->

    <hr width="80%">

    <div class="col-sm-6 article_col">

        <?php $post = $postslist[1];
        setup_postdata($post); ?>

        <div class="article_text">
            <div id="article2_imagewrapper">
                <a href ="<?php the_permalink(); ?>"><?php if ( has_post_thumbnail() ) {
                     the_post_thumbnail();
                }  ?></a>
            </div><!-- article1_imagewrapper -->
            <a href="<?php the_permalink(); ?>"><h4><?php the_title(); ?></h4></a>
            <?php the_excerpt(); ?>

            <div class="read_more"><a href ="<?php the_permalink(); ?>">read more &raquo;</a></div>
        </div>


    </div><!-- col-sm-6 -->
    
    
    
<!-- PUSHER POSTS DEPRICATED -->




        if ( has_post_thumbnail() ) {
                the_post_thumbnail();
        } 
        
        
        
        
        
        
        <!-- index page -->
        
        
        
                        <?php
                        $loop_number = 1;
			/* Start the Loop */
			while ( have_posts() ) : the_post();
                            if ($loop_number == 1){?>

                                <?php if(has_post_thumbnail()):?>
                                    <div class="first_index row first_index_row">
                                        <div class="col-sm-6">
                                            <a href="<?php echo get_permalink();?>"><h2><?php echo get_the_title();?></h2></a>
                                        </div>
                                        <div class="col-sm-6">
                                            <img src="<?php echo get_the_post_thumbnail_url(); ?>" alt ="">
                                        </div>
                                    </div><!-- first_index -->

                                <?php else: ?>
                                    <div class="row">
                                        <div class="col-sm-12 first_index first_index_row">
                                            <a href="<?php echo get_permalink();?>"><h2><?php echo get_the_title();?></h2></a>
                                            <div class="entry-meta">
                                                Posted by: <?php the_author_posts_link(); ?> on <?php the_time('m/d/y'); ?>
                                                &nbsp<i class="fa fa-tag" aria-hidden="true"></i>&nbsp<?php the_tags(); ?>
                                            </div><!-- .entry-meta -->

                                            <?php the_excerpt(); ?>

                                        </div><!-- first_index -->
                                    </div>
                                <?php endif ?>
                                
                            <?php } else {?>
                                <div class="row">
                                    <div class="col-sm-12 first_index first_index_row">
                                        <a href="<?php echo get_permalink();?>"><h2><?php echo get_the_title();?></h2></a>
                                        <div class="entry-meta">
                                            Posted by: <?php the_author_posts_link(); ?> on <?php the_time('m/d/y'); ?>
                                            &nbsp<i class="fa fa-tag" aria-hidden="true"></i>&nbsp<?php the_tags(); ?>
                                        </div><!-- .entry-meta -->

                                        <?php the_excerpt(); ?>

                                    </div><!-- first_index -->
                                </div> 
                            <?php } ?>
                                
                        <?php $loop_number++;
			endwhile; ?>