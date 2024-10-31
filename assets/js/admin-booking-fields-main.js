jQuery(document).ready(function($){

    if ($('#booking-fields').length > 0) {

        //console.log('Creating new Vue Capacity');

        window.vue_fields = new Vue({
            el: '#booking-fields',
            data: {
                input_tag: '#qrr_booking_fields',
                fields: [],
                field_types: [],
                popup_open: false,
                field_selected: null,
            },
            watch: {
                fields: {
                    deep: true,
                    handler: function(){ this.updateJsonField(); }
                },
            },
            methods: {
                updateJsonField: function(){
                    var json = this.fields;
                    $(this.input_tag).val(JSON.stringify(json));
                },
                fieldclass: function(field){
                    return 'field-'+field.type+' field-'+ (field.canremove ? 'canremove' : 'cannotremove');
                },
                edit: function(field) {
                    this.field_selected = field;
                    this.popup_open = true;
                },
                closePopup: function(){
                    this.popup_open = false;
                },
                resetFields: function(){
                    this.fields = [
                        {type: 'header', id:'header-booking', title: 'Book a table', required: true, canremove: false, canmove: false, error:'',"description":""},
                        {type: 'date', id: 'qrr-date', title: 'Date', required: true, canremove: false, canmove: false, error:'',"description":""},
                        {type: 'party', id: 'qrr-party', title: 'Party', required: true, canremove: false, canmove: false, error:'',"description":""},
                        {type: 'time', id: 'qrr-time', title: 'Time', required: true, canremove: false, canmove: false, error:'',"description":""},
                        {type: 'header', id: 'header-contact', title: 'Contact', required: true, canremove: true, canmove: false, error:'',"description":""},
                        {type: 'name', id: 'qrr-name', title: 'Name', required: true, canremove: false, canmove: true, error:'',"description":""},
                        {type: 'email', id: 'qrr-email', title: 'Email', required: true, canremove: false, canmove: true, error:'',"description":""},
                        {type: 'phone', id: 'qrr-phone', title: 'Phone', required: false, canremove: true, canmove: true, error:'',"description":""},
                        {type: 'textarea', id: 'qrr-message', title: 'Message', required: false, canremove: true, canmove: true, error:'',"description":""},

                    ];
                }
            },
            created: function(){
                var json_str = $(this.input_tag).val();
                if ( 'undefined' != typeof json_str && json_str != '' ) {
                    var json = JSON.parse(json_str);
                    this.fields = json;
                }
            }
        });


    }
});