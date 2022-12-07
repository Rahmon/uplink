<?php

namespace StellarWP\Uplink\Admin;

use StellarWP\Uplink\Contracts\Abstract_Subscriber;

class Provider extends Abstract_Subscriber {
	/**
	 * Register the service provider.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$this->container->singleton( Plugins_Page::class, Plugins_Page::class );
		$this->container->singleton( License_Field::class, License_Field::class );
		$this->container->singleton( Notice::class, Notice::class );
		$this->container->singleton( Ajax::class, Ajax::class );
		$this->container->singleton( Package_Handler::class, Package_Handler::class );
		$this->container->singleton( Update_Prevention::class, Update_Prevention::class );

		$this->register_hooks();
	}

	public function register_hooks(): void {
		add_filter( 'plugins_api', function ( $result, $action, $args ) {
			return $this->container->get( Plugins_Page::class)->inject_info( $result, $action, $args );
		}, 10, 3 );

		if ( ( ! defined( 'TRIBE_DISABLE_PUE' ) || true !== TRIBE_DISABLE_PUE ) ) {
			add_filter( 'pre_set_site_transient_update_plugins', function ( $transient ) {
				return $this->container->get( Plugins_Page::class )->check_for_updates( $transient );
			}, 10, 1 );
		}

		add_action( 'admin_init', function() {
			$this->container->get( License_Field::class)->register_settings();
		}, 10, 0 );

		add_action( 'admin_enqueue_scripts',  function () {
			$this->container->get( License_Field::class )->enqueue_assets();
		}, 10, 0 );

		add_action( 'admin_notices', function () {
			$this->container->get( Notice::class )->setup_notices();
		}, 10, 0 );

		add_action( 'wp_ajax_pue-validate-key-uplink' , function () {
			$this->container->get( Ajax::class)->validate_license();
		}, 10, 0 );

		add_action( 'admin_enqueue_scripts', function ( $page ) {
			$this->container->get( Plugins_Page::class)->display_plugin_messages( $page );
		}, 1, 1 );

		add_action( 'admin_enqueue_scripts', function ( $page ) {
			$this->container->get( Plugins_Page::class )->store_admin_notices( $page );
		}, 10, 0 );

		add_action( 'load-plugins.php', function () {
			$this->container->get( Plugins_Page::class )->remove_default_inline_update_msg();
		}, 50, 0 );

		add_filter( 'upgrader_pre_download', function ( $reply, $package, $upgrader ) {
			return $this->container->get( Package_Handler::class )->filter_upgrader_pre_download( $reply, $package, $upgrader );
		}, 5, 3 );

		add_filter( 'upgrader_source_selection', function ( $source, $remote_source, $upgrader, $extras )  {
			return $this->container->callback( Update_Prevention::class )->filter_upgrader_source_selection( $source, $remote_source, $upgrader, $extras );
		}, 15, 4 );
	}
}
