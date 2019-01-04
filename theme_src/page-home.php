<?php

/*

Template Name: Home Page

 */

// Advanced Custom Fields
$slider_photo           = get_field('slider_photo');
$tile_calendar          = get_field('tile_calendar');
$showcase               = get_field('showcase');



//LibCal API
date_default_timezone_set('EST');
$creds_url = 'https://api2.libcal.com/1.1/oauth/token';
$creds_args = array(
        	'body' => array( 'client_id' => '196',
                           'client_secret' => '4b619f6823c68f8541c9591a79a64543',
                           'grant_type' => 'client_credentials'),
        );
$creds_response = json_decode(wp_remote_retrieve_body(wp_remote_post( $creds_url, $creds_args)), true);
if ( is_wp_error( $creds_response ) ) {
   $error_message = $creds_response->get_error_message();
   echo "Something went wrong: $error_message";
} else {
   // echo 'Response:<pre>';
   // print_r( $creds_response['access_token']);
   // echo '</pre>';
}

$events_url = 'https://api2.libcal.com/1.1/events?cal_id=7469&days=30&limit=5';
$events_args = array(
              'headers' => array('Authorization' => 'Bearer ' . $creds_response['access_token']),
          );
$events_response = json_decode(wp_remote_retrieve_body(wp_remote_get( $events_url, $events_args)), true);
if ( is_wp_error( $events_response ) ) {
   $error_message = $events_response->get_error_message();
   echo "Something went wrong: $error_message";
} else {
  $events_array = $events_response['events'];
   // echo 'Response:<pre>';
   // print_r( $events_response['events'][0]);
   // echo '</pre>';
}



get_header(); ?>

<section id="content_area">

<!-- PAGE/BODY AREA
    ================================================== -->

    <div class="container" id="body_area">
        <div class="row">
            <?php get_sidebar('home');   ?>

            <div class="col-sm-9 col-sm-pull-3" id="page">
                <!-- SLIDER -->
                <div id ="slider_container">
                    <div class="swiper-slide owl-carousel">

                        <?php
                        wp_reset_postdata();
                         $i = 0; ?>
                        <?php if ($showcase):?>
                            <?php while( have_rows('showcase') ): the_row();

                                // vars
                                $slider_image = get_sub_field('slider_image');
                                $the_darkness = get_sub_field('apply_the_darkness');
                                $message_type = get_sub_field('message_type');
																$center_link_text = get_sub_field('center_link_text');
																$center_link_url	= get_sub_field('center_link_url');

                                $left_pane_title = get_sub_field('left_pane_title');
                                $left_pane_text = get_sub_field('left_pane_text');
                                $left_pane_url = get_sub_field('left_pane_url');
                                $left_pane_link_text = get_sub_field('left_pane_link_text');

																$right_pane_title = get_sub_field('right_pane_title');
																$right_pane_text = get_sub_field('right_pane_text');
																$right_pane_url = get_sub_field('right_pane_url');
                                $right_pane_link_text = get_sub_field('right_pane_link_text');
                                ?>

															<?php if($message_type == center_link): //add the vars?>
																	<div class="swiper-image" style="background-image: url('<?php echo $slider_image['url'];?>')">
																			<div class="dark_filter" style= <?php if($the_darkness){echo '"background-color: rgba(0, 0, 0, 0.3);"';}?>>

																					<?php if($center_link_url):?>
																						 <a href="<?php echo $center_link_url;?>" target="/blank" class="slider_link link_animate vertical_align table_parent">
																							<div class="table_child">
																									<span class="slider_link_text"><?php echo $center_link_text;?></span>
																							</div>
											                       </a><!--slider link link -->
																					<?php else:?>
																						<div class="slider_link vertical_align table_parent">
																							<div class="table_child">
																									<span class="slider_link_text"><?php echo $center_link_text;?></span>
																							</div>
																						</div><!--slider link div -->
																					<?php endif?>
																			</div><!-- the darkness-->
																	</div><!--swiper-image-->

                                <?php elseif($message_type == left_pane): //add the vars?>
                                    <div class="swiper-image" style="background-image: url('<?php echo $slider_image['url'];?>')">
                                        <div class="dark_filter" style=<?php if($the_darkness){echo '"background-color: rgba(0, 0, 0, 0.3);"';}?>>
                                            <div class="row swiper_info_bucket_wrapper">
                                                    <div class="swiper_info_bucket table_parent">
                                                        <div class="table_child">
                                                          <?php if($left_pane_title):?><h3><?php echo $left_pane_title;?></h3><?php endif?>
                                                          <?php if($left_pane_text):?><p><?php echo $left_pane_text;?></p><?php endif?>
                                                          <?php if($left_pane_url):?>
                                                            <a class="swiper_info_bucket_link" target="/blank" href="<?php echo $left_pane_url; ?>">
                                                                <span><?php if($left_pane_link_text): echo $left_pane_link_text;?><?php else:?>Read More <?php endif?></span>
                                                                &raquo;
                                                            </a><?php endif ?>
                                                        </div>
                                                    </div>
                                            </div>
                                        </div><!-- the darkness-->
                                    </div><!--swiper-image-->

                                  <?php elseif($message_type == right_pane): //add the vars?>
                                      <div class="swiper-image" style="background-image: url('<?php echo $slider_image['url'];?>')">
                                          <div class="dark_filter" style=<?php if($the_darkness){echo '"background-color: rgba(0, 0, 0, 0.3);"';}?>>
                                              <div class="row swiper_info_bucket_wrapper">
                                                      <div class="swiper_info_bucket table_parent pull-right">
                                                          <div class="table_child">
                                                            <?php if($right_pane_title):?><h3><?php echo $right_pane_title;?></h3><?php endif?>
                                                            <?php if($right_pane_text):?><p><?php echo $right_pane_text;?></p><?php endif?>
                                                            <?php if($right_pane_url):?>
                                                              <a class="swiper_info_bucket_link" target="/blank" href="<?php echo $right_pane_url; ?>">
                                                                  <span><?php if($right_pane_link_text): echo $right_pane_link_text;?><?php else:?>Read More <?php endif?></span>
                                                                  &raquo;
                                                              </a><?php endif ?>
                                                          </div>
                                                      </div>
                                              </div>
                                          </div><!-- the darkness-->
                                      </div><!--swiper-image-->

                                  <?php else: ?>
                                    <div class="swiper-image" style="background-image: url('<?php echo $slider_image['url'];?>')">
                                        <div class="dark_filter" style=<?php if($the_darkness){echo '"background-color: rgba(0, 0, 0, 0.3);"';}?>>
                                        </div><!-- the darkness-->
                                    </div><!--swiper-image-->

                                 <?php endif; ?>
                                <?php wp_reset_postdata(); // IMPORTANT - reset the $post object so the rest of the page works correctly ?>
                            <?php  $i++; endwhile; ?>
                        <?php endif; //endif ?>
                    </div><!-- swiper_slide -->
                </div><!-- slider_container -->

                <div class="row divider_row">
                        <div class="col-sm-12">
                                <hr>
                        </div>
                </div><!-- divider_row -->

