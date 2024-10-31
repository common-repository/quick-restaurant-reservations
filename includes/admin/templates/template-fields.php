<?php

wp_enqueue_script('admin-booking-fields-main');

?>

<div id="booking-fields">

    <div class="qrr-fields-wrap">

        <div v-for="field in fields" class="qrr-field-wrap">
            <div class="qrr-form-field" :class="fieldclass(field)">
                <span v-if="field.required && field.type!='header'"><i class="fa fa-asterisk"></i></span>
                <span class="f-title">{{field.title}}</span>
                <span class="f-icon f-icon-right" @click="edit(field)"><i class="fa fa-pencil"></i></span>
            </div>
        </div>

        <a href="#" class="button reset-fields" @click.prevent="resetFields"><?php _ex('Reset fields', 'Custom fields', 'qrr_f'); ?></a>

    </div>

    <!-- POPUP EDITOR FIELD -->
    <div class="qrr-popup popup-field-editor" :class="{'open':popup_open}" v-if="null != field_selected">
        <div class="popup-inner-wrap">
            <div class="popup-inner">


                <a href="#" class="popup-close-icon" @click.prevent="closePopup"><i class="fa fa-times"></i></a>

                <div class="popup-title">{{field_selected.title}}</div>

                <div class="popup-content">

                    <div class="qrr-field" v-if="field_selected.canremove">
                        <span v-if="field_selected.canremove && field_selected.type != 'header'">
                            <label>
                                <?php _ex('Required', 'Required', 'qrr_f'); ?>
                                <input type="checkbox" v-model="field_selected.required">
                            </label>
                        </span>
                    </div>

                    <div class="qrr-field">
                        <label><?php _ex('Title', 'Edit custom field', 'qrr_f'); ?></label>
                        <input type="text" v-model="field_selected.title">
                    </div>

                    <div class="qrr-field" v-if="field_selected.type != 'header'">
                        <label><?php _ex('Description', 'Edit custom field', 'qrr_f'); ?></label>
                        <input type="text" v-model="field_selected.description">
                    </div>

                </div>


            </div>
        </div>
    </div>

</div>
