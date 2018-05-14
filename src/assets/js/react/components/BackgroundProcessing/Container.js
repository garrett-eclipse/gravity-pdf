import React from 'react'
import { connect } from 'react-redux'
import ActionButtons from './ActionButtons'
import { refreshQueueApiThunk } from '../../thunks/backgroundProcessing'

import FullWidthTableRow from './FullWidthTableRow'
import Refresh from './Refresh'
import QueueRows from './QueueRows'

import Spinner from '../Spinner'
import ShowMessage from '../ShowMessage'

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

  getTableRows (isLoading, queue) {
    if (isLoading && !queue.length) {
      return <FullWidthTableRow text="Loading..." />
    }

    if (!queue.length) {
      return <FullWidthTableRow text="Nothing in the queue" />
    }

    return <QueueRows />
  }

  /**
   * Renders our Core Font downloader UI
   *
   * @returns {XML}
   *
   * @since 5.0
   */
  render () {
    const {
      isLoading,
      queue,
      errorMessage,
      refreshQueue,
    } = this.props

    return (
      <>
        {errorMessage.length ? <ShowMessage text={errorMessage} error={true} dismissable={true} delay={6000} /> : null}
        <table className="widefat gfpdf_table">
          <thead>
          <tr>
            <th>ID</th>
            <th>Key</th>
            <th>Date</th>
            <th>Status</th>
            <th>Queue</th>
            <th style={{textAlign: 'right'}}>
              {isLoading ? <Spinner /> : <Refresh callbackFunction={refreshQueue} />}
            </th>
          </tr>
          </thead>

          {this.getTableRows(isLoading, queue)}
        </table>

        <ActionButtons />
      </>
    )
  }
}

const mapStateToProps = (state) => {
  return {
    isLoading: state.backgroundProcessing.loadingQueue,
    queue: state.backgroundProcessing.queue,
    status: state.backgroundProcessing.status,
    successMessage: state.backgroundProcessing.successMessage,
    errorMessage: state.backgroundProcessing.errorMessage,
  }
}

const mapDispatchToProps = (dispatch) => {
  return {
    refreshQueue: (e) => {
      if (typeof e !== 'undefined') {
        e.preventDefault()
      }
      dispatch(refreshQueueApiThunk())
    },
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(Container)