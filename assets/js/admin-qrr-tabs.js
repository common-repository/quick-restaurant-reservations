jQuery(document).ready(function($){


    // qrr_restaurant global tabs
    $('#qrr-metabox-global .nav-tab-wrapper').on('click', '.nav-tab', function(ev){

        ev.preventDefault();

        $('#qrr-metabox-global .nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
        $(ev.target).addClass('nav-tab-active');

        var href = $(ev.target).attr('href');
        $('#qrr-metabox-global .tab-contents .group').removeClass('active');
        $(href).addClass('active');

        $('input[name=qrr_selected_tab]').val(href);

    });

    /*var selected_tab = $('input[name=qrr_selected_tab]');
    if (selected_tab.length == 1){
        var value = selected_tab.val();
        $('a[href="'+value+'"]').trigger('click');
    }*/

    // email tabs
    $('#qrr-email-tabs a').click(function(ev){

        ev.preventDefault();

        $('#qrr-email-tabs a').removeClass('active');
        $(ev.target).addClass('active');

        var href = $(ev.target).attr('href');
        $('#qrr-email-tabs .qrr-tab-content').removeClass('active');
        $(href).addClass('active');

    });

});