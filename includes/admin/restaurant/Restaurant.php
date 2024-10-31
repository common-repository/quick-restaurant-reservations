<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


class QRR_Restaurant
{

    private $restaurant_id;

    public function __construct( $restaurant_id )
    {

        $this->restaurant_id = $restaurant_id;

    }

    // Data passed to the form in javascript
    public function get_form_data()
    {

        return array(
            'format' => $this->get_form_date_form(),
            'post_id' => $this->restaurant_id,
            'allow_past' => false,
            'early_bookings' => $this->get_early_booking(),
            'disable_dates' => $this->get_dates_disabled_formated_for_script(),
            'first_day' => $this->get_first_day_of_week()
        );
    }


    public function get_dates_available( $formated = false )
    {

        $value_general = get_post_meta($this->restaurant_id, 'qrr_booking_general', true);
        //echo '<pre>$value_general '; print_r( $value_general ); echo '</pre>';
        $early = isset($value_general['early']) ? $value_general['early'] : 0;

        // From current day to early days
        $dates = array();
        $now = current_time('timestamp', 0); // Local
        $dates[] =  strtotime(date('Y-m-d', $now).' 00:00:00');

        if ($early > 0 ) {
            for($i = 1 ; $i <= $early; $i++){
                $dates[] = intval($dates[0]) + $i * 86400;
            }
        }

        // Filter dates
        $dates = $this->filter_days_based_schedules($dates, $this->restaurant_id);
        $dates = $this->filter_days_based_bookings($dates, $this->restaurant_id);
        $dates = $this->filter_days_based_late_booking($dates, $this->restaurant_id);
        $dates = apply_filters('qrr_filter_available_dates', $dates, $this->restaurant_id);

        // Formatted dates
        if ($formated && !empty($dates) ) {
            $dates_formated = array();
            foreach( $dates as $date ) {
                $dates_formated[] = date('Y-m-d', $date);
            }
            return $dates_formated;
        }

        return $dates;
    }


    // Return array of timestamps or Y-m-d
    public function get_dates_disabled( $formated = false )
    {

        // Create array of all dates based on early bookings
        $early = $this->get_early_booking();
        $all_dates = array();
        for( $i = 0; $i <= $early; $i++ ) {
            $time_local = current_time('timestamp', 0) + $i * 86400;
            $all_dates[] = date('Y-m-d', $time_local); // Formated to be able to compare
        }

        //echo '<pre>ALL DATES: '; print_r( $all_dates ); echo '</pre>';

        // Process the array to check if available in the schedules
        // in not available add to the list
        $dates_disabled = array();

        // Compare dates formatted
        if ($all_dates) {

            $dates_available = $this->get_dates_available(true);
            //echo '<pre>AVAILABLE: '; print_r( $dates_available ); echo '</pre>';

            foreach( $all_dates as $date ) {
                if (!in_array($date, $dates_available) ) {
                    $dates_disabled[] = $date;
                }
            }
        }

        //echo '<pre>DISABLED: '; print_r( $dates_disabled ); echo '</pre>';

        if (!$formated && !empty($dates_disabled) ) {
            $dates_disabled_time = array();
            foreach( $dates_disabled as $date ) {
                $dates_disabled_time[] = strtotime($date.' 00:00:00');
            }
            return $dates_disabled_time;
        }


        return $dates_disabled;

    }

    public function get_dates_disabled_formated_for_script()
    {

        $dates = $this->get_dates_disabled();
        $dates_formatted = array();

        if ($dates) {
            foreach( $dates as $date ) {
                $dates_formatted[] = array(
                    date('Y', $date),
                    intval(date('n', $date)) - 1, // January starts with 0
                    date('j', $date)
                );
            }
        }

        return $dates_formatted;

        /*return array(
            array(2017,8,15),
            array(2017,8,16)// January = 0
        );*/

    }


