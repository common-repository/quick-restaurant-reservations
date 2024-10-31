<?php

global $qrr_post_id;

// Javascrit data
$restaurant = new QRR_Restaurant($qrr_post_id);
$app_qrr = array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce'       => wp_create_nonce('qrr_form'),
    'form'     => $restaurant->get_form_data()
);

wp_localize_script('qrr-front-script', 'app_qrr', $app_qrr);

// Main script
wp_enqueue_script('qrr-front-script');

// Translation for pickadate
wp_enqueue_script('picker-locale');

$loading_gif = QRR_PLUGIN_URL.'assets/img/spinner_2.gif';

$booking_general = get_post_meta($qrr_post_id, 'qrr_booking_general', true);
$min_party = isset($booking_general['min_party']) ? intval($booking_general['min_party']) : 1;
$max_party = isset($booking_general['max_party']) ? intval($booking_general['max_party']) : 50;

$fields = json_decode(get_post_meta($qrr_post_id, 'qrr_booking_fields', true), ARRAY_A);


do_action('qrr_before_form', $qrr_post_id);

if (!function_exists('echo_field_label') ) {
    function echo_field_label( $field )
    {
        ?>
        <label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_attr($field['title']); ?><?php echo $field['required'] ? ' *':''; ?></label>
        <input type="hidden" name="label_<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($field['title']); ?>">
        <input type="hidden" name="type_<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($field['type']); ?>">
        <?php
    }
}

if (!function_exists('echo_field_bottom') ) {
    function echo_field_bottom( $field )
    {
        ?>
        <div class="qrr-error-valid" v-if="has('<?php echo esc_attr($field['id']); ?>')"><span v-text="validation['<?php echo esc_attr($field['id']); ?>']"></span></div>
        <div class="description"><?php echo esc_html($field['description']); ?></div>
        <?php
    }
}


?>

