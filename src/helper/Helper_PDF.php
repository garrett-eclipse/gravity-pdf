<?php

namespace GFPDF\Helper;

use GFPDF\Model\Model_PDF;
use GFPDF\Helper\Helper_Abstract_Form;
use GFPDF\Helper\Helper_Data;

use mPDF;

use Exception;

/**
 * Generates our PDF document using mPDF
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
 * @since 4.0
 */
class Helper_PDF {

	/**
	 * Holds our PDF Object
	 * @var Object
	 * @since 4.0
	 */
	public $mpdf;

	/**
	 * Holds our Gravity Form Entry Details
	 * @var Array
	 * @since 4.0
	 */
	protected $entry;

	/**
	 * Holds our PDF Settings
	 * @var Array
	 * @since 4.0
	 */
	protected $settings;

	/**
	 * Controls how the PDF should be output.
	 * Whether to display it in the browser, force a download, or save it to disk
	 * @var string
	 * @since 4.0
	 */
	protected $output = 'DISPLAY';

	/**
	 * Holds the predetermined paper size
	 * @var Mixed (String or Array)
	 * @since 4.0
	 */
	protected $paper_size;

	/**
	 * Holds our paper orientation in mPDF flavour
	 * @var String
	 * @since 4.0
	 */
	protected $orientation;

	/**
	 * Holds the full path to the PHP template to load
	 * @var String
	 * @since 4.0
	 */
	protected $template_path;

	/**
	 * Holds the PDF filename that should be used
	 * @var String
	 * @since 4.0
	 */
	protected $filename = 'document.pdf';

	/**
	 * Holds the path the PDF should be saved to
	 * @var String
	 * @since 4.0
	 */
	protected $path;

	/**
	 * Whether to force the print dialog when the PDF is opened
	 * @var boolean
	 * @since 4.0
	 */
	protected $print = false;

	/**
	 * Holds abstracted functions related to the forms plugin
	 * @var Object
	 * @since 4.0
	 */
	protected $form;

	/**
	 * Holds our Helper_Data object
	 * which we can autoload with any data needed
	 * @var Object
	 * @since 4.0
	 */
	protected $data;

	/**
	 * Initialise our class
	 * @param Array $entry    The Gravity Form Entry to be processed
	 * @param Array $settings The Gravity PDF Settings Array
	 * @since 4.0
	 */
	public function __construct( $entry, $settings, Helper_Abstract_Form $form, Helper_Data $data ) {
		
		/* Assign our internal variables */
		$this->entry    = $entry;
		$this->settings = $settings;
		$this->form     = $form;
		$this->data     = $data;

		$this->set_path();
	}

	/**
	 * A public method to start our PDF creation process
	 * @return void
	 * @since 4.0
	 */
	public function init() {
		$this->set_paper();
		$this->begin_pdf();
		$this->set_image_dpi();
		$this->set_text_direction();
		$this->set_pdf_format();
		$this->set_pdf_security();
	}

	/**
	 * Render the HTML to our PDF
	 * @param  Array  $args Any arguments that should be passed to the PDF template
	 * @param  String $html By pass the template  file and pass in a HTML string directly to the engine. Optional.
	 * @return void
	 * @since 4.0
	 */
	public function render_html( $args = array(), $html = '' ) {

		/* Because this class can load any content we'll only set up our template if no HTML is passed */
		if( empty( $html ) ) {
			$this->set_template();
		}
		
		$form = $this->form->get_form( $this->entry['form_id'] );

		/* Add filter to prevent HTML being written to document when it returns true. Backwards compatibility. */
		$prevent_html = apply_filters("gfpdfe_pre_load_template", $form['id'], $this->entry['id'], basename($this->template_path), $form['id'] . $this->entry['id'], $this->backwards_compat_output($this->output), $this->filename, $this->backwards_compat_conversion($this->settings), $args ); /* Backwards Compatibility */

		if( $prevent_html === true ) {
			return;
		}

		/* Load in our PHP template */
		if ( empty( $html ) ) {
			$html = $this->load_html( $args );
		}

		/* Apply our filters */
		$html = apply_filters("gfpdfe_pdf_template_{$form['id']}", apply_filters('gfpdfe_pdf_template', $html, $form['id'], $this->entry['id'], $this->settings), $this->entry['id'], $this->settings); /* Backwards compat */
		$html = apply_filters("gfpdf_pdf_html_output_{$form['id']}", apply_filters('gfpdf_pdf_html_output', $html, $form, $this->entry, $this->settings), $this->form, $this->entry, $this->settings);

		/* Check if we should output the HTML to the browser, for debugging */
		$this->maybe_display_raw_html( $html );

		/* Write the HTML to mPDF */
		$this->mpdf->WriteHTML( $html );
	}