    // Filter based on schedules rules
    //----------------------------------
    // Used to find the days availables from an array of timestamps
    public function filter_days_based_schedules( $dates, $restaurant_id = null )
    {

        //ray("filter_days_based_schedules");
        //ray($dates);

        if ($restaurant_id == null) {
            $restaurant_id = $this->restaurant_id;
        }

        if (empty($dates)) {
            return $dates;
        }

        if ($this->restaurant_id != $restaurant_id) {
            return $dates;
        }

        // Filter schedules
        $schedules = get_post_meta($restaurant_id, 'qrr_booking_schedule', true);

        //ray(json_decode($schedules));

        if (empty($schedules)) {
            return $dates;
        }

        $schedules =  json_decode($schedules, true);

        if (!is_array($schedules)) {
            return $dates;
        }

        if (empty($schedules)) {
            return $dates;
        }



        // Process each schedule
        $dates_final = array();
        foreach( $dates as $date ) {
            if ($this->is_date_available_for_schedules($date, $schedules) ) {
                $dates_final[] = $date;
            } else {

                //$date_str = date('Y', $date).'-'. (intval(date('n', $date)) - 1) .'-'.date('j', $date);
                //ray('date is not available for schedules: '.$date_str);
            }
        }

        return $dates_final;
    }

    public function is_date_available_for_schedules( $date, $schedules )
    {
        $list = $this->get_schedules_coincidences($date, $schedules, false);

        // Check each rule from the last to the beginning and apply first coincidence
        /*for ($i = count($list) - 1; $i >= 0; $i-- ) {
            if( $list[$i] && $schedules[$i]['active'] ) {
                return $schedules[$i]['opened'];
            }
        }
        return false;*/


        // Check OPEN and CLOSE FULL DAY schedules
        $found_active_opened_schedule = false;
        $found_active_closed_schedule_full_day = false;

        for ($i = count($list) - 1; $i >= 0; $i-- ) {
            if($list[$i] && $schedules[$i]['active'] ) {

                // Is an OPEN restaurant Schedule
                if ($schedules[$i]['opened']) {
                    $found_active_opened_schedule = true;
                }

                // Is a CLOSE restaurant schedule all day
                else if (!$schedules[$i]['opened']) {
                    if ($schedules[$i]['alltime']) {
                        $found_active_closed_schedule_full_day = true;
                    }
                }
            }
        }

        if ($found_active_closed_schedule_full_day) { return false;
        }
        return $found_active_opened_schedule;
    }




    // Get the list of schedules that has coincidence with the date
    // returns array list with true/false
    public function get_schedules_coincidences( $date, $schedules, $check_hour_too = false )
    {
        $list = array();
        foreach( $schedules as $schedule ) {
            $list[] = $this->is_date_coincidence_for_schedule($date, $schedule, $check_hour_too)
                      && $this->is_schedule_late_booking_allowed($date, $schedule);
        }
        return $list;
    }

    // date in unix format (seconds)
    // Check if a date fits the schedule rules
    // returns true or false
    public function is_date_coincidence_for_schedule( $date, $schedule, $check_hour_too = false )
    {

        $coincidence_week_day = false;
        $coincidence_date = false;

        // Check day of week
        $dayofweek = date('w', $date); // 0=sunday, 1=monday...
        $dayofweek = $dayofweek -1;
        if ($dayofweek == -1) { $dayofweek = 6; // 0=monday to 6=sunday
        }

        if ($schedule['days'][$dayofweek]['active']) {
            $coincidence_week_day = true;
        }

        // Check date
        if ($schedule['alldates']) {
            $coincidence_date = true;
        } else {
            $date_from = strtotime($schedule['dates']['from']);
            $date_to = strtotime($schedule['dates']['to']) + 86399; // End of the day, in case date from and to is the same
            if ($date >= $date_from && $date <= $date_to) {
                $coincidence_date = true;
            } else {
                $coincidence_date = false;
            }
        }

        $coincidence_total = $coincidence_week_day && $coincidence_date;
        //return $coincidence_total;

        // If not coincidence return, no need to follow checking
        // If no check hour then just return
        // If schedule is closed then no need to check hours because by default is all day
        // If all day return
        if (!$coincidence_total) {
            return $coincidence_total;
        }

        if (!$check_hour_too) {
            return $coincidence_total;
        }

        if (!$schedule['opened']) {
            return $coincidence_total;
        }



        if ($schedule['alltime']) {
            return $coincidence_total;
        }



        // Check with hour included
        if ($schedule['alldates']) {
            $schedule['dates']['from'] = date('Y/m/d', $date); // Same day
            $schedule['dates']['to'] = date('Y/m/d', $date); // Same day
        }
        $dt_from = $schedule['dates']['from'].' '.$schedule['time']['from'].':00';
        $dt_to = $schedule['dates']['to'].' '.$schedule['time']['to'].':00';
        $datetime_from = strtotime($dt_from);
        $datetime_to = strtotime($dt_to);

        if ($date >= $datetime_from && $date <= $datetime_to) {
            $coincidence_hour = true;
        } else {
            $coincidence_hour = false;
        }

        return $coincidence_hour;
    }


