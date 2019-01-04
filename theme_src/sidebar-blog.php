<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>



<div class="col-sm-3" id="sidebar">
    <div class="containment_box" id="search_containment">
            <div class="banner">
                    <h4 class="vertical_align">Search</h4>
            </div>


            <!--=============SEARCH FORM BOOYAH ==============-->
            <div id="search_form_wrapper">
                <form role = "search" id="searchform" action="/" method="get"
                style="padding:0px;margin:0px;z-index:-1;" target="_self">


                <fieldset class="form-group" id="search_box_wrapper">
                        <input id="search_box" type="text" class="form-control" placeholder="Search the Site" name="s">
                </fieldset>
                <fieldset class="form-group align-center center" id="search_radio_button_wrapper">
                    <label class="radio-inline"><input type="radio" name="search_select" id="button_catalog">Catalog</label>
                    <label class="radio-inline"><input type="radio" name="search_select" id="button_site" checked>Site</label>
                    <input type="submit" class="submit" value="" style="display:none;">
                </fieldset>
                </form><!-- search form -->

                <!-- advanced search -->
                    <div class="center" id="advanced_wrapper"><a href="http://ibistro.ci.richmond.va.us/uhtbin/cgisirsi/x/0/0/49" target="\blank">Power Search</a></div>
            </div>

    </div><!--containment_box -->



    <?php dynamic_sidebar( 'sidebar-blog' ); ?>





</div><!-- end sidebar col -->
