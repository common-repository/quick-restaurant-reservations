// Pickadate
//-------------------------------
Vue.component('vue-pickadate', {
    template: '#vue-pickadate',
    props:['value','options','id','name'],
    data: function(){
        return {
            picker: null,
            date: {
                time: 0,
                text: '',
                day: ''
                //day: ''
            }
        }
    },
    watch: {
        value: function(newdate) {
            //this.date = newdate;
        },
        date: function(){
            //this.$emit('input', this.date);
            //console.log('Emitting input');
        }
    },
    methods: {
        updateparent: function(newtime){
            this.date.time = newtime;
            this.date.text = jQuery(this.$el).val();
            this.date.day = jQuery(this.$el).closest('.qrr-date').find('input[name="qrr-date_submit"]').val();
            //console.log(this.date);
            this.$emit('input', this.date);
        },
        setup: function(){
            var self = this;

            self.picker = jQuery(self.$el).pickadate({

                format: self.options.format,
                formatSubmit: 'yyyy/mm/dd',

                disable: self.options.disable_dates,
                min: !self.options.allow_past,

                firstDay: self.options.first_day,

                onStart: function() {

                    // Block days early bookings
                    this.set('max', self.options.early_bookings);

                }
                ,
                onRender: function() {
                    console.log('Whoa.. rendered anew')
                },
                onOpen: function() {
                    console.log('Opened up')
                },
                onClose: function() {
                    console.log('Closed now')
                },
                onStop: function() {
                    console.log('See ya.')
                },
                onSet: function(context) {
                    console.log('Just set stuff:', context)
                    self.updateparent(context.select);
                }
            });
        }
    },
    created: function(){
        this.$nextTick(function(){ this.setup(); });
    }
});



// Select
//-------------------------------

Vue.component('vue-select', {
    template: '#vue-select',
    props: ['value','items', 'id', 'name'],
    data: function(){
        return {
            selected: '',
            list: [
                {name: 'Option 0', value: 0},
                {name: 'Option 1', value: 1},
                {name: 'Option 2', value: 2},
            ]
        }
    },
    watch: {
        value: function(newval){
            this.selected = newval;
        },
        selected: function(val, oldVal){
            this.$emit( 'input', this.selected );
        }
    },
    methods: {
    },
    created: function(){
        this.selected = this.value;
        this.list = this.items;
    }
});