	// Check the setting for the schedule late booking overwriting the general option
	public function is_schedule_late_booking_allowed($date, $schedule) {

		return apply_filters('qrr_filter_is_schedule_late_booking_allowed', true, $date, $schedule);


	}

    // Filter based on current bookings, if day is full then not available
    //----------------------------------

    public function filter_days_based_bookings( $dates, $restaurant_id )
    {

        if (empty($dates)) {
            return $dates;
        }

        if ($this->restaurant_id != $restaurant_id) {
            return $dates;
        }

        // @TODO filter available dates based on bookings confirmed


        return $dates;
    }


    // Filter based on late booking available (1d, 2d,...)
    //----------------------------------
    public function filter_days_based_late_booking( $dates, $restaurant_id )
    {

        if (empty($dates)) { return $dates;
        }

        $rm = new QRR_Restaurant_Model($restaurant_id);
        $late = $rm->get_late_booking();

        if (preg_match('#(.+)d#', $late, $matches) ) {
            $late_days = intval($matches[1]);
        } else {
            return $dates;
        }

        $now_time = current_time('timestamp');
        $now_0 = date('Y', $now_time)*365 + date('z', $now_time);

        $new_list_dates = array();

        foreach($dates as $day_time){
            $day_0 = date('Y', $day_time)*365 + date('z', $day_time);
            $diff_days = ($day_0 - $now_0);

            if ($diff_days >= $late_days) {
                $new_list_dates[] = $day_time;
            }
        }

        return $new_list_dates;
    }


    // Access to data
    //----------------------------------

    public function get_form_date_form()
    {
        $options_general = get_post_meta($this->restaurant_id, 'qrr_booking_general', true);
        return ( isset($options_general['date_format']) && !empty($options_general['date_format']) ) ? $options_general['date_format'] : 'mmmm d,yyyy';
    }

    public function get_early_booking()
    {
        $options_general = get_post_meta($this->restaurant_id, 'qrr_booking_general', true);
        return isset($options_general['early']) ? intval($options_general['early']) : 0;
    }

    public function get_first_day_of_week()
    {
        $options_general = get_post_meta($this->restaurant_id, 'qrr_booking_general', true);
        return intval($options_general['first_day']);
    }


    // Access to data
    //----------------------------------

    public function get_schedules()
    {
        $schedules = get_post_meta($this->restaurant_id, 'qrr_booking_schedule', true);
        if (empty($schedules)) {
            return array();
        }
        $schedules =  json_decode($schedules, true);
        return $schedules;
    }

    // Only active and open schedules
    public function get_schedules_opened()
    {
        $schedules = $this->get_schedules();
        if (!$schedules) { return false;
        }

        $list = array();
        $index = 0;
        foreach($schedules as $schedule){
            if ($schedule['active'] && $schedule['opened']) {
                $list[] = array(
                    'value' => $index,
                    'title'  => (!empty($schedule['name']) ? $schedule['name'] : _x('Schedule without name', 'Create booking', 'qrr'))
                );
            }
            $index++;
        }

        return $list;
    }