	/**
	 * Create the PDF
	 * @return void
	 * @since 4.0
	 */
	public function generate() {

		/* Process any final settings before outputting */
		$this->show_print_dialog();

		/* allow $mpdf object class to be modified */
		apply_filters( 'gfpdf_mpdf_class', $this->mpdf, $this->entry, $this->settings );

		apply_filters( 'gfpdfe_mpdf_class_pre_render', $this->mpdf, $this->entry['form_id'], $this->entry['id'], $this->settings, '', $this->get_filename() ); /* backwards compat */
		apply_filters( 'gfpdfe_pre_render_pdf', $this->mpdf, $this->entry['form_id'], $this->entry['id'], $this->settings, '', $this->get_filename() ); /* backwards compat */
		apply_filters( 'gfpdfe_mpdf_class', $this->mpdf, $this->entry['form_id'], $this->entry['id'], $this->settings, '', $this->get_filename() ); /* backwards compat */

		/* If a developer decides to disable all security protocols we don't want the PDF indexed */
		if ( ! headers_sent() ) {
			header( 'X-Robots-Tag: noindex, nofollow', true );
		}

		switch ( $this->output ) {
			case 'DISPLAY':
				$this->mpdf->Output( $this->filename, 'I' );
				exit;
			break;

			case 'DOWNLOAD':
				$this->mpdf->Output( $this->filename, 'D' );
				exit;
			break;

			case 'SAVE':
				return $this->mpdf->Output( '', 'S' );
			break;
		}
	}

	/**
	 * Save the PDF to our tmp directory
	 * @param  String $raw_pdf_string  The generated PDF to be saved
	 * @return Mixed                   The full path to the file or false if failed
	 * @since  4.0
	 */
	public function save_pdf( $raw_pdf_string ) {

		/* create our path */
		if ( ! is_dir( $this->path ) ) {
			if ( ! wp_mkdir_p( $this->path ) ) {
				throw new Exception( sprintf( 'Could not create directory: %s' ), esc_html( $this->path ) );
			}
		}

		/* save our PDF */
		if ( ! file_put_contents( $this->path . $this->filename, $raw_pdf_string ) ) {
			throw new Exception( sprintf( 'Could not save PDF: %s', $this->path . $this->filename ) );
		}

		return $this->path . $this->filename;
	}

	/**
	 * Public endpoint to allow users to control how the generated PDF will be displayed
	 * @param String $type Only display, download or save options are valid
	 * @since 4.0
	 */
	public function set_output_type( $type ) {
		$valid = array( 'DISPLAY', 'DOWNLOAD', 'SAVE' );

		if ( ! in_array( strtoupper( $type ), $valid ) ) {
			throw new Exception( sprintf( 'Display type not valid. Use %s', implode( ', ', $valid ) ) );
			return;
		}

		$this->output = strtoupper( $type );
	}


	/**
	 * Public Method to mark the PDF document creator
	 * @return void
	 * @since 4.0
	 */
	public function set_creator( $text = '' ) {
		if ( empty($text) ) {
			$this->mpdf->SetCreator( 'Gravity PDF v' . PDF_EXTENDED_VERSION . '. https://gravitypdf.com' );
		} else {
			$this->mpdf->SetCreator( $text );
		}
	}

