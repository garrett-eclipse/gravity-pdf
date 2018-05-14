import React from 'react'
import { connect } from 'react-redux'
import { runDeleteTaskThunk } from '../../thunks/backgroundProcessing'

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
export class DeleteTask extends React.Component {

  deleteTask = (e) => {
    e.preventDefault()

    this.props.runDeleteTaskApi(this.props.task)
  }

  render () {
    return (
      <a href="#" onClick={this.deleteTask} className="delete">Delete</a>
    )
  }
}

const mapStateToProps = (state) => {
  return {}
}

const mapDispatchToProps = (dispatch) => {
  return {
    runDeleteTaskApi: (task) => {
      dispatch(runDeleteTaskThunk(task))
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(DeleteTask)