    // Used when creating abooking from admin
    public function get_booking_hours_all($date)
    {

        // 1.Get list schedules for the date than can be applied
        // 2.Extract hours with intervals for each schedule

        $schedules = $this->get_schedules();
        if (empty($schedules)) { return array();
        }

        $list_coincidences = $this->get_schedules_coincidences($date, $schedules);

        $list_hours = array();
        for( $i = 0; $i < count($list_coincidences); $i++ ) {
            if($list_coincidences[$i]  && $schedules[$i]['active'] && $schedules[$i]['opened'] ) {
                $list_hours[] = array(
                    'schedule_index' => $i, // Starts at 0
                    'hours' => $this->get_hours_for_schedule($schedules[$i])
                );
            }
        }

        if (empty($list_hours)) { return array();
        }

        $final_list = $this->convert_list_hours_to_group_options($schedules, $list_hours);
        return $final_list;

    }

    // Used in ajax front fromt to Fetch available hours
    // date = unix time
    public function get_booking_hours( $date, $party )
    {

        $date_str = date('Y', $date).'-'. (intval(date('n', $date)) - 1) .'-'.date('j', $date);
        // ray("get_booking_hours for date: " . $date_str . ' and party '. $party);

        // 1.Get list schedules for the date than can be applied
        // 2.Extract hours with intervals for each schedule
        // 3.For each schedule check if that hour is available based on the party and bookings confirmed
        // 4.Merge all hours in a format with the schedule number so the booking knows which schedule to use


        // 1.Get list schedules for the date than can be applied
        //----------------------------------------------------
        $schedules = $this->get_schedules();
        if (empty($schedules)) { return array(); }

        // Returns array with True / False for every schedule
        $list_coincidences = $this->get_schedules_coincidences($date, $schedules);
        //ray("list_coincidences");
        //ray($schedules);
        //ray($list_coincidences);



        // 2.Extract hours with intervals for each schedule
        //----------------------------------------------------
        $list_hours = array();
        for( $i = 0; $i < count($list_coincidences); $i++ ) {

            // If there is a concidence in the Date and is Active and is Opened
            if($list_coincidences[$i]  && $schedules[$i]['active'] && $schedules[$i]['opened'] ) {
                $list_hours[] = array(
                    'schedule_index' => $i, // Starts at 0
                    'hours' => $this->get_hours_for_schedule($schedules[$i])
                );
            }

        }
        //ray($list_hours);

        // 2b. Remove hours for schedules CLOSED for specific range of time
        for( $i = 0; $i < count($list_coincidences); $i++ ) {
            if($list_coincidences[$i]  && $schedules[$i]['active'] && !$schedules[$i]['opened'] && !$schedules[$i]['alltime']) {

                $hours_closed = $this->get_hours_for_schedule($schedules[$i]);

                //ray("HOURS CLOSED");
                //ray($hours_closed);

                // Remove hours_closed
                foreach($list_hours as &$item){
                    // $available_hours = $item['hours'] - $hours_closed
                    $available_hours = [];
                    foreach($item['hours'] as $hour){
                        if (!in_array($hour, $hours_closed)) {
                            $available_hours[] = $hour;
                        }
                    }
                    $item['hours'] = $available_hours;
                }
            }
        }

        if (empty($list_hours)) { return array(); }



        // Filter based on late hour
        //----------------------------------------------------
        $list_hours = $this->filter_late_booking($date, $list_hours);

		//ray($list_hours);

        $list_hours_filtered = $list_hours;
        if(QRR_Active('capacity') ) {
            $day = date('Y-m-d', $date);

            // Filter based on seats available
            // Check only the hour not all the duration of the booking
            // So skip this step and use next filter only, is enough
            //----------------------------------------------------
            //$list_hours_filtered = $this->filter_available_seats($day, $list_hours_filtered, $party);

            // Better check all the duration with the real booking request function
            // Filter based on can book (the check when booking request)
            //----------------------------------------------------
            $list_hours_filtered = $this->filter_can_book($day, $list_hours_filtered, $party);
        }



        // Convert to grouped hours
        //----------------------------------------------------
        $final_list = $this->convert_list_hours_to_group_options($schedules, $list_hours_filtered);


        // Modify values to format 24h/12h
        //----------------------------------------------------
        $rm = new QRR_Restaurant_Model($this->restaurant_id);
        $hour_format = $rm->get_hour_format();

        foreach($final_list as &$item){
            foreach($item['list'] as &$hour){

                if ($hour_format == '12h') {
                    $hour['name'] = date('g:i A', strtotime($hour['name']));
                }

            }
        }

        // Last filter
        $final_list = apply_filters('qrr_front_form_hours', $final_list);
        return $final_list;



        // 3.
        // filter hours based on bookings confirmed
        // filter schedule based on party, may have total seats
        //$bookings = new QRR_Bookings();
        //$available_seats = $bookings->get_seats_available( $this->restaurant_id, $date );// this result is randomize, do correctly
        //echo '<pre>'; print_r( $available_seats ); echo '</pre>';


        /*
        // Remove those hours that have no party available
        $party = intval($party);
        $list_hours_filtered = array();
        foreach( $list_hours as $hours ){

            if (empty($hours['hours'])) continue;

            $hours_final = array();
            foreach( $hours['hours'] as $hour ) {

                // Check this hour
                if ($available_seats[$hour] >= $party) {
                    $hours_final[] = $hour;
                }
            }

            $list_hours_filtered[] = array(
                'schedule_index' => $hours['schedule_index'],
                'hours' => $hours_final
            );
        }


        // 4.
        // Merge hours in a final list
        $final_list = $this->convert_list_hours_to_group_options($schedules, $list_hours_filtered);
        return $final_list;
        */
    }

