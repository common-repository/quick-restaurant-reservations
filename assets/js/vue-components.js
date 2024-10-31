//-----------------------------------
// COMPONENTS
//-----------------------------------

// https://codepen.io/nicolebek/pen/oxgvxe
// https://stackoverflow.com/questions/40009197/update-model-from-custom-directive-vuejs





// Time picker from to
//-------------------------------
Vue.component('vue-weekdays',{
    template: '#vue-weekdays',
    props:['value','names'],
    data: function(){
        return {
            daysname: ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
            days: [ {active: false}, {active: true}, {active: true}, {active: true}, {active: true}, {active: true}, {active: true} ]
        }
    },
    watch: {
        value: {
            deep: true,
            handler: function(newvalue){
                this.days = newvalue;
            }
        },
        days: function(){
            this.$emit('input', this.days);
        }
    },
    created: function(){
        this.days = this.value;
        if ('undefined' != typeof this.names && this.names.length == 7) {
            this.daysname = this.names;
        }
    }
});


// Date picker
//-------------------------------
Vue.component('vue-datepicker', {
    template: '#vue-datepicker',
    props:['value','format'],
    data: function(){
        return {
            internalValue: ''
        }
    },
    watch: {
        value: function(newvalue) {
            this.internalValue = newvalue;
        },
        internalValue: function(){
            this.$emit('input', this.internalValue);
        }
    },
    methods: {
        setup: function(){
            var self = this;
            jQuery(self.$el).datepicker({
                defaultDate: self.internalValue,
                dateFormat: self.format,
                onSelect: function(date){
                    self.internalValue = date;
                }
            })
        }
    },
    created: function(){
        var self = this;
        this.internalValue = this.value;
        // https://github.com/vuejs/vue/issues/2421
        //setTimeout(function(){ self.setup(); }, 0);
        this.$nextTick(function(){ this.setup(); });
    }
});



// Hour picker
//-------------------------------
Vue.component('vue-hourpicker', {
    template: '#vue-hourpicker',
    props:['value'],
    data: function(){
        return {
            internalValue: ''
        }
    },
    watch: {
        value: function(newvalue) {
            this.internalValue = newvalue;
        },
        internalValue: function(){
            this.$emit('input', this.internalValue);
        }
    },
    methods: {
        setup: function(){
        }
    },
    created: function(){
        var self = this;
        this.internalValue = this.value;
        this.$nextTick(function(){ this.setup(); });
    }
});

// Minutes picker
//-------------------------------
Vue.component('vue-minutespicker', {
    template: '#vue-minutespicker',
    props:['value'],
    data: function(){
        return {
            internalValue: ''
        }
    },
    watch: {
        value: function(newvalue) {
            this.internalValue = newvalue;
        },
        internalValue: function(){
            this.$emit('input', this.internalValue);
        }
    },
    methods: {
        setup: function(){
        }
    },
    created: function(){
        var self = this;
        this.internalValue = this.value;
        this.$nextTick(function(){ this.setup(); });
    }
});



// Date picker from to
//-------------------------------
Vue.component('vue-datepicker-fromto', {
    template: '#vue-datepicker-fromto',
    props:['value','format'],
    data: function(){
        return {
            dates: {from:'', to:''}
        }
    },
    watch: {
        value: {
            deep: true,
            handler: function(newvalue){
                this.dates = newvalue;
            }
        },
        dates: function(){
            this.$emit( 'input', this.dates );
        }
    },
    methods: {
        setup: function(){
            var self = this;
            jQuery(this.$el).find('.datepicker-from').datepicker({
                defaultDate: self.dates.from,
                dateFormat: self.format,
                onSelect: function(date){
                    self.dates.from = date;
                }
            });
            jQuery(this.$el).find('.datepicker-to').datepicker({
                defaultDate: self.dates.to,
                dateFormat: self.format,
                onSelect: function(date){
                    self.dates.to = date;
                }
            });
        }
    },
    created: function(){
        this.dates = this.value;
        this.$nextTick(function(){ this.setup(); });
    }
});


// Time picker from to
//-------------------------------
Vue.component('vue-timepicker', {
    template: '#vue-timepicker',
    props:['value'],
    data: function(){
        return {
            time: {from:'', to:''}
        }
    },
    watch: {
        value: {
            deep: true,
            handler: function(newvalue){

                console.log('TimePicker newvalue: ' + newvalue.from + ' - ' + newvalue.to );

                if (newvalue.from != this.time.from && newvalue.to != this.time.to) {
                    this.time = newvalue;
                    var self = this;
                    jQuery(this.$el).find('.rangeslider').data("ionRangeSlider").update({
                        from: self.hour_to_number(self.time.from),
                        to: self.hour_to_number(self.time.to)
                    });
                }
            }
        },
        time: function(){
            this.$emit( 'input', this.time );
        }
    },
    methods: {
        setup: function(){
            var self = this;
            jQuery(this.$el).find('.rangeslider').ionRangeSlider({
                type: "double",
                min: 0,
                max: 24*4, // Cada 15 minutos
                from: self.hour_to_number(self.time.from),
                to: self.hour_to_number(self.time.to),
                keyboard: true,
                grid: true,
                grid_snap: true,
                prettify: function (num) {
                    return self.number_to_hour(num);
                },
                onStart: function (data) {
                },
                onChange: function (data) {
                    self.time.from = self.number_to_hour( data.from );
                    self.time.to = self.number_to_hour( data.to );
                    //self.$emit( 'input', self.time );
                    //console.log(self.time.from + ' | ' + self.time.to);
                },
                onFinish: function (data) {

                },
                onUpdate: function (data) {
                }
            });
        },
        number_to_hour: function(num) {
            var h = parseInt(num/4);
            var m = 15*(num-h*4);
            h = h<10 ? '0'+h : h;
            m = m<10 ? '0'+m : m;
            return h+':'+m;
        },
        hour_to_number: function(hour) {
            //'12:50';
            var hm = hour.split(':');
            var h = parseInt(hm[0]);
            var m = parseInt(hm[1]);
            var n = h*4+m/15;
            return n;
        }
    },
    created: function(){
        var self = this;
        this.time = this.value;
        if (this.time.from == '' || 'undefined' == typeof this.time.from) {
            this.time.from = '10:00';
        }
        if (this.time.to == '' || 'undefined' == typeof this.time.to) {
            this.time.to = '23:00';
        }
        this.$nextTick(function(){ this.setup(); });
    }
});

Vue.component('vue-yesno', {
    template: '#vue-yesno',
    props:['value','title_yes','title_no'],
    data: function(){
        return {
            active: true
        }
    },
    watch: {
        value: function(newval){
            this.active = newval;
        },
        active: function(val, oldVal){
            this.$emit( 'input', this.active );
        }
    },
    methods: {
        //updateValue: function(newValue){
        //    console.log('UpdateValue YESNO');
        //    console.log(newValue);
        //},
    },
    created: function(){
        var self = this;
        this.active = this.value;
    }
});


// Select
//-------------------------------

Vue.component('vue-select', {
    template: '#vue-select',
    props: ['value','items'],
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