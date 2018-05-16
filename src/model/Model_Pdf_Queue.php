<?php

namespace GFPDF\Model;

use GFPDF\Helper\Helper_Abstract_Model;
use GFPDF\Helper\Helper_Pdf_Queue;
use GFPDF\Helper\Helper_Abstract_Form;

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

	/**
	 * Holds the abstracted Gravity Forms API specific to Gravity PDF
	 *
	 * @var \GFPDF\Helper\Helper_Abstract_Form
	 *
	 * @since 5.0
	 */
	protected $gform;

	public function __construct( Helper_Pdf_Queue $queue, LoggerInterface $log, Helper_Abstract_Form $gform ) {
		/* Assign our internal variables */
		$this->queue = $queue;
		$this->log   = $log;
		$this->gform = $gform;
	}

	public function get_queue_display_data() {
		$queue_items = $this->queue->get_queued_items();
		$status      = $this->queue->is_process_running();
		$status_text = ( $status ) ? 'Processing' : 'Pending';

		$queue = [];
		$i     = 0;
		foreach ( $queue_items as $queue_item ) {
			$queue_data = maybe_unserialize( $queue_item['option_value'] );
			foreach ( $queue_data['data'] as $queue_id => $commands ) {
				foreach ( $commands as $key => $command ) {
					$function_name = $this->get_queue_function_name( $command['func'] );
					$function_args = $this->get_queue_function_args( $function_name, $command['args'] );

					$queue[ $i ][] = [
						'id'        => $queue_item['option_id'],
						'option_id' => $queue_item['option_name'],
						'queue_id'  => $queue_id,
						'task_id'   => $command['id'],
						'timestamp' => $queue_data['timestamp'],
						'retry'     => ( isset( $command['retry'] ) ) ? $command['retry'] : 0,
						'status'    => ( isset( $command['retry'] ) ) ? $command['retry'] . ' Failure(s)' : $status_text,
						'queue'     => "$function_name ($function_args)",
					];
				}
				$i++;
			}
		}

		return [
			'status' => $status,
			'queue'  => $queue,
		];
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

	public function check_permission_edit_settings() {
		if ( ! $this->gform->has_capability( 'gravityforms_edit_settings' ) ) {
			return new \WP_Error( 'rest_forbidden', esc_html__( 'You do not have permission to access this endpoint.', 'gravity-forms-pdf-extended' ), [ 'status' => 401 ] );
		}

		return true;
	}

	public function get_background_process_all( \WP_REST_Request $request ) {
		return $this->get_queue_display_data();
	}

	public function run_background_process_all( \WP_REST_Request $request ) {
		if ( ! $this->queue->is_process_running() && ! $this->queue->is_queue_empty() ) {
			$begin_queue = $this->queue->dispatch();
			if ( is_wp_error( $begin_queue ) ) {
				return $begin_queue;
			}

			return json_encode( true );
		}
	}

	public function run_background_process_task( \WP_REST_Request $request ) {
		if ( $this->queue->is_process_running() || $this->queue->is_queue_empty() ) {
			return new \WP_Error( 'bad_request', [ 'status' => 400 ] );
		}

		$params    = $this->get_task_params( $request );
		$option_id = $params['option_id'];
		$queue_id  = $params['queue_id'];
		$task_id   = $params['task_id'];

		$queue = $new_queue = get_site_option( $option_id );

		if ( ! isset( $queue['data'][ $queue_id ] ) ) {
			return new \WP_Error( 'bad_request', 'Could not find queue item', [ 'status' => 400 ] );
		}

		foreach ( $queue['data'][ $queue_id ] as $key => $task ) {
			if ( $task['id'] === $task_id ) {
				$results = $this->queue->run_single_task( [ $task ] );

				if ( $results !== false ) {
					$new_queue['data'][ $queue_id ][ $key ] = $results[0];
				} else {
					unset( $new_queue['data'][ $queue_id ][ $key ] );
				}

				if ( count( $new_queue['data'] ) === 0 ) {
					$this->queue->delete( $option_id );
				} else {
					$this->queue->update( $option_id, $new_queue['data'] );
				}

				break;
			}
		}

		return $this->get_background_process_all( $request );
	}

	public function delete_background_processes_all( \WP_REST_Request $request ) {
		if ( $this->queue->is_process_running() || $this->queue->is_queue_empty() ) {
			return new \WP_Error( 'bad_request', [ 'status' => 400 ] );
		}

		if ( ! $this->queue->clear_queue( true ) ) {
			return new \WP_Error( 'update_failed', [ 'status' => 500 ] );
		}

		return json_encode( true );
	}

	public function delete_background_processes_task( \WP_REST_Request $request ) {
		$params    = $this->get_task_params( $request );
		$option_id = $params['option_id'];
		$queue_id  = $params['queue_id'];
		$task_id   = $params['task_id'];

		$queue = get_site_option( $option_id );

		if ( ! isset( $queue['data'][ $queue_id ] ) ) {
			return new \WP_Error( 'bad_request', 'Could not find queue item', [ 'status' => 400 ] );
		}

		$this->remove_task_from_queue( $queue, $option_id, $queue_id, $task_id );

		return $this->get_background_process_all( $request );
	}

	protected function get_task_params( \WP_REST_Request $request ) {
		$option_id = $request->get_param( 'option_id' );
		$queue_id  = $request->get_param( 'queue_id' );
		$task_id   = $request->get_param( 'task_id' );

		if ( $this->queue->is_process_running() || $this->queue->is_queue_empty() ) {
			return new \WP_Error( 'bad_request', [ 'status' => 400 ] );
		}

		foreach ( [ $option_id, $queue_id, $task_id ] as $item ) {
			if ( $item === null ) {
				return new \WP_Error( 'bad_request', [ 'status' => 400 ] );
			}
		}

		return [
			'option_id' => $option_id,
			'queue_id'  => $queue_id,
			'task_id'   => $task_id,
		];
	}

	protected function remove_task_from_queue( $queue, $option_id, $queue_id, $task_id ) {
		$new_queue = $queue;

		foreach ( $queue['data'][ $queue_id ] as $key => $task ) {
			if ( $task['id'] === $task_id ) {
				unset( $new_queue['data'][ $queue_id ][ $key ] );

				if ( count( $new_queue['data'][ $queue_id ] ) === 0 ) {
					unset( $new_queue['data'][ $queue_id ] );
				}
				break;
			}
		}

		if ( $queue !== $new_queue ) {
			if ( count( $new_queue['data'] ) === 0 ) {
				$this->queue->delete( $option_id );
			} else {
				$this->queue->update( $option_id, $new_queue['data'] );
			}
		}
	}
}