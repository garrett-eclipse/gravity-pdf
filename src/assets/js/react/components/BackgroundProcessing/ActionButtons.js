import React from 'react'
import { connect } from 'react-redux'
import { runBackgroundProcessAll } from '../../thunks/backgroundProcessing'

/**
 * @package     Gravity PDF
 * @copyright   Copyright (c) 2018, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
 */

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
 Found
 */

/**
 * @since 5.0
 */
export class ActionButtons extends React.Component {

  render () {
    return (
      <>
        <button onClick={this.props.runAllTasks} id="gfpdf-background-process-run-all"
                className="button gfpdf-button button-primary" type="button">
          Run All Tasks
        </button>

        <button id="gfpdf-background-process-force-run-all" className="button gfpdf-button" type="button">
          Force Run All Tasks
        </button>

        <button id="gfpdf-background-process-delete-all" className="button gfpdf-button" type="button">
          Delete All Tasks
        </button>
      </>
    )
  }
}

const mapStateToProps = (state) => {
  return {}
}

const mapDispatchToProps = (dispatch) => {
  return {
    runAllTasks: (e) => {
      e.preventDefault()
      dispatch(runBackgroundProcessAll())
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(ActionButtons)