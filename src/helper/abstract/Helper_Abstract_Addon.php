<?php

namespace GFPDF\Helper;

/**
 * Abstract Helper Addon
 *
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2016, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.2
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    This file is part of Gravity PDF.

    Gravity PDF – Copyright (C) 2016, Blue Liquid Designs

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * An abstract class to assist with addon licensing
 */
abstract class Helper_Abstract_Addon {

	/**
	 * @var string The add-on slug (usually the name with the spaces substituted for hyphens)
	 *
	 * @since 4.2
	 */
	private $slug;

	/**
	 * @var string The add-on name (should match the name/title used in EDD)
	 *
	 * @since 4.2
	 */
	private $name;

	/**
	 * @var string The add-on author
	 *
	 * @since 4.2
	 */
	private $author;

	/**
	 * @var string The add-on version
	 *
	 * @since 4.2
	 */
	private $version;

	/**
	 * @var string The add-on mail file path
	 *
	 * @since 4.2
	 */
	private $addon_path_main_plugin_file;

	/**
	 * Holds our registred objects
	 *
	 * @var Helper_Singleton
	 *
	 * @since 4.2
	 */
	public $singleton;

	/**
	 * Holds our Helper_Data object
	 * which we can autoload with any data needed
	 *
	 * @var Helper_Data
	 *
	 * @since 4.2
	 */
	protected $data;

	/**
	 * Holds our Helper_Abstract_Options / Helper_Options_Fields object
	 * Makes it easy to access global PDF settings and individual form PDF settings
	 *
	 * @var Helper_Options_Fields
	 *
	 * @since 4.2
	 */
	protected $options;

	/**
	 * Holds our log class
	 *
	 * @var \Monolog\Logger
	 *
	 * @since 4.2
	 */
	protected $log;

	/**
	 * Give easy access to our notice helper
	 *
	 * @var Helper_Notices
	 *
	 * @since 4.2
	 */
	protected $notices;

	/**
	 * Helper_Abstract_Addon constructor.
	 *
	 * @param string                $addon_slug
	 * @param string                $addon_name
	 * @param string                $author
	 * @param string                $version
	 * @param string                $path_to_main_plugin_file
	 * @param Helper_Data           $data
	 * @param Helper_Options_Fields $options
	 * @param Helper_Singleton      $singleton
	 * @param Helper_Logger         $log
	 * @param Helper_Notices        $notices
	 *
	 * @since 4.2
	 */
	public function __construct( $addon_slug, $addon_name, $author, $version, $path_to_main_plugin_file, Helper_Data $data, Helper_Options_Fields $options, Helper_Singleton $singleton, Helper_Logger $log, Helper_Notices $notices ) {
		$this->slug                        = $addon_slug;
		$this->name                        = $addon_name;
		$this->author                      = $author;
		$this->version                     = $version;
		$this->addon_path_main_plugin_file = $path_to_main_plugin_file;

		$this->data      = $data;
		$this->options   = $options;
		$this->singleton = $singleton;
		$this->log       = $log->get_logger();

		$this->notices = $notices;
		$this->notices->init();
	}

	/**
	 * @return string Return the plugin slug
	 *
	 * @since 4.2
	 */
	final public function get_slug() {
		return $this->slug;
	}

	/**
	 * @return string Return the plugin name
	 *
	 * @since 4.2
	 */
	final public function get_name() {
		return $this->name;
	}

	/**
	 * @return string Return the short name for the plugin
	 *
	 * @since 4.2
	 */
	final public function get_short_name() {
		return trim(
			str_replace(
				'Gravity PDF',
				'',
				$this->get_name()
			)
		);
	}

	/**
	 * @return string Return the plugin version
	 *
	 * @since 4.2
	 */
	final public function get_version() {
		return $this->version;
	}

	/**
	 * @return string Return the plugin author
	 *
	 * @since 4.2
	 */
	final public function get_author() {
		return $this->author;
	}

	/**
	 * @return string Return the plugin main file path
	 *
	 * @since 4.2
	 */
	final public function get_main_plugin_file() {
		return $this->addon_path_main_plugin_file;
	}