    // Date integer
    function filter_late_booking($date, $list_hours )
    {

        if (empty($list_hours)) { return $list_hours;
        }

        $day = date('Y-m-d', $date);

        $current_day = date('Y-m-d H:i', current_time('timestamp'));

        $new_list_hours = array();

        // Late option
        $rm = new QRR_Restaurant_Model($this->restaurant_id);
        $late = $rm->get_late_booking();

        $late_minutes = 60;
        $late_days = -1;

        if (preg_match('#(.+)m#', $late, $matches) ) {
            $late_minutes = intval($matches[1]);
        } else if (preg_match('#(.+)h#', $late, $matches) ) {
            $late_minutes = 60 * intval($matches[1]);
        } else if (preg_match('#(.+)d#', $late, $matches) ) {
            $late_days = intval($matches[1]);
        }

        $now_time = current_time('timestamp');
        $now_0 = date('Y', $now_time)*365 + date('z', $now_time);

        foreach($list_hours as $schedule_data){

            $schedule_index = $schedule_data['schedule_index'];
            $schedule_hours = $schedule_data['hours'];
            $new_hours = array();

            foreach($schedule_hours as $the_hour){

                $day_time = strtotime($day.' '.$the_hour.':00');
                $day_0 = date('Y', $day_time)*365 + date('z', $day_time);

                // Compare minutes
                if ($late_days == -1) {

                    $diff_minutes = ($day_time - $now_time) / 60;
                    if ($diff_minutes >= $late_minutes) {
                        $new_hours[] = $the_hour;
                    }

                } else {

                    $diff_days = ($day_0 - $now_0);
                    if ($diff_days >= $late_days) {
                        $new_hours[] = $the_hour;
                    }
                }

            }

            if (!empty($new_hours)) {
                $new_list_hours[] = array(
                    'schedule_index' => $schedule_index,
                    'hours' => $new_hours
                );
            }

        }

        return $new_list_hours;
    }

    // FIlter looking only the start hour of the booking
    function filter_available_seats($date, $list_hours, $party)
    {

        if (empty($list_hours)) { return $list_hours;
        }

        $list_seats = QRR_Bookings_Controller::get_available_seats($date, $this->restaurant_id);

        $list_hours_filtered = array();

        foreach($list_hours as $s_hour){

            $index = $s_hour['schedule_index'];

            if (isset($list_seats['schedule-'.$index])) {

                $the_seats = $list_seats['schedule-'.$index];

                $hours_filtered = array();
                foreach($s_hour['hours'] as $hour){

                    // I can check all hours of the duration here not only the start hour
                    // Get the duration needed
                    // Check every 5 minutes, but better use can_book

                    if  (isset($the_seats[$hour]) && $the_seats[$hour]['seats_available'] >= $party ) {
                        $hours_filtered[] = $hour;
                    }


                }

                if (!empty($hours_filtered)) {
                    $list_hours_filtered[] = array(
                        'schedule_index' => $index,
                        'hours' => $hours_filtered
                    );
                }

            }

        }

        return $list_hours_filtered;
    }

