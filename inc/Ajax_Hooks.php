<?php
namespace AweBooking;

use AweBooking\Booking\Request;
use AweBooking\Support\Formatting;
use Skeleton\Container\Service_Hooks;
use AweBooking\Pricing\Price_Calculator;

class Ajax_Hooks extends Service_Hooks {
	/**
	 * Init service provider.
	 *
	 * This method will be run after container booted.
	 *
	 * @param AweBooking $awebooking AweBooking Container instance.
	 */
	public function init( $awebooking ) {
		add_action( 'wp_ajax_nopriv_awebooking/price_calculator', array( $this, 'price_calculator' ) );
		add_action( 'wp_ajax_awebooking/price_calculator', array( $this, 'price_calculator' ) );
	}

	/**
	 * This function contains output data.
	 */
	public function price_calculator() {
		try {
			$room_type = Factory::create_room_from_request();
			$booking_request = Factory::create_booking_request();

			$availability = Concierge::check_room_type_availability( $room_type, $booking_request );

		} catch ( \Exception $e ) {
			return wp_send_json_error( [ 'message' => $e->getMessage() ], 400 );
		}

		if ( $availability->unavailable() ) {
			return wp_send_json_error( [ 'message' => esc_html__( 'Unavailable room type.', 'awebooking' ) ], 400 );
		}

		$extra_services = array_map( 'absint', $_REQUEST['extra-services'] );

		$availability->get_request()->set_request( 'extra_services', $extra_services );
		$availability->get_request()->store();

		return wp_send_json_success( [ 'total_price' => (string) $availability->get_total_price() ], 200 );
	}
}
