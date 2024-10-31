jQuery(document).ready(function($){


    function setupSetting_BookingSchedule() {

        //-----------------------------------
        // VUE
        //-----------------------------------

        window.vue_schedule = new Vue({
            el: '#booking-schedule',
            data: {
                input_tag: '#qrr_booking_schedule',
                help: false,
                days: ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
                time_intervals: window.qrr_options.time_intervals,
                schedules: [],

                schedule_popup_open: false,
                schedule_selected: null,
                schedule_tab: 'dates',

                duration_items: window.qrr_options.duration_options,
                notifications_type: window.qrr_options.notification_options,

                test_date:{
                    day:'',
                    hour:'00',
                    minutes:'00'
                },
                test_result: '',

                time_specific: {
                    hour: '00',
                    minutes: '00'
                }
            },

            watch: {
                schedules: {
                    deep: true,
                    handler: function(){
                        var json = JSON.stringify(this.schedules);
                        $(this.input_tag).val(json);
                        //console.log(json);
                    }
                }
            },
            methods: {
                toggleVisible: function(schedule){
                    schedule.visible = !schedule.visible;
                },
                removeSchedule: function(schedule){
                    var newSchedules = [];
                    for( var i = 0; i < this.schedules.length; i++){
                        if (this.schedules[i] != schedule ) {
                            newSchedules.push(this.schedules[i]);
                        }
                    }
                    this.schedules = newSchedules;
                },
                addSchedule: function(){
                    var newSchedule = this.getNewSchedule();
                    this.schedules.push(newSchedule);
                },

                getNewSchedule: function(){
                    return {
                        //id: lastid+1,
                        name: 'New schedule',
                        active: true,
                        opened: true,
                        visible: true,
                        days: [ {active: true}, {active: true}, {active: true}, {active: true}, {active: true}, {active: true}, {active: true} ],
                        dates: { from: '', to: '' },
                        alldates: true,

                        time_type: 'interval',
                        time: { from: '08:00', to: '22:00' },
                        time_interval: 15,
                        time_specific: [], // Defined hours

                        alltime: false,

                        capacity: 'capacity_unlimited',
                        capacity_seats: {
                            total: 0,
                            duration: 0
                        },
                        capacity_tables: [],
                        notifications: {
                            confirmation_type: 'manual',
                            automatic_party: false,
                            automatic_party_number: 8,
                            automatic_seats: false,
                            automatic_seats_number: 20
                        },
                        test_applied: false,

                        late_bookings_overwrite: 'no',
                        late_bookings_mode: 'days_and_time', // days_and_time
                        late_booking_hours: 2,
                        late_bookings_days: 1,
                        late_bookings_time: 8*3600
                    };
                },
                dragstart: function(data){
                },
                dragend: function(data){

                    // I have deep watch on props now, no need of reloading
                    return;

                    // Force updating list to reload components
                    var newSchedules = [];
                    for(var i = 0; i < this.schedules.length; i++){
                        newSchedules.push(this.schedules[i]);
                    }
                    this.schedules = [];
                    this.$nextTick(function(){ this.schedules = newSchedules; });
                },

                // Manage popup
                //-----------------------
                openPopup: function(schedule){
                    this.schedule_selected = schedule;
                    this.schedule_tab = 'dates';
                    this.schedule_popup_open = true;
                    jQuery('body').addClass('qrr_popup_open');
                },
                closePopup: function(){
                    this.schedule_popup_open = false;
                    jQuery('body').removeClass('qrr_popup_open');
                },

                // Capacity schedule
                //-----------------------
                addNumTable: function(){
                    this.schedule_selected.capacity_tables.push({
                        num: 1, seats: 2, duration: 0
                    });
                },
                removeNumTable: function(table){
                    var newTables = [];
                    for( var i = 0; i < this.schedule_selected.capacity_tables.length; i++){
                        if (this.schedule_selected.capacity_tables[i] != table ) {
                            newTables.push(this.schedule_selected.capacity_tables[i]);
                        }
                    }
                    this.schedule_selected.capacity_tables = newTables;
                },
                copyGeneralTables: function(){

                    if ('undefined' == typeof window.vue_capacity.$data.capacity.tablesNum){
                        return;
                    }

                    var tables = window.vue_capacity.$data.capacity.tablesNum;
                    var newList = [];
                    for( var i = 0; i < tables.length; i++) {
                        var t = tables[i];
                        newList.push({ num: t.num, seats: t.seats, duration: t.duration });
                    }
                    this.schedule_selected.capacity_tables = newList;
                },

                // Test date against schedules
                //-----------------------
                test_the_date: function(){
                    this.test_result = 'Checking schedules...';

                    var self = this;

                    var post_data = {
                        action : 'test_schedules_date',
                        restaurant_id: jQuery('#qrr_booking_schedule').attr('data-restid'),
                        date: this.test_date.day + ' ' + this.test_date.hour + ':' + this.test_date.minutes + ':00',
                        schedules: jQuery('#qrr_booking_schedule').val()
                    };

                    $.post( ajaxurl, post_data, function(response){

                        if (response.success) {
                            self.test_result = response.data.messages.join('<br>');
                            var indexes = response.data.rules;
                            for(var i = 0; i<indexes.length; i++){
                                self.schedules[indexes[i]].test_applied = true;
                            }
                            setTimeout(function(){
                                for(var i = 0; i<indexes.length; i++){
                                    self.schedules[indexes[i]].test_applied = false;
                                }
                            }, 2000);

                        } else {
                            self.test_result = response.data;
                        }


                    }, 'json');
                },


                // Schedules test_applied = false
                //-----------------------
                setTestAppliedToFalse: function( schedules ){
                    for (var i = 0; i < schedules.length; i++) {
                        schedules[i].test_applied = false;
                    }
                },

                // Complete schedules
                //-----------------------
                completeSchedulesObject: function( schedules ){

                    // Merge with new properties for the schedule in case
                    // is saved with old version
                    var new_schedule = this.getNewSchedule();
                    var properties = Object.getOwnPropertyNames(new_schedule);
                    for( var i = 0; i < schedules.length; i++) {
                        for (var j = 0; j < properties.length; j++) {
                            var prop = properties[j];
                            if ('undefined' == typeof schedules[i][prop]){
                                schedules[i][prop] = JSON.parse(JSON.stringify(new_schedule[prop]));
                            }
                        }
                    }
                },


                // Schedule time interval
                //-----------------------
                addTimeSpecific: function() {

                    var hour = this.time_specific.hour+':'+this.time_specific.minutes;

                    var found = false;
                    for( var i = 0; i < this.schedule_selected.time_specific.length; i++){
                        if (this.schedule_selected.time_specific[i] == hour ) {
                            found = true;
                        }
                    }

                    if (!found) {
                        this.schedule_selected.time_specific.push(hour);
                    }
                },
                removeTimeSpecific: function(hour){
                    var new_hours = [];
                    for( var i = 0; i < this.schedule_selected.time_specific.length; i++){
                        if (this.schedule_selected.time_specific[i] != hour ) {
                            new_hours.push(this.schedule_selected.time_specific[i]);
                        }
                    }
                    this.schedule_selected.time_specific = new_hours;
                },

                // Capacity title
                //-----------------------

                cleanTitle: function(value){
                    return value
                        .replace(/_/g, " ")
                        .toLowerCase()
                        .split(' ')
                        .map(function(word) {
                            return word[0].toUpperCase() + word.substr(1);
                        })
                        .join(' ')
                        .replace('Capacity','');
                }

            },
            computed: {
                sortedHours: function() {

                    function compare(a, b) {
                        if (a < b)
                            return -1;
                        if (a > b)
                            return 1;
                        return 0;
                    }

                    return this.schedule_selected.time_specific.sort(compare);
                }
            },

            events: {

            },
            created: function(){
                var json_str = $(this.input_tag).val();
                if ( 'undefined' != typeof json_str && json_str != '' ) {

                    var json = JSON.parse(json_str);
                    var schedules = json;

                    // Clean test_applied = false, just in case
                    this.setTestAppliedToFalse( schedules );

                    // Complete schedules data
                    this.completeSchedulesObject( schedules );

                    // Assign at the end so have be reactive
                    this.schedules = schedules;
                }

            }
        });

    }


    //-----------------------------------
    // INIT
    //-----------------------------------

    if ($('#booking-schedule').length > 0) {
        setupSetting_BookingSchedule();
    }



    //-----------------------------------
    // File Upload field
    //-----------------------------------

    var _custom_media = true,
        _orig_send_attachment = wp.media.editor.send.attachment;

    $('.fileupload .button').click(function(e) {
        var container = $(this).parent();
        var preview_image = $(this).next('.preview-image');
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        var id = button.attr('id').replace('_button', '');
        _custom_media = true;
        wp.media.editor.send.attachment = function(props, attachment){
            if ( _custom_media ) {
                $("#"+id).val(attachment.url);
                if (preview_image.length){
                    $(preview_image).html('<img src="'+ attachment.url + '"  >');
                }
            } else {
                return _orig_send_attachment.apply( this, [props, attachment] );
            };
        }
        wp.media.editor.open(button);
        return false;
    });

    $('.add_media').on('click', function(){
        _custom_media = false;
    });

});


