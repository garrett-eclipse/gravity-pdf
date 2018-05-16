import React from 'react'
import { connect } from 'react-redux'
import DeleteTask from './DeleteTask'
import RunTask from './RunTask'

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
export class QueueRows extends React.Component {
  render () {
    return this.props.queue.map((group, groupIndex) => {
      const groupKey = 'gfpdf-background-process-group-' + groupIndex
      return (
        <tbody key={groupKey}>
        {group.map((task, taskIndex) => {
          const taskKey = 'gfpdf-background-process-task-' + groupIndex + '-' + taskIndex
          const taskClass = task === this.props.currentTask ? 'current-task' : ''
          return (
            <tr key={taskKey} className={taskClass}>
              <td>{task.id}</td>
              <td>{task.option_id}</td>
              <td>{task.timestamp}</td>
              <td>{task.status}</td>
              <td>{task.queue}</td>
              <td>
                {taskIndex === 0 ? <span><a href="#">Run queue</a> | <RunTask task={task} /> | </span> : null}
                <DeleteTask task={task} />
              </td>
            </tr>
          )
        })}
        </tbody>
      )
    })

  }
}

const mapStateToProps = (state) => {
  return {
    queue: state.backgroundProcessing.queue,
    currentTask: state.backgroundProcessing.currentTask
  }
}

const mapDispatchToProps = (dispatch) => {
  return {}
}

export default connect(mapStateToProps, mapDispatchToProps)(QueueRows)