    // Filter checking all the duration of the booking needed
    // date y/m/d
    // array(array(schedule_index=>'',hours=>array(19:00,19:15,...)))
    function filter_can_book( $date, $list_hours, $party )
    {

        if (empty($list_hours)) { return $list_hours;
        }

        $list_hours_filtered = array();
        foreach($list_hours as $s_hour){

            $schedule_index = $s_hour['schedule_index'];
            $hours_filtered = array();
            foreach($s_hour['hours'] as $hour){
                $result = QRR_Bookings_Controller::can_book($this->restaurant_id, $date, $hour, $schedule_index, $party);
                if (isset($result['success']) && $result['success']) {
                    $hours_filtered[] = $hour;
                }
            }

            if (!empty($hours_filtered)) {
                $list_hours_filtered[] = array(
                    'schedule_index' => $schedule_index,
                    'hours' => $hours_filtered
                );
            }

        }

        return $list_hours_filtered;
    }




    // Needed for grouping the select field in options group
    public function convert_list_hours_to_group_options($schedules = array(), $list_hours_filtered = array())
    {

        $final_list = array();

        foreach( $list_hours_filtered as $hours ){

            $index = $hours['schedule_index'];
            $option_group = array();

            if (!empty($hours['hours'])) {
                foreach( $hours['hours'] as $hour ) {

                    $option_group[] = array(
                        'value' => $index.'_'.$hour,
                        'name' => $hour
                    );

                }
            }

            if (!empty($option_group)) {

                $index_str = $option_group[0]['value'];
                $index_arr = explode('_', $index_str);
                $index = intval($index_arr[0]);

                $final_list[] = array(
                    'label' => $schedules[$index]['name'],
                    'list' => $option_group
                );
                //$final_list[] = $option_group; // Based on option groups for schedules
            }

        }

        return $final_list;
    }


    public function get_hours_for_schedule( $schedule )
    {

        if ($schedule['time_type'] == 'specific') {

            return $schedule['time_specific'];
        }

        if ($schedule['alltime']) {

            return $this->get_list_hours_for_interval(
                '00:00',
                '23:55',
                $schedule['time_interval']
            );
        }

        else if ($schedule['time_type'] == 'interval') {

            return $this->get_list_hours_for_interval(
                $schedule['time']['from'],
                $schedule['time']['to'],
                $schedule['time_interval']
            );

        }

        return array();
    }

    public function get_list_hours_for_interval( $from, $to, $interval )
    {

        $list = array();

        $start = $this->hour_to_integer($from);
        $end = $this->hour_to_integer($to);
        $interval = intval($interval);

        for ($i = $start; $i <= $end; $i += $interval ){
            $list[] = $this->integer_to_hour($i);
        }

        return $list;
    }

    function hour_to_integer( $hour = '00:00' )
    {

        $data = explode(':', $hour);
        $totalminutes = intval($data[0]) * 60 + intval($data[1]);

        return $totalminutes;
    }

    function integer_to_hour( $totalminutes = 0 )
    {

        $hours = intval(floor($totalminutes/60));
        $minutes = $totalminutes - 60 * $hours;

        $str_hours = $hours < 10 ? "0".$hours : "".$hours;
        $str_minutes = $minutes < 10 ? "0".$minutes : "".$minutes;

        return $str_hours.':'.$str_minutes;
    }


}


/*
// Helpers
function qrr_object2array( $o )
{
    $a = (array) $o;
    foreach( $a as &$v ) {
        if( is_object( $v ) ) {
            $v = qrr_object2array( $v );
        }
    }
    return $a;
}
*/
