<?php
/*
Template Name: Locations
 */

get_header();

    //Hours JSON url
    $url                    =   'https://api3.libcal.com/api_hours_grid.php?iid=4083&format=json&weeks=1&systemTime=0';
    $branchname             =   get_field('branch_name');

    $response = wp_remote_get( $url );
    if( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
       echo "Something went wrong: $error_message";
    } else {
      $body_string = json_decode($response['body'], true);
    }

    for($i = 0; $i < count($body_string['locations']); ++$i){
      if ($body_string['locations'][$i]['name'] == $branchname){
        $branch_index = $i;
      }
    }

    // Advanced Custom Fields

    //Branch Photo
    $location_photo         =   get_field('location_photo');

    //Time Table
    // $monday_open            =   get_field('monday_open');
    // $monday_close           =   get_field('monday_close');
    // $closed_monday          =   get_field('closed_monday');
    //
    // $tuesday_open           =   get_field('tuesday_open');
    // $tuesday_close          =   get_field('tuesday_close');
    // $closed_tuesday         =   get_field('closed_tuesday');
    //
    // $wednesday_open         =   get_field('wednesday_open');
    // $wednesday_close        =   get_field('wednesday_close');
    // $closed_wednesday       =   get_field('closed_wednesday');
    //
    // $thursday_open          =   get_field('thursday_open');
    // $thursday_close         =   get_field('thursday_close');
    // $closed_thursday        =   get_field('closed_thursday');
    //
    // $friday_open            =   get_field('friday_open');
    // $friday_close           =   get_field('friday_close');
    // $closed_friday          =   get_field('closed_friday');
    //
    // $saturday_open          =   get_field('saturday_open');
    // $saturday_close         =   get_field('saturday_close');
    // $closed_saturday        =   get_field('closed_saturday');
    //
    // $sunday_open            =   get_field('sunday_open');
    // $sunday_close           =   get_field('sunday_close');
    // $closed_sunday          =   get_field('closed_sunday');



    // function hours($timeinput){
    //     $hours = date('g', strtotime($timeinput));
    //     return $hours;
    // }
    //
    // function militaryhours($timeinput){
    //     $militaryhours = date('H', strtotime($timeinput));
    //     return $militaryhours;
    // }
    //
    // function militarytime($timeinput){
    //     $militarytime = date('H:i', strtotime($timeinput));
    //     return $militarytime;
    // }
    //
    //
    // function minutes($timeinput){
    //     $minutes = date('i', strtotime($timeinput));
    //     return $minutes;
    // }
    //
    // function ampm($timeinput){
    //     $ampm = date('a', strtotime($timeinput));
    //     return $ampm;
    // }

    $days = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
    // $opentimes  = array($sunday_open, $monday_open, $tuesday_open ,$wednesday_open, $thursday_open, $friday_open, $saturday_open);
    // $closetimes = array($sunday_close, $monday_close, $tuesday_close, $wednesday_close, $thursday_close, $friday_close, $saturday_close);
    // $closed = array($closed_sunday, $closed_monday, $closed_tuesday, $closed_wednesday, $closed_thursday, $closed_friday, $closed_saturday);
    //
    $dayofweek = date('w');
    // $timezone = 'America/New_York';
    // $date = new DateTime('now', new DateTimeZone($timezone));
    // $localtime = $date->format('g:i a');
    // $localhours = $date->format('H');
    // $localminutes = $date->format('i');

    //Address and coordinates
    $address_firstline      =   get_field('address_firstline');
    $address_secondline     =   get_field('address_secondline');
    $address_phonenumber    =  get_field('address_phonenumber');

    //manager
    $manager                =   get_field('manager');
	//manager title
	$manager_title			= 	get_field('manager_title');
    //details
    $branch_details         =   get_field('branch_details');
    //gallery
    $library_photos         =   get_field('library_photos');
    //calendar
    $calendar               =   get_field('calendar');

    //google maps link
    $map_link               =   get_field('map_link');
    $google_maps_iframe     =   get_field('google_maps_iframe');


    // function timerows($day, $openI, $closeI, $closedAllDay){
    //     echo "<tr>";
    //     echo "<th>$day</th>";
    //     if ($closedAllDay == false){
    //         echo "<th>";
    //         //open time
    //         echo hours($openI);
    //         if (minutes($openI) != 0 ){
    //             echo ":";
    //             echo minutes($openI);
    //             echo ampm($openI);
    //             echo "-";
    //         } else {
    //             echo ampm($openI);
    //             echo "-";
    //         }
    //         //close time
    //         echo hours($closeI);
    //         if (minutes($closeI) != 0 ){
    //             echo ":";
    //             echo minutes($closeI);
    //             echo ampm($closeI);
    //         } else {
    //             echo ampm($closeI);
    //         }
    //         echo "</th>";
    //
    //     } else {
    //         echo "<th>Closed</th>";
    //     }
    //     echo "</tr>";
    // }

    function timerowsAPI($day, $branch_index, $body_string){
        echo "<tr>";
        echo "<th>$day</th>";
        echo "<th>";
        echo $body_string['locations'][$branch_index]['weeks'][0][$day]['rendered'];
        echo "</th>";
        echo "</tr>";
    }



