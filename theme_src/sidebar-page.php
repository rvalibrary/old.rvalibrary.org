<!-- SIDEBAR
================================================== -->
<div class="col-sm-3" id="sidebar">
    <div class="containment_box" id="search_containment">
            <div class="banner">
                    <h4 class="vertical_align">Search</h4>
            </div>


            <!--=============SEARCH FORM BOOYAH ==============-->
            <div id="search_form_wrapper">
                <form role = "search" id="searchform" action="http://ibistro.ci.richmond.va.us/uhtbin/cgisirsi/x/0/0/123?" method="get"
                style="padding:0px;margin:0px;z-index:-1;" target="_blank" onsubmit="_gaq.push(['_trackEvent','Catalog','Search',this.href]);">


                <fieldset class="form-group" id="search_box_wrapper">
                        <input id="search_box" type="text" class="form-control" placeholder="Search the Catalog" name="searchdata1">
                </fieldset>
                <fieldset class="form-group align-center center" id="search_radio_button_wrapper">
                    <label class="radio-inline"><input type="radio" name="search_select" id="button_catalog" checked>Catalog</label>
                    <label class="radio-inline"><input type="radio" name="search_select" id="button_site">Site</label>
                    <input type="submit" id="sidebar_searchsubmit" class="btn btn-primary" value="Search" style="margin-left: 10px; font-family: Raleway;">
                </fieldset>
                </form><!-- search form -->

                <!-- advanced search -->
                    <div class="center" id="advanced_wrapper"><a href="http://ibistro.ci.richmond.va.us/uhtbin/cgisirsi/x/0/0/49" target="\blank">Advanced Search</a></div>
            </div>

    </div><!--containment_box -->


    <div class="containment_box" id="library_card_containment">
        <div class="image_container" id="librarycard">
            <a href="<?php echo get_site_url(); ?>/get-card/"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/GetALibraryCard.png" alt=""></a>
        </div>
    </div>


    <div class="containment_box" id="digital_services_containment">
            <div class="banner">
                    <h4 class="vertical_align">Digital Services</h4>
            </div>


            <?php

            wp_reset_postdata();
            $loop = new WP_Query( array (
                                        	'post_type'        => 'sidebar_item',
                                          'orderby'          => 'post_id',
                                        	'order'            => 'ASC',
                                        	'post_status'      => 'publish') ) ?>
            <?php while( $loop -> have_posts() ) : $loop -> the_post() ?>

              <div class= "image_container">
                  <a href="<?php the_field('link_url'); ?>" target="_blank"><img src="<?php the_field('image'); ?>" alt=""></a>
              </div>

            <?php endwhile; ?>


    </div><!-- containment_box -->


    <?php dynamic_sidebar( 'sidebar-page' ); ?>


</div><!-- end sidebar col -->
