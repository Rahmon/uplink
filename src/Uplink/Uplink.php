<?php

namespace StellarWP\Uplink;

class Uplink {

	/**
	 * Initializes the service provider.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function init() {
		if ( ! Config::has_container() ) {
			throw new \RuntimeException( 'You must call StellarWP\Uplink\Config::set_container() before calling StellarWP\Telemetry::init().' );
		}

		$container = Config::get_container();

		$container->singleton( API\Client::class, API\Client::class );
		$container->singleton( Resources\Collection::class, Resources\Collection::class );
		$container->singleton( Site\Data::class, Site\Data::class );
		$container->singleton( Admin\Provider::class, Admin\Provider::class );

		if ( static::is_enabled() ) {
			$container->get( Admin\Provider::class )->register();
		}
	}

	/**
	 * Returns whether or not licensing validation is disabled.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_disabled() : bool {
		$is_pue_disabled       = defined( 'TRIBE_DISABLE_PUE' ) && TRIBE_DISABLE_PUE;
		$is_licensing_disabled = defined( 'STELLARWP_LICENSING_DISABLED' ) && STELLARWP_LICENSING_DISABLED;

		return $is_pue_disabled || $is_licensing_disabled;
	}

	/**
	 * Returns whether or not licensing validation is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_enabled() : bool {
		return ! static::is_disabled();
	}
}
