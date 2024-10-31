jQuery(document).ready(function($){

    jQuery('input[name=qrr_date]').datepicker({
        dateFormat: 'yy-mm-dd',
        showOtherMonths: true,
        selectOtherMonths: true,
        gotoCurrent: true
    });

    // Fill the email, post_title, phone from the user selected
    $('#fill_from_customer').change(function(ev){

        var option = $('#fill_from_customer option:selected');
        var email = option.val();
        if (email != '') {
            $('input[name=qrr_email]').val(email);
            $('input[name=qrr_phone]').val(option.attr('data-phone'));
            $('input[name=post_title]').val(option.attr('data-name'));
        }

    });

    // Hide/Show option to send email depens on the post_status
    $('select[name=post_status]').change(function(ev){
        var status = $('select[name=post_status]').val();
        if (status == 'qrr-cancelled') {
            $('#send_email_to_customer').hide();
        } else {
            $('#send_email_to_customer').show();
        }
    });

    $('select[name=post_status]').trigger('change');


    // Show email list
    /*
    $('.qrr-email-list').on('click','.link-show-email',function(ev){
        ev.preventDefault();

        var $el = $(ev.target);
        var data_time = $el.attr('data-time');

        //qrr_booking
        for( var i = 0; i < qrr_booking.emails.length; i++) {
            var email = qrr_booking.emails[i];
            if (email.time == data_time){
                console.log(email.message);
                $('#qrr-popup-email .popup-inner').html(email.message);
                $('#qrr-popup-email').addClass('open');
            }
        }

    });

    // Close email popup
    $('#qrr-popup-email').click(function(ev){
        ev.preventDefault();
        $('#qrr-popup-email').removeClass('open');
    });
    */


    new Vue({
        el: '#qrr-emails',
        data: {
            booking_id: window.qrr_booking.booking_id,
            email_list: window.qrr_booking.emails,
            email_selected: null,
            popup_show: false,
            update_email: '',
            sending_message: false,
            sent_message: false
        },
        methods: {
            format_time: function(time){
                return moment(time*1000).format('dddd, MMMM Do YYYY, h:mm:ss');
            },
            show_email: function(item){
                this.email_selected = item;
                this.popup_show = true;
            },
            close_popup: function(){
                this.popup_show = false;
            },
            send_email: function(){

                var self = this;

                var post_data = {
                    action: 'send_update_email',
                    booking_id: this.booking_id,
                    message: this.update_email
                };

                this.sending_message = true;

                $.post( ajaxurl, post_data, function(response){

                    if (response.success){

                        self.email_list = response.data.list;
                        self.sending_message = false;
                        self.sent_message = true;
                        self.update_email = '';

                        setTimeout(function(){
                            self.sent_message = false;
                        }, 3000);

                    } else {

                        alert("Error");
                        self.sending_message = false;

                    }

                });
            }
        }
    });


});