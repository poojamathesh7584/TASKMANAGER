import React from 'react';
import { Task } from '../types/task';
import { Calendar, Clock, Edit, Trash2, Flag } from 'lucide-react';

interface TaskCardProps {
  task: Task;
  onToggleComplete: (id: string) => void;
  onEdit: (task: Task) => void;
  onDelete: (id: string) => void;
}

export const TaskCard: React.FC<TaskCardProps> = ({
  task,
  onToggleComplete,
  onEdit,
  onDelete,
}) => {
  const isOverdue = task.dueDate && new Date(task.dueDate) < new Date() && !task.completed;
  
  const priorityColors = {
    high: 'bg-red-100 border-red-300 text-red-800',
    medium: 'bg-yellow-100 border-yellow-300 text-yellow-800',
    low: 'bg-blue-100 border-blue-300 text-blue-800',
  };

  const priorityIcons = {
    high: <Flag className="w-4 h-4" />,
    medium: <Flag className="w-4 h-4" />,
    low: <Flag className="w-4 h-4" />,
  };

  return (
    <div className={`
      bg-white rounded-xl shadow-sm border-2 transition-all duration-300 hover:shadow-lg hover:-translate-y-1
      ${task.completed ? 'opacity-60' : ''}
      ${isOverdue ? 'border-red-300 bg-red-50' : 'border-gray-200'}
    `}>
      <div className="p-6">
        <div className="flex items-start justify-between mb-4">
          <div className="flex items-center space-x-3">
            <button
              onClick={() => onToggleComplete(task.id)}
              className={`
                w-5 h-5 rounded-full border-2 transition-all duration-200 flex items-center justify-center
                ${task.completed 
                  ? 'bg-green-500 border-green-500' 
                  : 'border-gray-300 hover:border-green-400'
                }
              `}
            >
              {task.completed && (
                <svg className="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                </svg>
              )}
            </button>
            <div className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border ${priorityColors[task.priority]}`}>
              {priorityIcons[task.priority]}
              <span className="ml-1 capitalize">{task.priority}</span>
            </div>
          </div>
          <div className="flex items-center space-x-2">
            <button
              onClick={() => onEdit(task)}
              className="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
            >
              <Edit className="w-4 h-4" />
            </button>
            <button
              onClick={() => onDelete(task.id)}
              className="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
            >
              <Trash2 className="w-4 h-4" />
            </button>
          </div>
        </div>

        <h3 className={`text-lg font-semibold mb-2 ${task.completed ? 'line-through text-gray-500' : 'text-gray-900'}`}>
          {task.title}
        </h3>

        {task.description && (
          <p className={`text-sm mb-4 ${task.completed ? 'text-gray-400' : 'text-gray-600'}`}>
            {task.description}
          </p>
        )}

        <div className="flex items-center justify-between text-xs text-gray-500">
          <div className="flex items-center space-x-4">
            <span className="inline-flex items-center">
              <Calendar className="w-3 h-3 mr-1" />
              {task.category}
            </span>
            {task.dueDate && (
              <span className={`inline-flex items-center ${isOverdue ? 'text-red-600 font-medium' : ''}`}>
                <Clock className="w-3 h-3 mr-1" />
                {new Date(task.dueDate).toLocaleDateString()}
                {isOverdue && ' (Overdue)'}
              </span>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};