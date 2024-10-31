jQuery(document).ready(function($){
    if ($('#qrr-create-booking').length == 1){

        var QRR = QRR_CREATE_BOOKING;

        new Vue({
            el:'#qrr-create-booking',
            data: {
                loading: false,
                step: 1,
                restaurants: QRR.restaurants,
                selected_restaurant: '',
                dates: [],
                selected_date: '',
            },
            watch: {
                selected_restaurant: function(newval){
                    this.get_dates();
                },
            },
            created: function(){
                console.log('created');
            },

            methods: {

                post: function( post_data, callback){
                    var self = this;
                    self.loading = true;
                    $.post( ajaxurl, post_data, function(response){
                        self.loading = false;
                        if (response.success){
                            callback(response.data);
                        } else {
                            alert('ERROR connecting server');
                        }

                    });
                },

                get_dates: function(){
                    var self = this;
                    var post_data = {
                        action: 'get_dates',
                        restaurant_id: this.selected_restaurant
                    };
                    this.post(post_data, function(data){
                        self.step = 2;
                        console.log(data);
                    });
                },

                get_schedules: function(){
                    var self = this;
                    var post_data = {
                        action: 'get_schedules',
                        restaurant_id: this.selected_restaurant
                    };
                    this.post(post_data, function(data){
                        self.schedules = data.schedules;
                        self.step = 2;
                    });
                },








            }
        });




    }
});