<!-- POSTS ROW
    ================================================== -->

            <div class="row " id="home_posts_row">
            <?php wp_reset_postdata();
             $news_args = array(
            	'orderby'          => 'date',
            	'order'            => 'DESC',
            	'post_type'        => 'article',
            	'post_status'      => 'publish',
            );
            $newspostslist = get_posts($news_args);
            $post = $newspostslist[0];
            setup_postdata($post); ?>

                <?php if(has_post_thumbnail()):
                    $excerpt_col_width = "partial_width_excerpt";
                ?>

                <?php else:
                    $excerpt_col_width = "full_width_excerpt";
                ?>
                <?php endif?>

                <div class="col-sm-6 home_posts " id="home_news_posts">
                    <div id="home_news_excerpt" class="">

                        <?php if (has_post_thumbnail()):?>
                            <a class="pull-right " id="home_post_thumbnail_link" href="<?php the_permalink(); ?>">
                                <img src="<?php echo get_the_post_thumbnail_url();?>" style="max-width: 100%;">
                            </a>
                        <?php endif?>


                        <p class="pull-left" id="home_excerpt_p">
                            <a href = "<?php echo get_site_url(); ?>/rplnews/"><h2 id="home_post_link">Library News</h2></a>
                            <a href="<?php the_permalink(); ?>"><h4 id="home_post_title"><?php the_title(); ?></h4></a>
                            <i class="fa fa-clock-o" aria-hidden="true"></i><span id="home_post_date"> <?php the_date('F j, Y'); ?></span>
                            <span id="home_post_excerpt"><?php the_excerpt(); ?></span>
                        </p>
                    </div><!-- home_news_excerpt -->
                </div><!-- home_posts -->


            <?php wp_reset_postdata();
              $post_args = array(
               'orderby'          => 'date',
               'order'            => 'DESC',
               'post_type'        => 'post',
               'post_status'      => 'publish',
             );
            $postslist = get_posts($post_args);
            $post = $postslist[0];

            setup_postdata($post); ?>
                    <div class="col-sm-6 home_posts " id="home_blog_posts">
                        <div id="home_news_excerpt" class="">
                            <?php if (has_post_thumbnail()):?>
                                <a class="pull-right " id="home_post_thumbnail_link" href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail();?>
                                </a>
                            <?php endif?>

                            <p class="pull-left"  id="home_excerpt_p">
                                <a href = "<?php echo get_site_url(); ?>/rpl-blog"><h2 id="home_post_link">Shelf Respect</h2></a>
                                <a href="<?php the_permalink(); ?>"><h4 id="home_post_title"><?php the_title(); ?></h4></a>
                                <i class="fa fa-clock-o" aria-hidden="true"></i><span id="home_post_date"> <?php the_date('F j, Y'); ?></span>
                                <span id="home_post_excerpt"><?php the_excerpt(); ?></span>

                            </p>

                        </div><!-- home_news_excerpt -->
                    </div>  <!-- home_blog_posts -->
                </div><!-- home_posts_row -->
            </div><!-- end page col-sm-9-->


    </div> <!-- row -->


