<?php
/*
Template Name: Main Calendar
 
 */



$main_calendar         = get_field('main_calendar');
$search_bar         = get_field('search_bar');


get_header(); ?>

<div class="container" id="calendar_crumbs">            
            <?php if ( function_exists('yoast_breadcrumb') ) {yoast_breadcrumb('<p id="breadcrumbs">','</p>');} ?>
</div>

<section id="content_area">
	    <!-- PAGE/BODY AREA
		================================================== -->
	
	
        <div class="container clearfix" id="body_area">            
            <div class="row clearfix">
			
			
                <div class="col-sm-12" id="page">

                    <?php echo $main_calendar;?>

                </div><!-- end page col-->
                
          
                
        </div> <!-- row -->     
    </div><!-- container -->
</section><!-- content area -->


<?php
get_footer();