<div class="qrr-booking-form">
    <form method="POST" action="" v-if="!submitted" @submit.prevent="onSubmit" @keydown="clearError($event.target.name)">

        <?php

        do_action('qrr_before_form_inside', $qrr_post_id);

        $group_index = 0;
        $group_opened = false;
        foreach( $fields as $field) {

            $field = apply_filters('qrr_form_field', $field, $qrr_post_id);
            do_action('qrr_form_field_before', $field, $qrr_post_id);

            switch($field['type']) {

            case 'header':
                if ($group_opened) {
                    echo '</fieldset>';
                }
                $group_opened = true;
                $group_index++;

                if ($group_index == 1) {
                    echo '<fieldset>';
                } else {
                    echo '<fieldset v-if="step >= 3 && !loading_hours">';
                }

                echo '<legend>'.esc_attr($field['title']).'</legend>';
                break;

            case 'date': ?>
                    <div class="qrr-text qrr-date" :class="errorClass('qrr-date')">
                        <label for="qrr-date"><i class="fa fa-calendar"></i><?php echo esc_attr($field['title']); ?></label>
                        <input type="hidden" name="label_qrr-date" value="<?php echo esc_attr($field['title']); ?>">
                        <vue-pickadate id="qrr-date" name="qrr-date" v-model="booking.date" :options="app_qrr.form"></vue-pickadate>
                        <div class="description"><?php echo esc_html($field['description']); ?></div>
                    </div>
                <?php break;

            case 'party': ?>
                    <div class="qrr-text qrr-party" :class="errorClass('qrr-party')">
                        <label for="qrr-party"><i class="fa fa-users"></i><?php echo esc_attr($field['title']); ?></label>
                        <input type="hidden" name="label_qrr-party" value="<?php echo esc_attr($field['title']); ?>">
                        <select v-if="step >= 1" type="text" name="qrr-party" id="qrr-party" value="" required="" aria-required="true" v-model="booking.party">
                            <?php

                            for( $i = $min_party; $i <= $max_party; $i++ ){
                                echo '<option value="'.esc_attr($i).'">'.esc_html($i).'</option>';
                            }
                            ?>
                        </select>
                        <div v-if="!(step >= 1)">-</div>
                        <div v-if="step >= 1" class="description"><?php echo esc_html($field['description']); ?></div>
                    </div>
                <?php break;

            case 'time':?>
                    <div class="qrr-text qrr-time" :class="errorClass('qrr-time')">
                        <label for="qrr-time"><i class="fa fa-clock-o"></i><?php echo esc_attr($field['title']); ?></label>
                        <input type="hidden" name="label_qrr-time" value="<?php echo esc_attr($field['title']); ?>">
                        <div v-if="step >= 2">
                            <span v-if="loading_hours"><img class="spinner" src="<?php echo esc_attr($loading_gif); ?>"></span>
                            <span v-if="!loading_hours">
                                <span v-if="hours_is_available">
                                    <select name="qrr-time" id="qrr-time" required="" aria-required="true" v-model="booking.hour">

                                        <optgroup v-for="group in hours_available" :label="group.label">
                                            <option v-for="option in group.list" :value="option.value">{{option.name}}</option>
                                        </optgroup>

                                    </select>
                                </span>
                                <span v-if="!hours_is_available">
                                    <div v-html="hours_message"></div>
                                </span>
                            </span>
                        </div>
                        <div v-if="!(step >= 2)">-</div>
                        <div v-if="step >= 2" class="description"><?php echo esc_html($field['description']); ?></div>
                    </div>
                <?php break;

            case 'name': ?>
                    <div class="qrr-text name" :class="errorClass('qrr-name')">
                        <label for="qrr-name"><?php echo esc_attr($field['title']); ?><?php echo $field['required'] ? ' *':''; ?></label>
                        <input type="hidden" name="label_qrr-name" value="<?php echo esc_attr($field['title']); ?>">
                        <input type="text" name="qrr-name" id="qrr-name" value="" required="" aria-required="true" v-model="booking.name">
                        <div class="qrr-error-valid" v-if="has('qrr-name')"><span v-text="validation['qrr-name']"></span></div>
                        <div class="description"><?php echo esc_html($field['description']); ?></div>
                    </div>
                <?php break;

            case 'email': ?>
                    <div class="qrr-text email" :class="errorClass('qrr-email')">
                        <label for="qrr-email"><?php echo esc_attr($field['title']); ?><?php echo $field['required'] ? ' *':''; ?></label>
                        <input type="hidden" name="label_qrr-email" value="<?php echo esc_attr($field['title']); ?>">
                        <input type="email" name="qrr-email" id="qrr-email" value="" required="" aria-required="true" v-model="booking.email">
                        <div class="qrr-error-valid" v-if="has('qrr-email')"><span v-text="validation['qrr-email']"></span></div>
                        <div class="description"><?php echo esc_html($field['description']); ?></div>
                    </div>
                <?php break;

            case 'phone': ?>
                    <div class="qrr-text phone" :class="errorClass('<?php echo esc_attr($field['id']); ?>')">
                        <?php echo_field_label($field); ?>
                        <input type="text" name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>" value="" <?php echo $field['required'] ? 'required="" aria-required="true"':''; ?>>
                        <?php echo_field_bottom($field); ?>
                    </div>
                <?php break;

            case 'text': ?>
                    <div class="qrr-text text" :class="errorClass('<?php echo esc_attr($field['id']); ?>')">
                        <?php echo_field_label($field); ?>
                        <input type="text" name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>" value="" <?php echo $field['required'] ? 'required="" aria-required="true"':''; ?>>
                        <?php echo_field_bottom($field); ?>
                    </div>
                <?php break;

            case 'textarea': ?>
                    <div class="qrr-text textarea" :class="errorClass('<?php echo esc_attr($field['id']); ?>')">
                        <?php echo_field_label($field); ?>
                        <textarea name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>" <?php echo $field['required'] ? 'required="" aria-required="true"':''; ?>></textarea>
                        <?php echo_field_bottom($field); ?>
                    </div>
                <?php break;

            case 'select': ?>
                    <div class="qrr-text select" :class="errorClass('<?php echo esc_attr($field['id']); ?>')">
                        <?php echo_field_label($field); ?>
                        <select name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>">
                            <?php foreach( $field['options'] as $option ) {
                                echo '<option value="'.$option['value'].'">'.esc_html($option['value']).'</option>';
                            } ?>
                        </select>
                        <?php echo_field_bottom($field); ?>
                    </div>
                <?php break;

            case 'checkbox': ?>
                    <div class="qrr-text checkbox" :class="errorClass('<?php echo esc_attr($field['id']); ?>')">
                        <?php echo_field_label($field); ?>
                        <?php foreach( $field['options'] as $option ) {
                            echo '<label><input type="checkbox" name="'.esc_attr($field['id']).'" id="'.esc_attr($field['id']).'" value="'.esc_attr($option['value']).'"> '.esc_html($option['value']).'</label>';
                        } ?>
                        <?php echo_field_bottom($field); ?>
                    </div>
                <?php break;

            case 'radio': ?>
                    <div class="qrr-text radio" :class="errorClass('<?php echo esc_attr($field['id']); ?>')">
                        <?php echo_field_label($field); ?>
                        <?php foreach( $field['options'] as $option ) {
                            echo '<label><input type="radio" name="'.esc_attr($field['id']).'" id="'.esc_attr($field['id']).'" value="'.esc_attr($option['value']).'"> '.esc_html($option['value']).'</label>';
                        } ?>
                        <?php echo_field_bottom($field); ?>
                    </div>
                <?php break;

            default:
                break;

            }
        };

        do_action('qrr_form_field_after', $field, $qrr_post_id);

        if ($group_opened) {
            echo '</fieldset>';
        }

        do_action('qrr_after_form_inside', $qrr_post_id);

        ?>

        <div v-if="step >= 3 && !loading_hours" class="qrr-submit">
            <div v-if="!submitting">
                <button type="submit"><?php _ex('Request Booking', 'from', 'qrr'); ?></button>
            </div>
            <div v-if="submitting">
                <img class="spinner" src="<?php echo esc_attr($loading_gif); ?>">
                <?php _e('Sending request ....', 'qrr'); ?>
            </div>
        </div>

        <?php do_action('qrr_after_form_submit_button', $qrr_post_id); ?>

    </form>

    <div class="form-submitted" v-if="submitted" v-html="submitted_success_msg"></div>

    <div class="form-error" v-html="submitted_error_msg"></div>

</div>

<?php do_action('qrr_after_form', $qrr_post_id); ?>

<template id="vue-pickadate">
    <input type="text" v-model="date.text" :id="id" :name="name" required="" aria-required="true">
</template>


<template id="vue-select">
    <select :id="id" :name="name" class="" v-model="selected">
        <option v-for="item in list" :value="item.value">{{item.name}}</option>
    </select>
</template>


