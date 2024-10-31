jQuery(document).ready(function($){

    $('input[name=booking_date]').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    // Tooltips
    var tiptip_args = {
        'attribute': 'data-tip',
        'fadeIn': 50,
        'fadeOut': 50,
        'delay': 200
    };
    $( '.tips, .help_tip' ).tipTip( tiptip_args );

});