	/**
	 * Setup the add-on licensing and initialise any classes
	 *
	 * @param array $classes
	 *
	 * @since 4.2
	 */
	public function init( $classes = [] ) {

		/*
		 * Register our plugin updater on the admin initialisation action
		 *
		 * @Internal Due to WordPress.org rules we cannot initialisation the updater code in the core plugin
		 *           Add-ons have to initialise this functionality via GFPDF\Helper\Licensing\EDD_SL_Plugin_Updater
		 */
		add_action( 'admin_init', [ $this, 'plugin_updater' ] );

		/*
		 * Automatically register our addon with the main plugin to enable license management in the UI
		 */
		$this->register_addon();

		/*
		 * Register add-on fields (if any) when class uses our extension interface
		 */
		if( $this instanceof Helper_Interface_Extension_Settings ) {
			add_filter( 'gfpdf_settings_extensions', [ $this, 'register_addon_fields'] );
		}

		/*
		 * Automatically schedule license checks weekly
		 */
		add_action( 'admin_init', [ $this, 'maybe_schedule_license_check' ] );
		add_action( 'gfpdf_' . $this->get_slug() . '_license_check', [ $this, 'schedule_license_check' ] );

		/*
		 * Run the init() method (if it exists) for the add-on classes and register them with our internal singleton
		 */
		array_walk( $classes, function( $class ) {
			if ( method_exists( $class, 'init' ) ) {
				$class->init();
			}

			$this->singleton->add_class( $class );
		} );

		$this->log->notice( sprintf( '%s plugin fully loaded', $this->get_name() ) );
	}

	/**
	 * This method handles the add-on update code
	 *
	 * Due to WordPress.org rules we cannot initialisation the updater code in the core plugin so add-ons that utilise
	 * this class need to handle that code themselves.
	 *
	 * Official Gravity PDF add-ons should initialise the GFPDF\Helper\Licensing\EDD_SL_Plugin_Updater class
	 * when the add-on license status is set to "active". You can check the status of the plugin
	 * using the following:
	 *
	 * $license_info = $this->get_license_info();
	 * if ( $license_info['status'] !== 'active' ) {
	 *    return;
	 * }
	 *
	 * The EDD_SL_Plugin_Updater should be initialised as follows:
	 *
	 * new EDD_SL_Plugin_Updater(
	 *     $this->data->store_url,
	 *   $this->get_main_plugin_file(),
	 *   [
	 *      'version'   => $this->get_version(),
	 *      'license'   => $license_info['license'],
	 *      'item_name' => $this->get_addon_name(),
	 *      'author'    => $this->get_version(),
	 *      'beta'      => false,
	 *   ]
	 * );
	 *
	 * @since 4.2
	 *
	 * @return void
	 */
	public abstract function plugin_updater();

	/**
	 * Register the add-on with Gravity PDF
	 *
	 * @Internal If you don't want the add-on licensing handled automatically in the UI override this method
	 *
	 * @since    4.2
	 */
	protected function register_addon() {
		$this->data->add_addon( $this );
	}

	/**
	 * When Helper_Interface_Extension_Settings is used we'll auto-register any
	 * settings the add-on includes
	 *
	 * @param array $settings
	 *
	 * @return array
	 *
	 * @since 4.2
	 */
	final public function register_addon_fields( $settings ) {
		/*
		 * Because this method is called via a filter it needs to be public
		 * so we'll check the class impliments the correct interface before
		 * doing anything.
		 */
		if( ! $this instanceof Helper_Interface_Extension_Settings ) {
			return $settings;
		}

		$registered_fields = $this->get_global_addon_fields();

		/* Add plugin heading before fields are included */
		return array_merge( $settings, [
			$this->get_slug() . '_heading' => [
				'id'   => $this->get_slug() . '_heading',
				'type' => 'descriptive_text',
				'desc' => '<h4 class="section-title">' . $this->get_name() . '</h4>',
				'class' => 'gfpdf-no-padding',
			],
		], $registered_fields );
	}

	/**
	 * Get the add-on license information stored in the database (if any)
	 *
	 * @Internal If you don't want the add-on licensing handled automatically in the UI override this method
	 *
	 * @since    4.2
	 */
	public function get_license_info() {
		$settings = $this->options->get_settings();

		$slug    = $this->get_slug();
		$license = ( isset( $settings["license_$slug"] ) ) ? $settings["license_$slug"] : '';
		$status  = ( isset( $settings["license_{$slug}_status"] ) ) ? $settings["license_{$slug}_status"] : '';
		$message = ( isset( $settings["license_{$slug}_message"] ) ) ? $settings["license_{$slug}_message"] : '';

		$license_details = [
			'license' => $license,
			'status'  => $status,
			'message' => $message,
		];

		$this->log->notice( 'Get plugin license details', $license_details );

		return $license_details;
	}

