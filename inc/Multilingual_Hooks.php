<?php
namespace AweBooking;

use AweBooking\Support\Multilingual;
use Skeleton\Container\Service_Hooks;

class Multilingual_Hooks extends Service_Hooks {
	/**
	 * Registers services on the given container.
	 *
	 * @param AweBooking $awebooking AweBooking Container instance.
	 */
	public function register( $awebooking ) {
		$awebooking->bind( 'multilingual', function () {
			return new Multilingual;
		});

		$awebooking->extend( 'option_key', function ( $option_key ) use ( $awebooking ) {
			if ( $awebooking->is_multi_language() ) {
				$active_language = $awebooking['multilingual']->get_active_language();

				// If active language is "en", "" or all.
				if ( ! in_array( $active_language , [ '', 'en', 'all' ] ) ) {
					$option_key .= '_' . $active_language;
				}
			}

			return $option_key;
		});
	}

	/**
	 * Init service provider.
	 *
	 * @param AweBooking $awebooking AweBooking instance.
	 */
	public function init( $awebooking ) {
		add_filter( 'awebooking/room_type/get_id_for_rooms', [ $this, 'room_type_id' ] );

		// Make sure the options are copied if needed.
		if ( $awebooking->is_multi_language() ) {
			$this->run_copy_settings();
		}
	}

	public function room_type_id( $room_id ) {
		if ( awebooking()->is_multi_language() ) {
			return awebooking( 'multilingual' )->get_original_object_id( $room_id );
		}

		return $room_id;
	}

	protected function run_copy_settings() {
		$awebooking = awebooking();

		if ( AweBooking::SETTING_KEY !== $awebooking['option_key'] ) {
			$current_options = $awebooking['setting']->all();
			$original_options = (array) get_option( AweBooking::SETTING_KEY, [] );

			if ( ! empty( $original_options ) && empty( $current_options ) ) {
				update_option( $awebooking['option_key'], $original_options );
			}
		}
	}
}
