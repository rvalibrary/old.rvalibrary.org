<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
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

                    
                    <?php
                    if ( have_posts() ) :
                        while ( have_posts() ) : the_post();
                                get_template_part( 'template-parts/content', 'page' );

                                // If comments are open or we have at least one comment, load up the comment template.
    //				if ( comments_open() || get_comments_number() ) :
    //					comments_template();
    //				endif;

                        endwhile; // End of the loop.
                    else :
                            echo wpautop( 'Sorry, no posts were found' );
                    endif;
                    ?>          
                    
                   

                </div><!-- end page col-->
                
              
                
          <?php get_sidebar('page');   ?> 
                
                
                
        </div> <!-- row -->     
    </div><!-- container -->
</section><!-- content area -->


<?php
get_footer();












