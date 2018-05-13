import React from 'react'
import { connect } from 'react-redux'
import ActionButtons from './ActionButtons'
import { refreshQueueApi } from '../../thunks/backgroundProcessing'

import Spinner from '../Spinner'
import { addToConsole } from '../../actions/coreFonts'

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
 * Handles the grunt work for our Core Font downloader (API calls, display, state ect)
 *
 * @since 5.0
 */
export class Container extends React.Component {

  /**
   * When new props are received we'll check if the fonts should be downloaded
   *
   * @param nextProps
   *
   * @since 5.0
   */
  componentWillReceiveProps (nextProps) {

  }

  /**
   * When the component is first mounted we'll check if the fonts should be downloaded
   *
   * @since 5.0
   */
  componentDidMount () {
    this.props.refreshQueue()
  }

  /**
   * Renders our Core Font downloader UI
   *
   * @returns {XML}
   *
   * @since 5.0
   */
  render () {
    return (
      <>
        <table className="widefat gfpdf_table">
          <thead>
          <tr>
            <th>ID</th>
            <th>Key</th>
            <th>Date</th>
            <th>Status</th>
            <th>Queue</th>
            <th></th>
          </tr>
          </thead>

          <tbody>
          <tr>
            <td colSpan="6">Loading...</td>
          </tr>
          </tbody>
        </table>

        <ActionButtons />
      </>
    )
  }
}

const mapStateToProps = (state) => {
  return {

  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    refreshQueue: () => {
      dispatch(refreshQueueApi())
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(Container)