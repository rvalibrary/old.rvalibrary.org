(function($){

    $( document ).ready(function() {
            $('#meeting_rooms_iframe').iFrameResize();
    });

    $(document).ready(function() {
        new mainSlider();
    });

    //****Vertical Align Issuu Synopsis****//
    // $( document ).ready(function() {
    //     var calc_marg = $('#issuu_row').outerHeight(true) - $('#issuu_synopsis_col').outerHeight(true);
    //     $('#issuu_synopsis_col').css('margin-top', calc_marg/2);
    // });


//****Search Bar****//
    $("#button_catalog").click(function() {
        $(this).closest("form").attr("action", "http://ibistro.ci.richmond.va.us/uhtbin/cgisirsi/x/0/0/123?");
        $(this).closest("form").attr("onsubmit", "_gaq.push(['_trackEvent','Catalog','Search',this.href]);");
        $(this).closest("form").attr('target', '_blank');
        $('#search_box').val('');
        $('#search_box').attr('name', 'searchdata1');
        $('input#search_box').attr('placeholder', 'Search the Catalog');
    });
    $("#button_site").click(function() {
        $(this).closest("form").attr("action", "/");
        $(this).closest("form").attr("onsubmit", "");
        $(this).closest("form").attr('target', '_self');
        $('#search_box').val('');
        $('#search_box').attr('name', 's');
        $('input#search_box').attr('placeholder', 'Search the Site');
    });



//****Google Translate****//
    $("#translate_button").mouseover(function(){
        $("#translate_button").css("display", "none");
        $("#google_translate").css("display", "inline");
    });
    $("#google_translate").mouseleave(function(){

        $("#google_translate").css("display", "none");
        $("#translate_button").css("display", "inline");
    });



//****Define Slider function****//
    function mainSlider(){

    $(".swiper-slide").owlCarousel({
        singleItem: true,
        loop:true,
        autoPlay: 7000,
        responsiveRefreshRate: 1,
        slideSpeed: '500',
        paginationSpeed: '500',
        theme: "",
        stopOnHover: true, transitionStyle : "fade"

    });

    }




    $(".ai1ec-category-filter .ai1ec-dropdown-toggle").text("Branch");
    $(".ai1ec-category-filter .ai1ec-dropdown-toggle").toggleClass('changed');

    //call the libcal booking
    // $("#eq_2710").LibCalEquipmentBooking({iid: 4083, gid: 2710, eid: 0, width: 560, height: 680});


})(jQuery);