	/**
	 * Update the add-on license information stored in the database
	 *
	 * @param array $license_info
	 *
	 * @Internal If you don't want the add-on licensing handled automatically in the UI override this method
	 *
	 * @since    4.2
	 */
	public function update_license_info( $license_info ) {
		$settings = $this->options->get_settings();
		$slug     = $this->get_slug();

		$settings["license_$slug"]           = $license_info['license'];
		$settings["license_{$slug}_status"]  = $license_info['status'];
		$settings["license_{$slug}_message"] = $license_info['message'];

		$this->log->notice( 'Update plugin license details', $license_info );

		$this->options->update_settings( $settings );
	}

	/**
	 * Remove the license info and keys from the settings
	 *
	 * @since 4.2
	 */
	public function delete_license_info() {
		$settings = $this->options->get_settings();
		$slug     = $this->get_slug();

		unset( $settings["license_$slug"] );
		unset( $settings["license_{$slug}_status"] );
		unset( $settings["license_{$slug}_message"] );

		$this->log->notice( 'Delete plugin license details' );

		$this->options->update_settings( $settings );
	}

	/**
	 * @return string Returns the current add-on license key
	 *
	 * @since 4.2
	 */
	final public function get_license_key() {
		return $this->get_license_info()['license'];
	}

	/**
	 * @return string Returns the currenct add-on license status
	 *
	 * @since 4.2
	 */
	final public function get_license_status() {
		return $this->get_license_info()['status'];
	}

	/**
	 * @return string Returns the current add-on license message
	 *
	 * @since 4.2
	 */
	final public function get_license_message() {
		return $this->get_license_info()['message'];
	}

	/**
	 * Register our license check event one week into the future.
	 *
	 * @Internal Using wp_schedule_single_event() means we don't need to 1. Add a weekly internval to wp_schedule_event()
	 *           and 2. Need to clear the scheduled hook when the plugin is deactivated
	 *
	 * @since    4.2
	 */
	final public function maybe_schedule_license_check() {
		if ( ! wp_next_scheduled( 'gfpdf_' . $this->get_slug() . '_license_check' ) ) {
			wp_schedule_single_event( strtotime( '+ 1 week' ), 'gfpdf_' . $this->get_slug() . '_license_check' );
		}
	}

	/**
	 * Makes an API call to check the status of the license and updates the license settings
	 *
	 * @Internal If you don't want the add-on licensing handled automatically in the UI override this method
	 *
	 * @since    4.2
	 */
	public function schedule_license_check() {
		$this->log->notice( 'Check status of plugin license details' );

		$license_info = $this->get_license_info();

		$response = wp_remote_post( $this->data->store_url, [
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => [
				'edd_action' => 'check_license',
				'license'    => $license_info['license'],
				'item_name'  => urlencode( $this->get_short_name() ),
				'url'        => home_url(),
			],
		] );

		/* If there was a problem with the request we'll try again in an hour */
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			$this->log->error( 'Failed to contact remote API for license status check. Rescheduling.' );
			wp_schedule_single_event( strtotime( '+ 1 hour' ), 'gfpdf_' . $this->get_slug() . '_license_check' );

			return false;
		}

		$license_check = json_decode( wp_remote_retrieve_body( $response ) );

		/* License still valid, no need to do anything */
		if ( isset( $license_check->license ) && $license_check->license === 'valid' ) {
			$this->log->notice( 'License key still valid.' );

			return false;
		}

		/* Error occured. Update status and message in the license settings */
		$possible_responses = $this->data->addon_license_responses( $this->get_name() );

		/* Ensure we have a known error */
		if ( ! isset( $license_check->error ) || ! isset( $possible_responses[ $license_check->error ] ) ) {
			$this->log->error( 'Unknown license status returned from remote API' );

			return false;
		}

		$license_info['status']  = $license_check->error;
		$license_info['message'] = $possible_responses[ $license_check->error ];

		/* Include the expiry date if license expired */
		if ( $license_check->error === 'expired' ) {
			$license_info['message'] = sprintf( $license_info['message'], date_i18n( get_option( 'date_format' ), strtotime( $license_check->expires, current_time( 'timestamp' ) ) ) );
		}

		$this->log->notice( 'License key no longer valid', $license_info );
		$this->update_license_info( $license_info );

		return true;
	}
}