<?php
/*
Template Name: Children's
 
 */

get_header(); ?>



<section id="content_area">

	
	    <!-- PAGE/BODY AREA
		================================================== -->
	
	
        <div class="container clearfix" id="body_area">
            
            <div class="row clearfix">
			
			
                <div class="col-sm-9 clearfix" id="page">

			<?php
			while ( have_posts() ) : the_post();

				get_template_part( 'template-parts/content', 'childrens_page' );

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile; // End of the loop.
			?>

                </div><!-- end page col-->
                
              
                
          <?php get_sidebar('page');   ?> 
                
                
                
        </div> <!-- row -->     
    </div><!-- container -->
</section><!-- content area -->


<?php
get_footer();