<?php
/**
 * The header for our theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package RPL
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="profile" href="http://gmpg.org/xfn/11">
  <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
  <link rel="icon" type="image/png" href="http://rvalibrary.org/wp-content/themes/rpl/assets/img/rpl_favicon2.png">
<!-- HTML5 shiv and Respond.js IE8 support of HTML5 elements and media queries --><!--[if lt IE 9]><script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script><script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
<?php wp_head(); ?>
</head>


<body <?php body_class(); ?>>
  <?php rpl_body(); ?>
  <header id="top_section">


	  <?php $alert_loop = new WP_Query( array('post_type' => 'alert', 'orderby' => 'post_id', 'order' => 'DSC')); ?>
	  <?php while( $alert_loop ->have_posts()) : $alert_loop->the_post(); ?>


			  <div class="center" style="padding: 5px; background-color: rgb(254, 226, 74);" id="alerts_bar">
				  <span><strong><?php the_field('alert_message'); ?></strong></span>
			  </div><!-- alerts_bar -->




	  <?php endwhile; ?>
	  <?php wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly ?>



    <div class="container" id="header_wrapper">
      <div class="row" id="header_top_row">
        <div class="header_child" id="masthead_holder">
              <a href='/'><div id="masthead_image" class="vertical_align">
                <a href="<?php echo get_site_url(); ?>"><img id="masthead_full" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/header.gif"></a>
                <a href="<?php echo get_site_url(); ?>"><img id="masthead_logo" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/header_logo.png"></a>
                <a href="<?php echo get_site_url(); ?>"><img id="masthead_med" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/header_med.gif"></a>
        </div></a>
      </div><!-- col-sm-6 -->

      <div class="header_child pull-right" id="header_link_holder">
        <div class="row">
          <div class="pull-right top_menu" id="admin_links">
             <i class="fa fa-users"></i>
                <a href="http://ibistro.ci.richmond.va.us/uhtbin/cgisirsi/x/0/0/57/1/1166/X/BLASTOFF?user_id=WEBSERVER">My Account</a> | <a href="<?php echo get_site_url(); ?>/contact-us/">Contact</a>                                </div>
                  <a href="https://secure.qgiv.com/for/rplf" target = "_blank" id="donate_button_link"><button class="pull-right btn btn-success" id="donate_button">
                    <i class="fa fa-heart"></i>Donate</button></a>
                    <div class="pull-right top_menu" id="translate_button">
                      <i class="fa fa-globe"></i><span>Translate</span>
                    </div>
                    <div class="pull-right top_menu" id="google_translate">
                      <div id="google_translate_element"></div><script type="text/javascript">
                          function googleTranslateElementInit() {new google.translate.TranslateElement({pageLanguage: 'en', autoDisplay: false, gaTrack: true, gaId: 'UA-78846324-1'}, 'google_translate_element');}</script>
                          <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
                      </div>
                    </div><!-- row -->
                    <div class="row pull-right" id="social_icons">
                      <a href="https://www.facebook.com/RichmondPublicLibrary" target="_blank"><i class="fa fa-facebook-square"></i></a>
                      <a href="https://twitter.com/RVA_Library" target="_blank"><i class="fa fa-twitter-square"></i></a>
                      <!--                            <a href="https://www.pinterest.com/rplibrary/?d" target="_blank"><i class="fa fa-pinterest-square"></i></a>                            <a href="https://www.flickr.com/photos/75261021@N07/" target="_blank"><i class="fa fa-flickr"></i></a>-->
                      <a href="https://www.instagram.com/rva_library/?hl=en" target="_blank"><i class="fa fa-instagram"></i></a>
                    </div>
                  </div>
                </div><!-- row -->
              </div><!-- container header_wrapper -->


<!--the alert was here -->


            <div class="container" id="navbar_wrapper">
              <div class="navbar" role="navigation" id="navbar">
                <?php wp_nav_menu( array( 'theme_location' => 'primary' ) );?>
              </div><!-- navbar -->
            </div><!-- container -->


      </header>