	/**
	 * Public Method to set how the PDF should be displyed when first open
	 * @param Mixed  $mode A string or integer setting the zoom mode
	 * @param String $layout The PDF layout format
	 * @return void
	 * @since 4.0
	 */
	public function set_display_mode( $mode = 'fullpage', $layout = 'continuous' ) {

		$valid_mode = array( 'fullpage', 'fullwidth', 'real', 'default' );
		$valid_layout = array( 'single', 'continuous', 'two', 'twoleft', 'tworight', 'default' );

		/* check the mode */
		if ( ! in_array( strtolower( $mode ), $valid_mode ) ) {
			/* determine if the mode is an integer */
			if ( ! is_int( $mode ) || $mode <= 10 ) {
				throw new Exception( sprintf( 'Mode must be an number value more than 10 or one of these types: %s', implode( ', ', $valid_mode ) ) );
			}
		}

		/* check theh layout */
		if ( ! in_array( strtolower( $mode ), $valid_mode ) ) {
			throw new Exception( sprintf( 'Layout must be one of these types: %s', implode( ', ', $valid_mode ) ) );
		}

		$this->mpdf->SetDisplayMode( $mode, $layout );
	}


	/**
	 * Public Method to allow the print dialog to be display when PDF is opened
	 * @param boolean $print
	 * @return void
	 * @since 4.0
	 */
	public function set_print_dialog( $print = true ) {
		if ( ! is_bool( $print ) ) {
			throw new Exception( 'Only boolean values true and false can been passed to setPrintDialog().' );
		}

		$this->print = $print;
	}

	/**
	 * Generic PDF JS Setter function
	 * @param String $js The PDF Javascript to execute
	 * @since 4.0
	 */
	public function set_JS( $js ) {
		$this->mpdf->SetJS( $js );
	}

	/**
	 * Get the current PDF Name
	 * @return String
	 * @since 4.0
	 */
	public function get_filename() {
		return $this->filename;
	}

	/**
	 * Get the current Gravity Form Entry
	 * @return String
	 * @since 4.0
	 */
	public function get_entry() {
		return $this->entry;
	}

	/**
	 * Get the current PDF Settings
	 * @return String
	 * @since 4.0
	 */
	public function get_settings() {
		return $this->settings;
	}
	/**
	 * Generate the PDF filename used
	 * @return void
	 * @since 4.0
	 */
	public function set_filename( $filename ) {
		$this->filename = $this->get_file_with_extension( $filename, '.pdf' );
	}

	/**
	 * Get the current PDF path
	 * @return String
	 * @since 4.0
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * Sets the path the PDF should be saved to
	 * @param string $path
	 * @return void
	 * @since 4.0
	 */
	public function set_path( $path = '' ) {

		if ( empty($path) ) {
			/* build our PDF path location */
			$path = $this->data->template_tmp_location . $this->entry['form_id'] . $this->entry['id'] . '/';
		} else {
			/* ensure the path ends with a forward slash */
			if ( substr( $path, -1 ) !== '/' ) {
				$path .= '/';
			}
		}

		$this->path = $path;
	}

	/**
	 * Initialise our mPDF object
	 * @return void
	 * @since 4.0
	 */
	protected function begin_pdf() {
		$this->mpdf = new mPDF( '', $this->paper_size, 0, '', 15, 15, 16, 16, 9, 9, $this->orientation );
	}

	/**
	 * Set up the paper size and orentation
	 * @return void
	 * @since 4.0
	 */
	protected function set_paper() {

		/* Get the paper size from the settings */
		$paper_size = (isset($this->settings['pdf_size'])) ? strtoupper( $this->settings['pdf_size'] ) : 'A4';

        $valid_paper_size = array(
            '4A0', '2A0',
            'A0', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'A9', 'A10',
            'B0', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7', 'B8', 'B9', 'B10',
            'C0', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8', 'C9', 'C10',
            'RA0', 'RA1', 'RA2', 'RA3', 'RA4',
            'SRA0', 'SRA1', 'SRA2', 'SRA3', 'SRA4',
            'LETTER', 'LEGAL', 'LEDGER', 'TABLOID', 'EXECUTIVE', 'FOILIO', 'B', 'A', 'DEMY', 'ROYAL', 'CUSTOM'
        );

		if ( ! in_array( $paper_size, $valid_paper_size ) ) {
			throw new Exception( sprintf( 'Paper size not valid. Use %s', implode( ', ', $valid ) ) );
			return;
		}

		/* set our paper size and orientation based on user selection */
		if ( $paper_size == 'CUSTOM' ) {
			$this->set_custom_paper_size();
			$this->set_orientation( true );
		} else {
			$this->set_paper_size( $paper_size );
			$this->set_orientation();
		}
	}