?>

      <?php
      // echo $body_string['locations'][0]['weeks'][0]['Monday']['rendered'];
      //       print_r(  );
      //       echo '<br>';
      //       if($body_string['locations'][$i]['weeks'][0]['Wednesday']['times']['currently_open']){
      //         echo 'Open';
      //       } else {
      //         echo 'Closed';
      //       }
      //
      //     }
      //   }
      //
      //
      //
      // }

      ?>


<section id="content_area">

    <div class="container">
		<div class="row location_title_row">
			<div class="col-sm-12 center location_title">
				<h1><?php echo get_the_title(); ?></h1>
			</div>
		</div><!-- row -->
    <?php if ( function_exists('yoast_breadcrumb') ) {yoast_breadcrumb('<p id="breadcrumbs">','</p>');} ?>
		<div class="row location_hours_row">
			<div class="col-sm-7 location_img center">
				<img src="<?php echo $location_photo['url']; ?>" alt="<?php echo $location_photo['alt']; ?>">
			</div>
			<div class="col-sm-5 location_hours vertical_align center">
                            <table class="">
                              <?php
                                for ($i = 0; $i < count($days); $i++){
                                  timerowsAPI($days[$i], $branch_index, $body_string);
                                }
                              ?>
                            </table>


                            <div class="center raleway location_status_div">
                                <?php
                                    echo "<span id='location_status1'>Status:</span>";
                                    // if (militarytime($localtime) < militarytime($opentimes[$dayofweek]) || militarytime($localtime) >= militarytime($closetimes[$dayofweek]) || $closed[$dayofweek] == true){ //CLOSED CASES
                                    //     echo "<span id='location_status2'>   CLOSED</span>";
                                    // } else { //OPEN CASES
                                    //     if (militaryhours($closetimes[$dayofweek]) - militaryhours($localtime) <= 1 &&
                                    //         minutes($closetimes[$dayofweek])+60    - minutes($localtime) <= 15         ){
                                    //         echo "<span id='location_status3'>   CLOSING SOON</span>";
                                    //     } else {
                                    //         echo "<span id='location_status2'>   OPEN</span>";
                                    //     }
                                    // }
                                    $is_open = $body_string['locations'][$branch_index]['weeks'][0][$days[$dayofweek]]['times']['currently_open'];

                                    if ($is_open){
                                      echo "<span id='location_status2'>   OPEN</span>";
                                    } else {
                                      echo "<span id='location_status2'>   CLOSED</span>";
                                    }

                                ?>
                            </div>
			</div><!-- table div -->
		</div><!-- location_hours_row -->

                <hr class="location_break_line">

		<div class="row location_address_row">
                    <div class="col-sm-4">
                        <div class="vertical_align raleway" id="location_address">
                            <?php echo $address_firstline; ?>
                            <br>
                            <?php echo $address_secondline; ?>
                            <br>
                            Phone: <?php echo $address_phonenumber; ?>
                            <br>
                            <a href="<?php echo $map_link;?>" target = "/blank">Get Directions</a>
                        </div>

                    </div>
                    <div class="col-sm-8 map_wrapper">




                        <div id="map1"><?php echo $google_maps_iframe?></div>

                    </div><!-- col-sm-8 -->
		</div><!-- location_address_row -->

                <div class="row location_manager_row">

                    <div class="col-sm-12 branch_manager">
                        <h2 id="manager_title"><?php echo $manager_title;?></h2>
                        <span><?php echo $manager; ?></span>

                    </div><!-- col-sm-12 -->
                </div><!-- .location_manager_row -->


                <div class="row location_branchdetails_row">
                    <div class="col-sm-12 branch_details">
                        <h2 id="details_title">Branch Details</h2>
                        <?php echo $branch_details; ?>
                    </div><!-- .branch_details -->
                </div><!-- location_branchdetails_row -->



                <div class="row location_photogallery_row">


                    <div class="col-sm-6 upcoming_events">
                        <!-- <h2 id="details_title">Upcoming Events</h2> -->
                        <?php echo $calendar; ?>
                    </div>


                    <div class="col-sm-6">
                        <?php if( $library_photos ): ?>
                        <h2 id="details_title">Branch Photos</h2>

                        <div id="links">
                                <?php foreach( $library_photos as $image ): ?>
                                        <a href="<?php echo $image['url']; ?>" data-gallery>
                                             <img src="<?php echo $image['sizes']['thumbnail']; ?>" alt="<?php echo $image['alt']; ?>" />
                                        </a>
                                <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    </div><!-- col-sm-12 -->

                </div><!-- location_photogallery_row"-->

	</div><!-- container -->

        <!-- The Bootstrap Image Gallery lightbox, should be a child element of the document body -->
        <div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-use-bootstrap-modal="false">
            <!-- The container for the modal slides -->
            <div class="slides"></div>
            <!-- Controls for the borderless lightbox -->
            <h3 class="title"></h3>
            <a class="prev">‹</a>
            <a class="next">›</a>
            <a class="close">×</a>
            <a class="play-pause"></a>
            <ol class="indicator"></ol>
            <!-- The modal dialog, which will be used to wrap the lightbox content -->
            <div class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" aria-hidden="true">&times;</button>
                            <h4 class="modal-title"></h4>
                        </div>
                        <div class="modal-body next"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left prev">
                                <i class="glyphicon glyphicon-chevron-left"></i>
                                Previous
                            </button>
                            <button type="button" class="btn btn-primary next">
                                Next
                                <i class="glyphicon glyphicon-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- blueimp-gallery -->





</section><!-- content area -->


<?php
get_footer();
