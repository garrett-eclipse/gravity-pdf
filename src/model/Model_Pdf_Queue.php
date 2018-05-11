<?php

namespace GFPDF\Model;

use GFPDF\Helper\Helper_Abstract_Model;
use GFPDF\Helper\Helper_Pdf_Queue;

use Psr\Log\LoggerInterface;

/**
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2018, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    This file is part of Gravity PDF.

    Gravity PDF – Copyright (C) 2018, Blue Liquid Designs

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
 * Model_Pdf_Queue
 *
 * @since 5.0
 */
class Model_Pdf_Queue extends Helper_Abstract_Model {

	/**
	 * Holds our log class
	 *
	 * @var LoggerInterface
	 * @since 5.0
	 */
	protected $log;

	/**
	 * @var \GFPDF\Helper\Helper_Pdf_Queue
	 *
	 * @since 5.0
	 */
	protected $queue;

	public function __construct( Helper_Pdf_Queue $queue, LoggerInterface $log ) {
		/* Assign our internal variables */
		$this->queue = $queue;
		$this->log   = $log;
	}

	public function queue_management() {
		$controller = $this->getController();

		$queue_items = $this->get_queue_display_data();
		$controller->view->queue( [
			'queue_status' => $queue_items[0],
			'queue_items'  => $queue_items[1],
		] );
	}

	public function get_queue_display_data() {
		$queue_items = $this->queue->get_queued_items();
		$status      = $this->queue->is_process_running();
		$status_text = ( $status ) ? 'Processing' : 'Pending';

		$queue = [];
		foreach ( $queue_items as $queue_item ) {
			$queue_data = maybe_unserialize( $queue_item['option_value'] );
			foreach ( $queue_data['data'] as $commands ) {
				foreach ( $commands as $key => $command ) {
					$function_name = $this->get_queue_function_name( $command['func'] );
					$function_args = $this->get_queue_function_args( $function_name, $command['args'] );

					if ( isset( $command['retry'] ) ) {
						$status_text = 'Retrying';
					}

					$queue[] = [
						'id'         => $queue_item['option_id'],
						'key'        => $command['id'],
						'timestamp'  => $queue_data['timestamp'],
						'status'     => $status_text,
						'queue'      => "$function_name ($function_args)",
						'first_item' => $key === 0,
					];
				}
			}
		}

		return [ $status, $queue ];
	}

	public function get_queue_function_name( $function_name ) {
		$function_name = str_replace( '\GFPDF\Statics\Queue_Callbacks::', '', $function_name );
		$function_name = apply_filters( 'gfpdf_queue_display_function_name', $function_name );

		return $function_name;
	}

	public function get_queue_function_args( $function_name, $args ) {
		switch ( $function_name ) {
			case 'create_pdf':
				$args[0] = 'Entry: ' . $args[0];
				$args[1] = 'PDF: ' . $args[1];
			break;

			case 'send_notification':
				$args[0] = 'Form: ' . $args[0];
				$args[1] = 'Entry: ' . $args[1];
				$args[2] = 'Name: ' . $args[2]['name'];
			break;

			case 'cleanup_pdfs':
				$args[0] = 'Form: ' . $args[0];
				$args[1] = 'Entry: ' . $args[1];
			break;
		}

		$args = apply_filters( 'gfpdf_queue_display_args', $args, $function_name );

		return implode( ', ', $args );
	}

}