	/**
	 * Set our paper size using pre-defined values
	 * @return void
	 * @since 4.0
	 */
	protected function set_paper_size( $size ) {
		$this->paper_size = $size;
	}

	/**
	 * Set our custom paper size which will be a 2-key array signifying the
	 * width and height of the paper stock
	 * @return void
	 * @since 4.0
	 */
	protected function set_custom_paper_size() {
		$custom_paper_size = (isset($this->settings['custom_pdf_size'])) ? $this->settings['custom_pdf_size'] : array();

		if ( sizeof( $custom_paper_size ) !== 3 ) {
			throw new Exception( 'Custom paper size not valid. Array should contain three keys: width, height and unit type' );
		}

		$this->paper_size = $this->get_paper_size( $custom_paper_size );

	}

	/**
	 * Ensure the custom paper size has the correct values
	 * @param  Array $size
	 * @return Array
	 * @since  4.0
	 */
	protected function get_paper_size( $size ) {
		$size[0] = ($size[2] == 'inches') ? (int) $size[0] * 25.4 : (int) $size[0];
		$size[1] = ($size[2] == 'inches') ? (int) $size[1] * 25.4 : (int) $size[1];

		/* tidy up custom paper size array */
		unset($size[2]);

		return $size;
	}

	/**
	 * Set the page orientation based on the paper size selected
	 * @param Boolean $custom Whether a predefined paper size was used, or a custom size
	 * @return void
	 * @since 4.0
	 */
	protected function set_orientation( $custom = false ) {

		$orientation = (isset($this->settings['orientation'])) ? strtolower( $this->settings['orientation'] ) : 'portrait';

		if ( $custom ) {
			$this->orientation = ($orientation == 'landscape') ? 'L' : 'P';
		} else {
			$this->orientation = ($orientation == 'landscape') ? '-L' : '';
		}
	}

	/**
	 * Get the correct path to the PHP template we should load into mPDF
	 * @return void
	 * @since 4.0
	 */
	protected function set_template() {

		$template = (isset($this->settings['template'])) ? $this->get_file_with_extension( $this->settings['template'] ) : '';

		/* Allow a user to change the current template if they have the appropriate capabilities */
		if ( rgget( 'template' ) && is_user_logged_in() && $this->form->has_capability( 'gravityforms_edit_settings' ) ) {
			$template = $this->get_file_with_extension( rgget( 'template' ) );
		}

		/**
		 * Check for the template's existance
		 * We'll first look for a user-overridding template
		 * Then check our default templates
		 */
		$default_template_path = PDF_PLUGIN_DIR . 'initialisation/templates/';

		if ( is_file( $this->data->template_location . $template ) ) {
			$this->template_path = $this->data->template_location . $template;
		} else if ( is_file( $default_template_path . $template ) ) {
			$this->template_path = $default_template_path . $template;
		} else {
			throw new Exception( 'Could not find the template: ' . esc_html( $template ) );
		}
	}


	/**
	 * Ensure an extension is added to the end of the name
	 * @param  String $name The PHP template
	 * @return String
	 * @since  4.0
	 */
	protected function get_file_with_extension( $name, $extension = '.php' ) {
		if ( substr( $name, -strlen( $extension ) ) !== $extension ) {
			$name = $name . $extension;
		}

		return $name;
	}

	/**
	 * Load our PHP template file and return the buffered HTML
	 * @return String The buffered HTML to pass into mPDF
	 * @since 4.0
	 */
	protected function load_html( $args = array() ) {
		/* for backwards compatibility extract the $args variable */
		extract( $args, EXTR_SKIP ); /* skip any arguments that would clash - i.e filename, args, output, path, this */

		ob_start();
		include $this->template_path;
		return ob_get_clean();
	}


