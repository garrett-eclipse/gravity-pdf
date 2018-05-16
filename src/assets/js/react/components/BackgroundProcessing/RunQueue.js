import React from 'react'
import { connect } from 'react-redux'
import { runQueueThunk } from '../../thunks/backgroundProcessing'

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
export class RunQueue extends React.Component {

 runQueue = (e) => {
    e.preventDefault()

   this.props.runQueueApi(this.props.queue)
  }

  render () {
    return (
      <a href="#" onClick={this.runQueue}>Run queue</a>
    )
  }
}

const mapStateToProps = (state) => {
  return {}
}

const mapDispatchToProps = (dispatch) => {
  return {
    runQueueApi: (queue) => {
      dispatch(runQueueThunk(queue))
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(RunQueue)