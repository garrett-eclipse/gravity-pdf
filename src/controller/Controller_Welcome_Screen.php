<?php

namespace GFPDF\Controller;

use GFPDF\Helper\Helper_Abstract_Controller;
use GFPDF\Helper\Helper_Abstract_Model;
use GFPDF\Helper\Helper_Abstract_View;
use GFPDF\Helper\Helper_Interface_Actions;
use GFPDF\Helper\Helper_Interface_Filters;
use GFPDF\Helper\Helper_Data;
use GFPDF\Helper\Helper_Options;

use Psr\Log\LoggerInterface;

/**
 * Welcome Screen Controller
 *
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2015, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       4.0
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    This file is part of Gravity PDF.

    Gravity PDF Copyright (C) 2015 Blue Liquid Designs

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
 * Controller_Welcome_Screen
 * A general class for About / Intro Screen
 *
 * @since 4.0
 */
class Controller_Welcome_Screen extends Helper_Abstract_Controller implements Helper_Interface_Actions, Helper_Interface_Filters
{

	/**
	 * Holds our log class
	 * @var Object
	 * @since 4.0
	 */
	protected $log;

	/**
	 * Holds our Helper_Data object
	 * which we can autoload with any data needed
	 * @var Object
	 * @since 4.0
	 */
	protected $data;

	/**
	 * Holds our Helper_Options / Helper_Options_Fields object
	 * Makes it easy to access global PDF settings and individual form PDF settings
	 * @var Object
	 * @since 4.0
	 */
	protected $options;

	/**
	 * Load our model and view and required actions
	 */
	public function __construct( Helper_Abstract_Model $model, Helper_Abstract_View $view, LoggerInterface $log, Helper_Data $data, Helper_Options $options ) {
	
		/* Assign our internal variables */
		$this->log     = $log;
		$this->data    = $data;
		$this->options = $options;

		/* Load our model and view */
		$this->model = $model;
		$this->model->setController( $this );

		$this->view  = $view;
		$this->view->setController( $this );
	}

	/**
	 * Initialise our class defaults
	 * @since 4.0
	 * @return void
	 */
	public function init() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Apply any actions needed for the welcome page
	 * @since 4.0
	 * @return void
	 */
	public function add_actions() {
		 /* Load the welcome screen into the menu */
		add_action( 'admin_menu', array( $this->model, 'admin_menus' ) );
		add_action( 'admin_init', array( $this, 'welcome' ) );
	}

	/**
	 * Apply any filters needed for the welcome page
	 * @since 4.0
	 * @return void
	 */
	public function add_filters() {
		add_filter( 'admin_title', array( $this->model, 'add_page_title' ), 10, 3 );
	}

	/**
	 * Sends user to the Welcome page on first activation, as well as everytime plugin is upgraded
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function welcome() {

		/* Bail if no activation redirect */
		if ( ! get_transient( '_gravitypdf_activation_redirect' ) ) {
			return false;
		}

		/* Delete the redirect transient */
		delete_transient( '_gravitypdf_activation_redirect' );

		/* Bail if activating from network, or bulk */
		if ( is_network_admin() || isset($_GET['activate-multi']) ) {
			$this->log->addNotice( 'Network Activation.' );

			return false;
		}

		/* add own update tracker */
		if ( ! $this->data->is_installed ) {
			$this->log->addNotice( 'Redirect to Getting Started page (first time activated).' );

			/* First time install */
			wp_safe_redirect( admin_url( 'index.php?page=gfpdf-getting-started' ) );
			exit;

		} else {
			$this->maybe_display_update_screen();
		}
	}

	public function maybe_display_update_screen() {

		/* Check we actually upgraded, otherwise don't redirect */
		if( PDF_EXTENDED_VERSION == get_option( 'gfpdf_current_version' ) ) {
			return false;
		}

		/* Check current version is not a bug fix or security release */
		$version = explode('.', PDF_EXTENDED_VERSION);

		/* Check is there is a third version identifier (4.1.x) and if so see if it's an interger or does not equal zero */
		if( isset( $version[2] ) && ! is_int( $version[2] ) || 0 !== (int) $version[2] ) {
			/* bug fix or security release, do not redirect */
			return false;
		}

		/* Check if the user has opted to view the What's New page */
		$show_update_page = $this->options->get_option( 'update_screen_action', 'Enable' );

		if( $show_update_page == 'Enable' ) {

			$this->log->addNotice( 'Redirect to Update page (previously activated).' );

			/* Update */
			wp_safe_redirect( admin_url( 'index.php?page=gfpdf-update' ) );
			exit;
		}
	}

	/**
	 * Load our welcome screen
	 * @return void
	 * @since 4.0
	 */
	public function getting_started_screen() {
		$this->view->welcome();
	}

	/**
	 * Load our update welcome screen
	 * @return void
	 * @since 4.0
	 */
	public function update_screen() {
		$this->view->update();
	}
}
