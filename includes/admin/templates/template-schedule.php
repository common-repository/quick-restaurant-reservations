<?php

wp_enqueue_script('admin-booking-schedule');

require 'template-components.php';

?>

<div id="booking-schedule">

    <div class="qrr-right-align">
        <a href="#" class="button btn-help" @click.prevent="help = !help"><?php _e('Help', 'qrr'); ?></a>
    </div>

    <div v-if="help">
        <p>
            <?php _e(
                'Schedule Rules are applied from top to bottom.<br>
            Define general rules at top, then specific rules at the bottom.<br>
            Second rule will override first rule, etc..<br>
            <small>For example you can create a general rule for all days,<br>
            then a second rule for weekends or specific dates.</small>', 'qrr'
            ); ?>
        </p>
    </div>

    <draggable v-model="schedules" :options="{handle:'.schedule-drag'}" @start="dragstart" @end="dragend">

        <div v-for="schedule in schedules" class="qrr-schedule">

            <div class="schedule-wrap" :class="{'status-active': schedule.active, 'status-inactive': !schedule.active, 'status-opened':schedule.opened,'status-closed':!schedule.opened, 'status-visible':schedule.visible, 'status-invisible':!schedule.visible, 'test_applied': schedule.test_applied}">

                <div class="schedule-header">

                    <div class="schedule-col schedule-col1">
                        <div class="field-inline schedule-drag">
                            <i class="fa fa-bars"></i>
                        </div>
                        <div class="field-inline schedule-active">
                            <vue-yesno v-model="schedule.active" title_yes="<?php _ex('Yes', 'settings', 'qrr'); ?>" title_no="<?php _ex('No', 'settings', 'qrr'); ?>"></vue-yesno>
                        </div>
                    </div>

                    <div class="schedule-col schedule-col2">
                        <div class="field-inline schedule-name">
                            <span v-text="schedule.name"></span>
                        </div>
                        <div class="field-inline schedule-open">
                            <span v-for="day in schedule.days">
                                <i class="fa" :class="{'fa-square-o':!day.active, 'fa-check-square-o':day.active}"></i>
                            </span>
                        </div>
                        <div class="field-inline schedule-dates">
                            <span v-if="schedule.alldates"><?php _ex('All Dates', 'settings', 'qrr'); ?></span>
                            <span v-if="!schedule.alldates">{{schedule.dates.from}} - {{schedule.dates.to}}</span>
                            &nbsp;&nbsp;
                            <span v-if="(schedule.opened && schedule.alltime && schedule.time_type == 'interval') || !schedule.opened"><?php _ex('All Day', 'settings', 'qrr'); ?></span>
                            <span v-if="schedule.opened && schedule.time_type == 'specific'"><?php _ex('Specific hours', 'settings', 'qrr'); ?></span>
                            <span v-if="!schedule.alltime && schedule.opened && schedule.time_type!='specific'">{{schedule.time.from}} - {{schedule.time.to}}</span>
                        </div>
                        <div class="field-inline schedule-capacity">
                            <span v-text="cleanTitle(schedule.capacity)"></span>
                        </div>
                        <div class="field-inline schedule-manual">
                            <span v-text="cleanTitle(schedule.notifications.confirmation_type)"></span>
                        </div>
                    </div>

                    <div class="schedule-col schedule-col3">
                        <div class="field-inline schedule-actions">
                            <a class="qrr-action schedule-edit" href="#" @click.prevent="openPopup(schedule)"><i class="fa fa-pencil"></i></a>
                            <a class="qrr-action schedule-remove" href="#" @click.prevent="removeSchedule(schedule)"><i class="fa fa-times"></i></a>
                        </div>
                    </div>

                </div>


            </div>

        </div>

    </draggable>


    <a class="schedule-add-rule button" href="#" @click.prevent="addSchedule"><?php _ex('Add Schedule', 'settings', 'qrr'); ?></a>



    <!-- CHECK RULE -->
    <div class="qrr-check-rules">
        <p><?php _e('You can check the rules here, please insert some date and press the button.', 'qrr'); ?></p>
        <vue-datepicker v-model="test_date.day" format="yy/mm/dd"></vue-datepicker>
        <vue-hourpicker v-model="test_date.hour"></vue-hourpicker>
        <vue-minutespicker v-model="test_date.minutes"></vue-minutespicker>
        <a class="button" href="#" @click.prevent="test_the_date"><?php _e('CHECK', 'qrr'); ?></a>
        <br><span class="result" v-html="test_result" ></span>
    </div>




    <!-- POPUP SCHEDULE -->
    <div class="qrr-popup" :class="{'open':schedule_popup_open}" v-if="null != schedule_selected">

        <div class="popup-inner-wrap schedule-wrap" :class="{'status-opened':schedule_selected.opened,'status-closed':!schedule_selected.opened}">
            <div class="popup-inner">

                <a href="#" class="popup-close-icon" @click.prevent="closePopup"><i class="fa fa-times"></i></a>

                <div class="popup-title">
                    <?php _ex('name', 'settings', 'qrr'); ?> <input type="text" v-model="schedule_selected.name">
                </div>

                <div class="qrr-tabs">
                    <a href="#dates" @click.prevent="schedule_tab = 'dates'" :class="{'active': schedule_tab=='dates'}"><?php _e('Dates', 'qrr'); ?></a>
                    <a href="#time" v-if="schedule_selected.opened" @click.prevent="schedule_tab = 'time'" :class="{'active': schedule_tab=='time'}"><?php _e('Time', 'qrr'); ?></a>
                    <a href="#capacity" v-if="schedule_selected.opened" @click.prevent="schedule_tab = 'capacity'" :class="{'active': schedule_tab=='capacity'}"><?php _e('Capacity', 'qrr'); ?></a>
                    <a href="#notifications" v-if="schedule_selected.opened" @click.prevent="schedule_tab = 'notifications'" :class="{'active': schedule_tab=='notifications'}"><?php _e('Notifications', 'qrr'); ?></a>
                    <a href="#late_bookings" v-if="schedule_selected.opened" @click.prevent="schedule_tab = 'late_bookings'" :class="{'active': schedule_tab=='late_bookings'}"><?php _e('Late bookings', 'qrr'); ?></a>

                    <a href="#time_closed" v-if="!schedule_selected.opened" @click.prevent="schedule_selected.time_type = 'interval'; schedule_tab = 'time_closed'" :class="{'active': schedule_tab=='time_closed'}"><?php _e('Time', 'qrr'); ?></a>

                    <?php if (QRR_Active('payments') ) { ?>
                    <a href="#payment" v-if="schedule_selected.opened" @click.prevent="schedule_tab = 'payment'" :class="{'active': schedule_tab=='payment'}"><?php _e('Payment', 'qrr'); ?></a>
                    <?php } ?>

                </div>


                <div class="qrr-tab-content popup-tab-body">
                    <div class="tab-content-wrap">

                        <!-- DATES -->
                        <div v-if="schedule_tab == 'dates'" class="qrr-tab-inside">
                            <div class="qrr-field field-status">
                                <vue-yesno v-model="schedule_selected.opened" title_yes="<?php _ex('Opened', 'settings', 'qrr'); ?>" title_no="<?php _ex('Closed', 'settings', 'qrr'); ?>"></vue-yesno>
                            </div>
                            <div class="qrr-field">
                                <vue-weekdays v-model="schedule_selected.days" :names="days"></vue-weekdays>
                            </div>
                            <div class="qrr-field field-dates" :class="{'alldates': schedule_selected.alldates}">
                                <label class="dates-alldates">
                                    <input type="checkbox" v-model="schedule_selected.alldates"> <?php _ex('All Dates', 'settings', 'qrr'); ?>
                                </label>
                                <vue-datepicker-fromto v-model="schedule_selected.dates" format="yy/mm/dd"></vue-datepicker-fromto>
                            </div>
                        </div>

                        <!-- TIME CLOSED -->
                        <div v-if="schedule_tab == 'time_closed'" class="qrr-tab-inside">
                            <div class="qrr-field field-time" :class="{'alltime': schedule_selected.alltime}">
                                <label class="time-alltime">
                                    <input type="checkbox" v-model="schedule_selected.alltime"> <?php _ex('All Day', 'settings', 'qrr'); ?>
                                </label>
                                <vue-timepicker v-model="schedule_selected.time"></vue-timepicker>
                            </div>
                        </div>

                        <!-- TIME -->
                        <div v-if="schedule_tab == 'time'" class="qrr-tab-inside">

                            <select v-model="schedule_selected.time_type">
                                <option value="interval"><?php _ex('Use Time Interval', 'Time type', 'qrr'); ?></option>
                                <option value="specific"><?php _ex('Use Specific Hours', 'Time type', 'qrr'); ?></option>
                            </select>

                            <br><br>

                            <div v-if="schedule_selected.time_type == 'interval'">
                                <div class="qrr-field field-time" :class="{'alltime': schedule_selected.alltime}">
                                    <label class="time-alltime">
                                        <input type="checkbox" v-model="schedule_selected.alltime"> <?php _ex('All Day', 'settings', 'qrr'); ?>
                                    </label>
                                    <vue-timepicker v-model="schedule_selected.time"></vue-timepicker>
                                </div>
                                <div class="qrr-field" v-if="schedule_selected.opened">
                                    <label><?php _ex('Allow Booking every ', 'settings', 'qrr'); ?></label>
                                    <select v-model="schedule_selected.time_interval">
                                        <option v-for="time in time_intervals" :value="time.value">
                                            {{time.name}}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div v-if="schedule_selected.time_type == 'specific'">
                                <vue-hourpicker v-model="time_specific.hour"></vue-hourpicker>
                                <vue-minutespicker v-model="time_specific.minutes"></vue-minutespicker>
                                <a class="button" href="#" @click.prevent="addTimeSpecific"><?php _e('ADD', 'qrr'); ?></a>
                                <div class="qrr-list-hours">
                                    <span class="qrr-hour" v-for="hour in sortedHours">
                                        <span @click.prevent="$root.removeTimeSpecific(hour)">{{hour}}</span>
                                    </span>
                                </div>

                            </div>

                        </div>


                        <!-- CAPACITY -->
                        <div v-if="schedule_tab == 'capacity'" class="qrr-tab-inside">
                            <div class="qrr-field">
                                <label><?php _ex('Limit Capacity based on ', 'settings', 'qrr'); ?></label>
                                <select v-model="schedule_selected.capacity">
                                    <?php
                                        $options = array(
                                            'capacity_unlimited' => _x('Unlimited', 'settings', 'qrr')
                                        );
                                        $options = apply_filters('qrr_capacity_list', $options);
                                        foreach( $options as $value => $title ) {
                                            echo '<option value="'.esc_attr($value).'">'.esc_html($title).'</option>';
                                        }
                                        ?>
                                </select>
                                <?php
                                if (!QRR_Active('capacity') ) {
                                    include QRR_PLUGIN_DIR.'includes/admin/template-addons/addon-capacity.php';
                                }
                                ?>
                            </div>
                            <?php do_action('qrr_popup_schedule_capacity'); ?>
                        </div>


                        <!-- NOTIFICATIONS -->
                        <div v-if="schedule_tab == 'notifications'" class="qrr-tab-inside">
                            <?php
                            if (!QRR_Active('capacity') ) {
                                include QRR_PLUGIN_DIR.'includes/admin/template-addons/addon-capacity.php';
                            }
                            ?>
                            <?php do_action('qrr_popup_schedule_notifications'); ?>
                        </div>

                        <!-- CLOSE BOOKINGS -->
                        <div v-if="schedule_tab == 'late_bookings'" class="qrr-tab-inside">
	                        <?php
	                        if (!QRR_Active('capacity') ) {
		                        include QRR_PLUGIN_DIR.'includes/admin/template-addons/addon-capacity.php';
	                        }
	                        ?>
	                        <?php do_action('qrr_popup_schedule_late_bookings'); ?>
                        </div>

                        <!-- PAYMENT -->
                        <div v-if="schedule_tab == 'payment'" class="qrr-tab-inside">
                            PAYMENT STRIPE
                        </div>


                    </div>
                </div>


            </div>
        </div>

    </div>
    <!-- END POPUP -->




</div>





