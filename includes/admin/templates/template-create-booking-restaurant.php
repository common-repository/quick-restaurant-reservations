<?php

if (! defined('ABSPATH') ) { exit; // Exit if accessed directly
}

$restaurant_id = intval(sanitize_text_field($_GET['restaurant_id']));
$rest = new QRR_Restaurant($restaurant_id);

$booking_general = get_post_meta($restaurant_id, 'qrr_booking_general', true);
$min_party = isset($booking_general['min_party']) ? intval($booking_general['min_party']) : 1;
$max_party = isset($booking_general['max_party']) ? intval($booking_general['max_party']) : 50;
//$list_hours = $rest->get_booking_hours_all();


wp_localize_script(
    'admin-create-qrr_booking', 'app_qrr', array(
    'form' =>  $rest->get_form_data(),
    'nonce'       => wp_create_nonce('qrr_form')
    )
);

wp_enqueue_script('admin-create-qrr_booking');


?>

<div class="wrap_qrr" id="qrr-create-booking">

    <form method="POST" @submit.prevent="onSubmit">

        <h2><?php _e('Create Booking for Restaurant:', 'qrr'); ?> <?php echo esc_html(get_the_title($restaurant_id)); ?></h2>

        <div>
            <label>
                <input type="checkbox" v-model="restrict_conditions">
                <?php _ex('Restrict to schedules capacity.', 'Booking create', 'qrr'); ?>
            </label>
        </div>

        <div class="qrr-spinner">
            <img v-if="loading" src="<?php echo QRR_PLUGIN_URL.'assets/img/spinner_2.gif'; ?>">
        </div>

        <table>

            <tr>
                <td><?php _ex('Date*', 'Booking create admin', 'qrr'); ?></td>
                <td>
                    <div class="qrr-date">
                    <vue-pickadate id="qrr-date" name="qrr-date" v-model="booking.date" :options="app_qrr.form"></vue-pickadate>
                    </div>
                </td>
            </tr>

            <tr>
                <td><?php _ex('Party*', 'Booking create admin', 'qrr'); ?></td>
                <td  v-if="restrict_conditions">
                    <select name="qrr-party" id="qrr-party" value="" required="" aria-required="true" v-model="booking.party">
                        <?php
                        for( $i = $min_party; $i <= $max_party; $i++ ){
                            echo '<option value="'.esc_attr($i).'">'.esc_html($i).'</option>';
                        }
                        ?>
                    </select>
                </td>
                <td v-if="!restrict_conditions">
                    <input type="text" name="qrr-party" id="qrr-party" value="" required="" aria-required="true" v-model="booking.party">
                </td>
            </tr>

            <tr>
                <td>
                    <span v-if="restrict_conditions"><?php _ex('Time*', 'Booking create admin', 'qrr'); ?></span>
                    <span v-if="!restrict_conditions"><?php _ex('Schedule* and Time*', 'Booking create admin', 'qrr'); ?></span>
                </td>
                <td v-if="restrict_conditions">
                    <select name="qrr-time" id="qrr-time" required="" aria-required="true" v-model="booking.hour">
                        <optgroup v-for="group in hours_available" :label="group.label">
                            <option v-for="option in group.list" :value="option.value">{{option.name}}</option>
                        </optgroup>
                    </select>
                    <small v-text="hours_message"></small>
                </td>
                <td v-if="!restrict_conditions">
                    <select v-model="booking.hour_schedule">
                        <option v-for="item in hours_schedules" :value="item.index">{{ item.label }}</option>
                    </select>
                    <input type="text" aria-required="true" v-model="booking.hour_schedule_hour" placeholder="00:00">
                    <input type="hidden" name="qrr-time" id="qrr-time" :value="booking.hour_schedule + '_' + booking.hour_schedule_hour">
                </td>
            </tr>

            <?php if (QRR_Active('capacity')) { ?>
            <tr>
                <td><?php _ex('Table Name', 'Booking create admin', 'qrr'); ?></td>
                <td><input type="text" name="qrr-table-name" id="qrr-table-name"></td>
            </tr>
            <?php } ?>

            <tr>
                <td><?php _ex('Name*', 'Booking create admin', 'qrr'); ?></td>
                <td>
                    <input type="text" name="qrr-name" v-model="booking.name" required="" aria-required="true">

                    <?php
                    $load_list_clients = QRR()->settings->get('qrr_booking_new_use_clients');
                    if ($load_list_clients) { ?>

                        <i class="fa fa-arrow-left"></i>
                        <select id="fill_from_customer">
		                    <?php
		                    echo '<option value="">'.__('Fill from customer', 'qrr').'</option>';
		                    $clients = QRR_Clients::get_list_clients();
		                    if (!empty($clients) ) {
			                    foreach($clients as $client) {
				                    echo '<option value="'.esc_attr($client['email']).'" data-name="'.esc_attr($client['name']).'" data-phone="'.esc_attr($client['phone']).'">'.esc_html($client['email']).' | '.esc_html($client['name']).'</option>';
			                    }
		                    }
		                    ?>
                        </select>
                    <?php } ?>

                </td>
            </tr>

            <tr>
                <td><?php _ex('Email*', 'Booking create admin', 'qrr'); ?></td>
                <td>
                    <input type="email" name="qrr-email" v-model="booking.email" required="" aria-required="true">
                </td>
            </tr>

            <tr>
                <?php $label = _x('Phone*', 'Booking create admin', 'qrr'); ?>
                <td><?php echo esc_html($label); ?></td>
                <td>
                    <div class="qrr-text phone">
                        <input type="hidden" name="label_qrr-phone" value="<?php echo esc_attr($label); ?>">
                        <input type="hidden" name="type_qrr-phone" value="phone">
                        <input type="text" name="qrr-phone" id="qrr-phone" v-model="booking.phone" required="" aria-required="true">
                    </div>
                </td>
            </tr>

            <tr>
                <?php $label = _x('Message', 'Booking create admin', 'qrr'); ?>
                <td><?php echo esc_html($label); ?></td>
                <td>
                    <div class="qrr-text textarea">
                        <input type="hidden" name="label_qrr-message" value="<?php echo esc_attr($label); ?>">
                        <input type="hidden" name="type_qrr-message" value="textarea">
                        <textarea name="qrr-message" id="qrr-message" v-model="booking.message"></textarea>
                    </div>
                </td>
            </tr>

            <tr>
                <td><?php _ex('Status*', 'Booking create admin', 'qrr'); ?></td>
                <td>
                    <select name="qrr-status" v-model="booking.status" required="" aria-required="true">
                        <?php
                        $list_status = QRR_Booking_Edit::get_list_status();
                        foreach($list_status as $key => $item){
                            echo '<option value="'.esc_attr($key).'">'.esc_html($item['label_text']).'</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td><?php _ex('Send email to customer', 'Booking create admin', 'qrr'); ?></td>
                <td>
                    <input type="checkbox" name="qrr-send-email">
                </td>
            </tr>

        </table>

        <br>

        <div v-if="!loading && !submitting">
            <button class="button button-primary" type="submit"><?php _ex('Confirm', 'from', 'qrr'); ?></button>
        </div>

        <div class="form-submitted" v-if="submitted" v-html="submitted_success_msg"></div>

        <div class="form-error" v-html="submitted_error_msg" style="color:red;"></div>

    </form>

</div>



<template id="vue-pickadate">
    <input type="text" v-model="date.text" :id="id" :name="name" required="" aria-required="true">
</template>


<template id="vue-select">
    <select :id="id" :name="name" class="" v-model="selected">
        <option v-for="item in list" :value="item.value">{{item.name}}</option>
    </select>
</template>



<style>
    .qrr-spinner { height: 30px; }
    .qrr-spinner img { height: 25px; }
    #qrr-create-booking td { vertical-align: top; }
    #qrr-create-booking input[type=text],
    #qrr-create-booking input[type=email]{ min-width: 200px; background: white; }
    #qrr-create-booking select { min-width: 200px; }
    #qrr-create-booking textarea { min-width: 200px; height: 120px;}
    #qrr-create-booking .form-error { margin: 10px; }
</style>
