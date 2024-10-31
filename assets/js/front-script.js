// Javascript Front form Quick Restaurante Reservations

jQuery(document).ready(function ($) {

    if ($('.qrr-booking-form').length > 0) {

        new Vue({
            el: '.qrr-booking-form',
            data: {

                app_qrr: app_qrr,

                booking: {
                    date: { time: 0, text: '', day:''},
                    party: 0,
                    hour: '',
                    name: '',
                    email: '',
                    phone: '',
                    message: ''
                },

                loading_hours: false,
                hours_available: [],
                hours_is_available: false,
                hours_message: '',

                previous: {
                    time: 0,
                    party: 0
                },

                submitting: false,
                submitted: false,
                submitted_success_msg: '',
                submitted_error_msg: '',
                validation: {},

            },
            watch: {
                booking: {
                    deep: true,
                    handler: function(newvalue){

                        // Check date and party has changed
                        if (this.booking.date.time != this.previous.time || this.booking.party != this.previous.party){
                            this.previous.time = this.booking.date.time;
                            this.previous.party = this.booking.party;
                            //console.log('Date or Party has changed');
                            this.get_available_hours();
                        }
                    }
                }
            },
            methods: {
                setup: function(){

                },
                get_available_hours: function(){

                    if (this.step <2) return;

                    var self = this;

                    // Send data
                    self.submitted_error_msg = '';
                    self.loading_hours = true;

                    var post_data = {
                        action : 'get_available_hours',
                        nonce  : self.app_qrr.nonce,
                        id: self.app_qrr.form.post_id,
                        date : self.booking.date,
                        party: self.booking.party
                    };

                    $.post( self.app_qrr.ajax_url, post_data, function(response){

                        self.loading_hours = false;

                        if (response.success) {
                            self.hours_available = response.data.hours;
                            //console.log(self.hours_available);
                            self.booking.hour = self.hours_available[0]['list'][0].value;
                            self.hours_is_available = true;
                        } else {
                            //alert("ERROR");
                            self.hours_is_available = false;
                            self.hours_message = response.data;
                        }

                    }, 'json');
                },

                onSubmit: function(){

                    var self = this;
                    self.submitting = true;
                    self.submitted_error_msg = '';

                    var booking_data = jQuery(self.$el).find('form').serializeArray();


                    var post_data = {
                        action : 'request_booking',
                        nonce  : self.app_qrr.nonce,
                        id: self.app_qrr.form.post_id,
                        //booking : self.booking,
                        booking_data: booking_data,
                        is_front_end: 'yes'
                    };

                    $.post( self.app_qrr.ajax_url, post_data, function(response){

                        if (response.success) {

                            self.submitted = true;
                            self.submitted_success_msg = response.data.message;

                            if ( 'undefined' != typeof response.data.redirect && response.data.redirect != '') {
                                setTimeout(function(){
                                    if (response.data.redirect == 'same'){
                                        location.reload();
                                    } else {
                                        location.href = response.data.redirect;
                                    }
                                }, response.data.timeout);
                            }

                        } else {

                            self.submitting = false;

                            if ( 'undefined' != typeof response.data.validation ) {
                                self.validation = response.data.validation;
                            } else {
                                self.validation = {};
                                self.submitted_error_msg = response.data;
                            }

                        }

                    }, 'json');

                },

                has: function(name){
                    return 'undefined' != typeof this.validation[name] && this.validation[name] != '';
                },

                errorClass: function(name) {
                    return this.has(name) ? 'qrr-field-error' : '';
                },

                clearError: function(name){
                    if ( 'undefined' != typeof this.validation[name] ) {
                        this.validation[name] = '';
                    } else {

                    }
                },

            },

            computed: {
                step: function(){
                    step = 0;
                    if (this.booking.date.time > 0) {
                        step++;
                        if (this.booking.party > 0) {
                            step++;
                            if (this.booking.time !='' && this.hours_is_available) {
                                step++;

                            }
                        }
                    }
                    return step;
                }
            },

            events: {},

            created: function(){
                this.$nextTick(function(){ this.setup(); });
            }
        });
    }


});