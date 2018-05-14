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

<h3>
    <span><i class="fa fa-clock-o"></i> <?php esc_html_e( 'Monitor Background Processing', 'gravity-forms-pdf-extended' ); ?></span>
</h3>

<p>When a form is submitted, or when using the <a href="#">Resend Notifications feature</a>, Gravity PDF generates and emails all PDF documents in a background process. Tasks that are "Processing" are being actively run in the background. Tasks that are "Pending" are in the queue, but the background process is not currently running. <a href="#">Read more about background processing in Gravity PDF</a>.</p>

<div id="gfpdf-background-processing-status">
    Loading...

    <div id="gfpdf-background-processing-actions">

    </div>

</div>