<!-- ISSUU ROW

    ================================================== -->

    <div class="row" id="issuu_row">
        <!-- <div class="col-sm-4" id="issuu_col">
            <h4 class=' '>See our latest read: Shel Silverstein</h4>

            <div data-configid="28143600/47001407" style="width:100%; height:254px;" class="issuuembed"></div>
            <script type="text/javascript" src="//e.issuu.com/embed.js" async="true"></script>
        </div> -->
        <div class="col-sm-12">
            <h2 class=' '>Click on a book from our catalog:</h2>
            <div id="book_river">
                <div class="libraryaware_widget_0eb48e8294484bfeae4b737f6be1a9a6"></div>
            </div>
        </div>
    </div>

    <div class="row divider_row">
        <div class="col-sm-12">
            <hr>
        </div>
    </div>


    <!-- EVENTS ROW

        ================================================== -->

        <div class="row" id="home_events_row2018">
          <div class="col-sm-12">
              <h2>Upcoming Events<span style="margin-left: 15px;"><a target = "/" href="http://rvalibrary.libcal.com/" style="font-family: Raleway;">browse by branch</a></span></h2>
          </div>
        </div>

        <div class="row calendar_row2018" style="margin-top: 10px;">

        <?php $dateFormat1 = "D ";
              $dateFormat2 = "d ";
              $dateFormat3 = "M ";
              $dateFormat_time = "g:i a";
              ?>


        <?php for ($i = 0; $i < sizeof($events_array); $i++){ 
	      $event_time_start = strtotime($events_array[$i]['start']  . "+1hours");
	      $event_time_end = strtotime($events_array[$i]['end']  . "+1hours");
		  ?>
          <a href="<?php echo $events_array[$i]['url']['public']?>" id="event_link2018">
            <div class="event2018" <?php if ($events_array[$i]['featured_image']){?>
                                        style="background-image: url('<?php echo 'https:' . $events_array[$i]['featured_image']?> ')">
                                      <?php } else { ?>
                                        style="background-color: #006599">
                                      <?php } ?>
              <div class="dark_filter2018">
                <?php echo date( $dateFormat1, strtotime($events_array[$i]['start']) )?><br>
                <span style="font-size: 30px;"><?php echo date( $dateFormat2, strtotime($events_array[$i]['start']) )?></span><br>
                <?php echo date( $dateFormat3, strtotime($events_array[$i]['start']) )?><br>
                <span style="font-size: 25px;"><?php echo $events_array[$i]['title'] ?></span><br>
                <div style="margin-top: 10px; font-size: 16px;"><i class="fa fa-clock-o"></i> <?php echo date( $dateFormat_time, $event_time_start)?> -
                <?php echo date( $dateFormat_time, $event_time_end)?>
				<br>
                <i class="fa fa-map-marker"></i> Main</div>
              </div>
            </div>
          </a>
        <?php } ?>
      </div>


    <!-- <div class="row " id="home_events_row">
        <div class="col-sm-12">
            <h2>Upcoming Events<span style="margin-left: 15px;"><a target = "/" href="http://rvalibrary.libcal.com/" style="font-family: Raleway;">browse by branch</a></span></h2>

<iframe src="https://api3.libcal.com/embed_calendar.php?iid=4083&cal_id=7752,7340,7753,7750,7405,7469,7402,7751,7403&w=1200&h=300&dv=basicWeek&st=1&we=1&hl=prev%2Cnext%20today&hc=title&hr=basicWeek%20agendaDay%2CagendaWeek%2Cmonth&hd=dddd%2C%20MMM%20D%2C%20YYYY&hw=MMMM%20D&hm=MMMM%20YYYY&cd=dddd%20M%2FD&cw=ddd%20M%2FD&cm=ddd&ta=h(%3Amm)a&td=h(%3Amm)a&tm=h(%3Amm)a&fh=08%3A00%3A00&mi=00%3A00%3A00&ma=24%3A00%3A00&ts=00%3A30%3A00&eo=1&el=0&hf=1&sp=1&c1=%23000000&c2=%23000000&c3=%23F3F3F3&c4=%23D0D0D0&c5=%23000000&cam=&cat=&tar=0&set=0" style="border: 0" width="1200" height="300" frameborder="0" scrolling="auto"></iframe>

    </div>
      </div> -->


</div><!-- container -->
</section><!-- content area -->




<?php get_footer();
