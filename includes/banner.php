<?php

class QRR_Header_Banner {

	public function __construct() {
		add_action( 'admin_bar_menu', array($this, 'banner') );
		//add_action( 'admin_notices', array($this, 'banner') );
	}

	public function banner() {
		$screen = get_current_screen();

        if (isset($_GET['post_type'])) {
            $post_type = sanitize_text_field($_GET['post_type']);
            if (in_array($post_type, ['qrr_booking', 'qrr_restaurant', 'qrr_client'])) {
	            $this->banner_content();
            }
        }
	}

    public function banner_content() {
        $img_url = QRR_PLUGIN_URL.'assets/img/dashboard-banner-4.png';
	    ?>

            <style>
                .qrr-upgrade-banner {
                    position: relative;
                    /*float: left;*/
                    margin-left: -20px;
                    width: calc(100% + 20px);
                    height:256px;
                    background: url(<?php echo $img_url; ?>) center no-repeat #1b0f49;
                    background-size: cover;
                }
                .qrr-banner-title {
                    color: white;
                    font-size: 20px;
                    font-weight: bold;
                    padding-left: 20px;
                    padding-top: 20px;
                }
                .qrr-banner-features {
                    color: white;
                    padding-left: 30px;
                }
                .qrr-banner-button {
                    position: absolute;
                    top: 100px;
                    left: 320px;
                    font-size: 40px;
                }
                .qrr-banner-button a {
                    font-size: 24px;
                    display: inline-block;
                    padding: 10px 20px;
                    background: orange;
                    color: white;
                    text-decoration: none;
                    border-radius: 10px;
                    border: 2px solid white;
                }
                .qrr-banner-button a:hover {
                    background: orangered;
                }
                .qrr-banner-license {
                    color: #65f8ec;
                    padding-left: 20px;
                    padding-top: 7px;
                }
                @media screen and (max-width: 640px) {
                    .qrr-banner-title {
                        padding-top: 60px;
                    }
                    .qrr-banner-button {
                        position: absolute;
                        top: auto;
                        bottom: 20px;
                        left: 280px;
                        font-size: 40px;
                    }
                    .qrr-banner-license {
                        display: none;
                    }
                }
            </style>

            <div class="qrr-upgrade-banner">

                <div class="qrr-banner-title">Quick Restaurant Reservations <span style="color:#cdcdcd">is now</span> <span style="color: orange">Alex Reservations</span></div>
                <div class="qrr-banner-features">
                    <ul>
                        <li>- Full screen Dashboard / Access Control</li>
                        <li>- Tables Management / Timeline View</li>
                        <li>- Booking Tags / Customers CRM</li>
                        <li>- Multiple Schedules / Events</li>
                        <li>- Customize Multiple Widgets</li>
                        <li>- Metrics / Reviews</li>
                        <li>- Migrate Bookings from QRR</li>
                    </ul>
                </div>
                <div class="qrr-banner-button">
                    <a target="_blank" href="https://alexreservations.com/?utm_source=plugin_qrr&utm_campaign=qrr_to_alex&utm_medium=wp_repository&utm_term=analytics&utm_content=banner+link">Get it now</a>
                </div>
                <div class="qrr-banner-license">
                    (If you have addons with active license we can move them to the new plugin)
                </div>
            </div>

	    <?php
    }
}

new QRR_Header_Banner();