	/**
	 * Allow site admins to view the RAW HTML if needed
	 * @param  String $html
	 * @return void
	 * @since 4.0
	 */
	protected function maybe_display_raw_html( $html ) {
		
		if ( $this->output !== 'SAVE' && rgget( 'html' ) && $this->form->has_capability( 'gravityforms_edit_settings' ) ) {
			echo $html;
			exit;
		}
	}

	/**
	 * Prompt the print dialog box
	 * @return void
	 * @since 4.0
	 */
	protected function show_print_dialog() {
		if ( $this->print ) {
			$this->setJS( 'this.print();' );
		}
	}

	/**
	 * Sets the image DPI in the PDF
	 * @return void
	 * @since 4.0
	 */
	protected function set_image_dpi() {
		$dpi = (isset($this->settings['image_dpi'])) ? (int) $this->settings['image_dpi'] : 96;

		$this->mpdf->img_dpi = $dpi;
	}

	/**
	 * Sets the text direction in the PDF (RTL support)
	 * @return void
	 * @since 4.0
	 */
	protected function set_text_direction() {
		$rtl = (isset($this->settings['rtl'])) ? $this->settings['rtl'] : 'No';

		if ( strtolower( $rtl ) == 'yes' ) {
			$this->mpdf->SetDirectionality( 'rtl' );
		}
	}

	/**
	 * Set the correct PDF Format
	 * Normal, PDF/A-1b or PDF/X-1a
	 * @return void
	 * @since 4.0
	 */
	protected function set_pdf_format() {
		switch ( strtolower( $this->settings['format'] ) ) {
			case 'pdfa1b':
				$this->mpdf->PDFA     = true;
				$this->mpdf->PDFAauto = true;
			break;

			case 'pdfx1a':
				$this->mpdf->PDFX     = true;
				$this->mpdf->PDFXauto = true;
			break;
		}
	}

	/**
	 * Add PDF Security, if able
	 * @return void
	 * @since 4.0
	 */
	protected function set_pdf_security() {
		/* Security settings cannot be applied to pdfa1b or pdfx1a formats */
		if ( strtolower( $this->settings['format'] ) == 'standard' && strtolower( $this->settings['security'] == 'Yes' ) ) {

			$password        = (isset( $this->settings['password'] ) ) ? 		$this->settings['password'] : 			'';
			$privileges      = (isset( $this->settings['privileges'] ) ) ? 		$this->settings['privileges'] : 		array();
			$master_password = (isset( $this->settings['master_password'] ) ) ? $this->settings['master_password'] : 	'';

			$this->mpdf->SetProtection( $privileges, $password, $master_password, 128 );
		}
	}

	/**
	 * Converts the 4.x settings array into a compatible 3.x settings array
	 * @param  Array $settings The 4.x settings to be converted
	 * @return Array           The 3.x compatible settings
	 * @since 4.0
	 * @todo
	 */
	protected function backwards_compat_conversion( $settings ) {
		
		$compat                   = array();
		$compat['premium']		  = ( isset($settings['advanced_template']) && $settings['advanced_template'] == 'Yes' ) ? true : false;
		$compat['rtl']            = ( isset($settings['rtl']) && $settings['rtl'] == 'Yes' ) ? true : false;
		$compat['dpi']            = ( isset($settings['image_dpi']) ) ? (int) $settings['image_dpi'] : 96;
		$compat['security']       = ( isset($settings['security']) && $settings['security'] == 'Yes' ) ? true : false;
		$compat['pdf_password']   = ( isset($settings['password']) ) ? $settings['password'] : '';
		$compat['pdf_privileges'] = ( isset($settings['privileges']) ) ? $settings['privileges'] : '';
		$compat['pdfa1b']         = ( isset($settings['format']) && $settings['format'] == 'PDFA1B' ) ? true : false;
		$compat['pdfx1a']         = ( isset($settings['format']) && $settings['format'] == 'PDFX1A' ) ? true : false;

		return $compat;
	}

	/**
	 * Converts the 4.x output to into a compatible 3.x type
	 * @param  String $type
	 * @return String
	 * @since 4.0
	 */
	protected function backwards_compat_output( $type ) {
		switch( strtolower( $type ) ) {
			case 'display':
				return 'view';
			break;

			case 'download':
				return 'download';
			break;

			default:
				return 'save';
			break;
		}
	}
}
