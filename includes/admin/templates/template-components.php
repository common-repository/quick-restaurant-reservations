
<template id="vue-yesno">
    <label class="qrr-checkbox-yesno" :class="{'status-yes':active,'status-no':!active}">
        <input type="checkbox" v-model="active">
        <div class="status">
            <span class="title-yes" v-text="title_yes"></span>
            <span class="title-no" v-text="title_no"></span>
        </div>
    </label>
</template>

<template id="vue-weekdays">
    <div class="vue-group-data">
        <div v-for="(day,index) in days" class="qrr-checkbox-day">
            <label>
                <input type="checkbox" v-model="day.active">
                <span>{{daysname[index]}}</span>
            </label>
        </div>
    </div>
</template>

<template id="vue-datepicker">
    <input type="text" v-model="internalValue">
</template>

<template id="vue-hourpicker">
    <select v-model="internalValue">
        <?php for($i=0; $i<=23; $i++){
            $cadena = $i<10 ? '0'.$i : ''.$i;
            echo '<option value="'.esc_attr($cadena).'">'.esc_html($cadena).'</option>';
        }?>
    </select>
</template>

<template id="vue-minutespicker">
    <select v-model="internalValue">
        <?php for($i=0; $i<=55; $i+=5){
            $cadena = $i<10 ? '0'.$i : ''.$i;
            echo '<option value="'.esc_attr($cadena).'">'.esc_html($cadena).'</option>';
        }?>
    </select>
</template>

<template id="vue-datepicker-fromto">
    <div class="vue-group-data">
        <span><?php _ex('Dates from', 'settings', 'qrr'); ?></span> <input type="text" class="datepicker-from" v-model="dates.from">
        <span><?php _ex('to', 'settings', 'qrr'); ?></span> <input type="text" class="datepicker-to" v-model="dates.to">
    </div>
</template>

<template id="vue-timepicker">
    <div class="vue-group-data">
        <input type="text" class="rangeslider">
        <input type="hidden" v-model="time.from">
        <input type="hidden" v-model="time.to">
    </div>
</template>

<template id="vue-select">
    <select class="" v-model="selected">
        <option v-for="item in list" :value="item.value">{{item.name}}</option>
    </select>
</template>

