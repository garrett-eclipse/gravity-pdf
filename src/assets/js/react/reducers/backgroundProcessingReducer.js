import {
  DO_API_CALL, EMPTY_QUEUE, REFRESH_QUEUE_FAILURE, REFRESH_QUEUE_SUCCESS,
  RUN_BACKGROUND_PROCESS_ALL_FAILURE, RUN_BACKGROUND_PROCESS_ALL_SUCCESS, RUN_DELETE_ALL_FAILURE,
  RUN_DELETE_ALL_SUCCESS, RUN_DELETE_TASK_FAILURE,
  RUN_DELETE_TASK_SUCCESS
} from '../actionTypes/backgroundProcessing'

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
 * Setup the initial state of the "coreFont" portion of our Redux store
 *
 * @type {{console: {}, retry: Array}}
 *
 * @since 5.0
 */
export const initialState = {
  queue: [],
  status: false,
  loadingQueue: true,
  successMessage: '',
  errorMessage: '',
}

/**
 * The action coreFont reducer which updates our state
 *
 * @param state The current state of our template store
 * @param action The Redux action details being triggered
 *
 * @returns {*} State (whether updated or note)
 *
 * @since 5.0
 */
export default function (state = initialState, action) {
  switch (action.type) {

    /**
     * @since 5.0
     */
    case EMPTY_QUEUE:
      return {
        ...state,
        queue: [],
      }

    case DO_API_CALL:
      return {
        ...state,
        loadingQueue: true,
        status: false,
        successMessage: '',
        errorMessage: '',
      }

    case REFRESH_QUEUE_SUCCESS:
      return {
        ...state,
        loadingQueue: false,
        queue: action.queue,
        status: action.status,
        errorMessage: '',
      }

    case REFRESH_QUEUE_FAILURE:
    case RUN_BACKGROUND_PROCESS_ALL_FAILURE:
    case RUN_DELETE_TASK_FAILURE:
    case RUN_DELETE_ALL_FAILURE:
      return {
        ...state,
        loadingQueue: false,
        errorMessage: action.errorMessage,
      }

    case RUN_BACKGROUND_PROCESS_ALL_SUCCESS:
      return {
        ...state,
        queue: [...state.queue].map((group) => group.map((task) => task.status = 'Processing')),
        loadingQueue: false,
        status: action.status,
        successMessage: action.successMessage,
      }

    case RUN_DELETE_ALL_SUCCESS:
      return {
        ...state,
        queue: [],
        loadingQueue: false,
        successMessage: action.successMessage,
      }

    case RUN_DELETE_TASK_SUCCESS:
      let newQueue = state.queue.map(group => {
        return group.filter(task => task !== action.task)
      }).filter(group => group.length)

      console.log(newQueue)

      return {
        ...state,
        queue: newQueue,
        loadingQueue: false,
        successMessage: action.successMessage,
      }

  }

  /* None of these actions fired so return state */
  return state
}