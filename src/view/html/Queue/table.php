<?php

/**
 * Queue Management Table
 *
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

?>

<div class="hr-divider"></div>

<div id="gfpdf-background-processing-status">
    <h3>
        <span><i class="fa fa-clock-o"></i> <?php esc_html_e( 'Background Processing', 'gravity-forms-pdf-extended' ); ?></span>
    </h3>

    <table class="widefat gfpdf_table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Key</th>
            <th>Date</th>
            <th>Status</th>
            <th>Queue</th>
            <th><a href="#">Refresh</a></th>
        </tr>
        </thead>

<!--		--><?php //foreach ( $args['queue_items'] as $group ): ?>
<!--            <tbody>-->
<!--			--><?php //foreach ( $group as $id => $task ): ?>
<!---->
<!--                <tr data-task-id="--><?//= esc_attr( $task['task_id'] ); ?><!--">-->
<!--                    <td>--><?//= $task['id']; ?><!--</td>-->
<!--                    <td>--><?//= $task['task_id']; ?><!--</td>-->
<!--                    <td>--><?//= $task['timestamp']; ?><!--</td>-->
<!--                    <td>--><?//= $task['status']; ?><!--</td>-->
<!--                    <td>--><?//= $task['queue'] ?><!--</td>-->
<!--                    <td>-->
<!--						--><?php //if ( ! $args['queue_status'] ): ?>
<!--							--><?php //if ( $id === 0 ): ?>
<!--                                <a href="#">Run queue</a> | <a href="#">Run task</a> |-->
<!--							--><?php //endif; ?>
<!--                            <a href="#" class="delete">Delete</a>-->
<!--						--><?php //endif; ?>
<!--                    </td>-->
<!--                </tr>-->
<!--			--><?php //endforeach; ?>
<!--            </tbody>-->
<!--		--><?php //endforeach; ?>
    </table>

    <div id="gfpdf-background-processing-actions">

    </div>

<!--	--><?php //if ( ! $args['queue_status'] ): ?>
<!--        <button id="gfpdf-background-process-run-all" class="button gfpdf-button button-primary" type="button">Run All Tasks</button>-->
<!--        <button id="gfpdf-background-process-force-run-all" class="button gfpdf-button" type="button">Force Run All Tasks</button>-->
<!--        <button id="gfpdf-background-process-delete-all" class="button gfpdf-button" type="button">Delete All Tasks</button>-->
<!--	--><?php //endif; ?>
</div>