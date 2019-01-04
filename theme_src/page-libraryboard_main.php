<?php
/*
Template Name: Library Board Main
 
 */
get_header(); 

$group_photo      =       get_field('group_photo');
$photo_names      =       get_field('photo_names');
$board_member     =       get_field('board_member');
$wysiwyg_editor   =       get_field('board_wysiwyg_editor');

?>



<section id="content_area">

    <div class="container" id="body_area">
            
 
        <div class="row board_title_row">

<!--                <div class="col-sm-12 center">
                        <img src="assets/img/friendly_logo.jpg">
                </div>-->

                <div class="col-sm-12 center board_title">
                    <div class="">
                        <h1>Library Board</h1>
                        <?php
                        wp_nav_menu ( array(
                            'theme_location'    => 'library_board',
                            'container'         => 'span',
                            'depth'             => 1,
                            'menu_class'        => 'library_board_nav sub_nav main_libraryboard_sub_nav')
                        );
                        ?>
                    </div>  
                </div>

        <?php if ( function_exists('yoast_breadcrumb') ) {yoast_breadcrumb('<p id="breadcrumbs">','</p>');} ?>

        </div><!-- row -->

        <div class="row">

                <div class="col-sm-12 " id="board_group_photo">
                        <img src="<?php if ($group_photo) {echo $group_photo['url'];}?>" alt="Photo of Board Members">
                </div>
                <div class="col-sm-12 " id="board_name_list">
                <p><strong>Left to right</strong>: <?php echo $photo_names; ?></p>
                </div>
        </div><!-- row -->


        <div class="row bio_block_row">

            <?php  $i = 0; ?>

            <?php if( have_rows('board_member') ): 
                $quant_rows = count($board_member);
            ?>
                <?php while( have_rows('board_member') ): the_row(); 
                    // vars
                    $name = get_sub_field('name');
                    $title = get_sub_field('title');
                    $bio = get_sub_field('bio');
                    ?>

                <div class="col-sm-8 col-sm-offset-2 bio_block">
                        <h2><?php echo $name; ?></h2>
                        <h3><?php echo $title; ?></h3>

                        
                    <?php if ($i != $quant_rows - 1) { //check if last row ?>
                        <?php if ($bio) {
                            echo "<p>";
                            echo $bio;
                            echo "</p>";
                            echo "<hr>";
                        } else { echo "<hr>";} ?>
                    <?php } else { ?>
                        <?php if ($bio) {
                            echo "<p>";
                            echo $bio;
                            echo "</p>";
                        }?>
                    <?php } ?>
                </div>

                <?php  $i++; endwhile; ?>
            <?php endif; ?>

        </div><!-- row -->
        
        
        <div class="row foundation_wysiwyg_row">
            <div class="col-sm-8 col-sm-offset-2" id="foundation_wysiwyg">
                <?php echo $wysiwyg_editor;?>
            </div>
        </div>
        

    </div><!-- container -->
</section><!-- content area -->


<?php
get_footer();