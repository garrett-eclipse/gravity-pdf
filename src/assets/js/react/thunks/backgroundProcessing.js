import {
  REFRESH_QUEUE_SUCCESS,
  REFRESH_QUEUE_FAILURE,
  RUN_BACKGROUND_PROCESS_ALL_SUCCESS,
  RUN_BACKGROUND_PROCESS_ALL_FAILURE,
  RUN_DELETE_ALL_SUCCESS,
  RUN_DELETE_ALL_FAILURE,
  RUN_TASK_SUCCESS,
  RUN_TASK_FAILURE,
  RUN_DELETE_TASK_SUCCESS,
  RUN_DELETE_TASK_FAILURE
} from '../actionTypes/backgroundProcessing'
import {
  doApiCall, emptyQueue
} from '../actions/backgroundProcessing'
import request from 'superagent'

/**
 * Our Background Processing Redux Thunks
 *
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

export function refreshQueueApiThunk () {
  return async (dispatch) => {
    dispatch(doApiCall())

    try {
      const response = await request
        .get(GFPDF.restUrl + 'background-process/')
        .set('X-WP-Nonce', GFPDF.restNonce)

      dispatch({
        type: REFRESH_QUEUE_SUCCESS,
        queue: response.body.queue,
        status: response.body.status
      })
    } catch (e) {
      const errorMessage = e.response.body.message || 'Could not retrieve background processing queue. Please try again.'
      dispatch({type: REFRESH_QUEUE_FAILURE, errorMessage})
    }
  }
}

export function runBackgroundProcessAllThunk () {
  return async (dispatch) => {
    dispatch(doApiCall())

    try {
      await request
        .get(GFPDF.restUrl + 'background-process/run/all')
        .set('X-WP-Nonce', GFPDF.restNonce)

      dispatch({
        type: RUN_BACKGROUND_PROCESS_ALL_SUCCESS,
        status: true,
        successMessage: 'All tasks now now being processed'
      })
    } catch (e) {
      let errorMessage = e.response.body.message || 'Could not begin background processing queue. Please try again.'

      if (e.response.body.code === 'queue_empty') {
        dispatch(emptyQueue())
        errorMessage = ''
      }

      dispatch({type: RUN_BACKGROUND_PROCESS_ALL_FAILURE, errorMessage})
    }
  }
}

export function runDeleteAllTasksThunk (task) {
  return async (dispatch) => {
    dispatch(doApiCall())

    try {
      await request
        .post(GFPDF.restUrl + 'background-process/delete')
        .set('X-WP-Nonce', GFPDF.restNonce)
        .send(task)

      dispatch({
        type: RUN_DELETE_ALL_SUCCESS,
        status: true,
        successMessage: 'All Tasks Deleted'
      })
    } catch (e) {
      let errorMessage = e.response.body.message || 'Could not delete all tasks from the queue. Please try again.'
      dispatch({type: RUN_DELETE_ALL_FAILURE, errorMessage})
    }
  }
}

export function runDeleteTaskThunk (task) {
  return async (dispatch) => {
    dispatch(doApiCall(task))

    try {
      await request
        .post(GFPDF.restUrl + 'background-process/delete/task')
        .set('X-WP-Nonce', GFPDF.restNonce)
        .send(task)

      dispatch({
        type: RUN_DELETE_TASK_SUCCESS,
        successMessage: 'Task Deleted',
        task
      })
    } catch (e) {
      let errorMessage = e.response.body.message || 'Could not delete task from the queue. Please try again.'
      dispatch({type: RUN_DELETE_TASK_FAILURE, errorMessage})
    }
  }
}

export function runTaskThunk (task) {
  return async (dispatch) => {
    dispatch(doApiCall(task))

    try {
      await request
        .post(GFPDF.restUrl + 'background-process/run/task')
        .set('X-WP-Nonce', GFPDF.restNonce)
        .send(task)

      dispatch({
        type: RUN_TASK_SUCCESS,
        successMessage: 'Task successfully run',
        task
      })
    } catch (e) {
      let errorMessage = e.response.body.message || 'Could not run task from the queue. Please try again.'
      dispatch({type: RUN_TASK_FAILURE, errorMessage})
    }
  }
}