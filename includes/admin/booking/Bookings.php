<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


class QRR_Bookings
{

    public function __construct()
    {

    }

    public function get_seats_available( $restaurant_id, $datetime )
    {

        $list = array();

        // @TODO ++++ get_seats_available

        // Create every 5 minutes
        $max = 24*60;
        for( $i = 0; $i < $max; $i += 5 ) {
            // hours => seats
            $list[ $this->integer_to_hour($i) ] = rand(50, 100);
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