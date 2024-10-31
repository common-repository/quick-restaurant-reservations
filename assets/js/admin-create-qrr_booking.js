jQuery(document).ready(function($){
    if ($('#qrr-create-booking').length == 1){


        var vuebooking = new Vue({
            el:'#qrr-create-booking',
            data: {
                loading: false,
                app_qrr: app_qrr,
                restrict_conditions: true,
                booking: {
                    date: { time: 0, text: '', day:''},
                    party: 0,
                    hour: '',

                    // Not restricted to booking rules
                    hour_schedule: '',
                    hour_schedule_hour: '',

                    name: '',
                    email: '',
                    phone: '',
                    message: '',
                    status: ''
                },
                previous: {
                    time: 0,
                    party: 0
                },
                hours_available: [],
                hours_schedules: [],
                hours_message: '',

                submitting: false,
                submitted: false,
                submitted_success_msg: '',
                submitted_error_msg: '',

            },
            watch: {
                booking: {
                    deep: true,
                    handler: function(newvalue){

                        // Check date and party has changed
                        if (this.booking.date.time != this.previous.time){
                            this.previous.time = this.booking.date.time;
                            console.log('Date or Party has changed');
                            this.get_available_hours();
                        }
                    }
                }
            },
            created: function(){

            },

            methods: {

                post: function( post_data, callback){
                    var self = this;
                    self.loading = true;
                    $.post( ajaxurl, post_data, function(response){
                        self.loading = false;
                        callback(response);
                    });
                },

                get_available_hours: function(){

                    var self = this;

                    var day = self.booking.date.day;
                    console.log('day: '+day);
                    if ( "" == day || typeof day == 'undefined' ) return;

                    var post_data = {
                        action : 'get_available_hours',
                        nonce  : self.app_qrr.nonce,
                        id: self.app_qrr.form.post_id,
                        date : self.booking.date,
                        party: self.booking.party
                    };

                    self.post( post_data, function(response){

                        if (response.success){
                            self.hours_available = response.data.hours;
                            self.hours_slots = [];
                            for (var i = 0; i < self.hours_available.length; i++){
                                self.hours_schedules.push({index:i, label: self.hours_available[i].label});
                            }
                            self.booking.hour = self.hours_available[0].list[0].value;
                            self.hours_message = '';
                        } else {
                            self.hours_message = response.data;
                        }

                    });
                },

                update_customer: function(name, email, phone) {
                    this.booking.name = name;
                    this.booking.email = email;
                    this.booking.phone = phone;
                },

                displayErrorMessage: function(message){
                    var self = this;
                    self.submitted_error_msg = message;
                    setTimeout(function(){
                        self.submitted_error_msg = '';
                    }, 3000);
                },

                onSubmit: function(){

                    var self = this;

                    self.submitting = true;
                    self.submitted_error_msg = '';

                    var validate = this.validate();
                    if (!validate.success){
                        self.submitting = false;
                        self.displayErrorMessage(validate.message);
                        return;
                    }

                    var booking_data = jQuery(self.$el).find('form').serializeArray();

                    var post_data = {
                        action : 'request_booking',
                        nonce  : self.app_qrr.nonce,
                        id: self.app_qrr.form.post_id,
                        booking_data: booking_data,
                        restrict_conditions: self.restrict_conditions,
                        is_front_end: 'no'
                    }

                    console.log(post_data);
                    //return;

                    self.post(post_data, function(response){

                        console.log(response);

                        if (response.success) {

                            self.submitted = true;
                            self.submitting = false;
                            //self.submitted_success_msg = response.data.message;

                            if ( 'undefined' !== typeof response.data.message ) {
                                self.displayErrorMessage(response.data.message);
                            }

                            if ( 'undefined' !== typeof response.data.booking_edit ) {
                                // Redirect to booking_id
                                location.href = he.decode(response.data.booking_edit);
                            }


                        } else {

                            self.submitting = false;
                            self.submitted_error_msg = response.data;
                        }

                    });

                    //self.submitted = true;
                    //self.submitted_success_msg = 'NOW SUBMITTING';
                },

                validate: function(){

                    var self = this;
                    var valid = true;

                    var errors = [];

                    if (self.booking.date === '')
                        errors.push('Date is empty.');

                    if (self.booking.party === '' || self.booking.party == 0)
                        errors.push('Party must be >0.');

                    if (self.booking.name === '')
                        errors.push('Name is empty.');

                    if (self.booking.phone === '')
                        errors.push('Phone is empty.');

                    if (self.booking.status === '')
                        errors.push('Status is empty.');

                    if (self.restrict_conditions){

                        if (self.booking.hour === '')
                            errors.push('Hour is empty.');
                    }

                    if (!self.restrict_conditions){

                        if (self.booking.hour_schedule === '' || 'undefined' == typeof self.booking.hour_schedule)
                            errors.push('Schedule is empty.');

                        if (self.booking.hour_schedule_hour === '')
                            errors.push('Hour is empty.');

                        if (/\d\d:\d\d/.test(self.booking.hour_schedule_hour) != true)
                            errors.push('Hour is not correct.');

                    }

                    if (errors.length > 0)
                        valid = false;

                    if (valid) {
                        return {success: true};
                    }

                    var message = '<ul>';
                    for (var i = 0; i < errors.length; i++){
                        message += '<li>'+errors[i]+'</li>';
                    }
                    message += '</ul>';

                    return {success: false, message: message};
                }


            }
        });


        // Fill the email, post_title, phone from the user selected
        $('#fill_from_customer').change(function(ev){
            var option = $('#fill_from_customer option:selected');
            var email = option.val();
            if (email != '') {
                vuebooking.update_customer( option.attr('data-name'), email, option.attr('data-phone'));
            }
        });


    }
});