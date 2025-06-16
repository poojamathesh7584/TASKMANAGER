import React from 'react';
import { Task } from '../types/task';
import { BarChart3, TrendingUp, Target, Clock, Calendar, CheckCircle } from 'lucide-react';

interface StatsProps {
  tasks: Task[];
}

export const Stats: React.FC<StatsProps> = ({ tasks }) => {
  const totalTasks = tasks.length;
  const completedTasks = tasks.filter(task => task.completed).length;
  const pendingTasks = totalTasks - completedTasks;
  const completionRate = totalTasks > 0 ? (completedTasks / totalTasks) * 100 : 0;

  const overdueTasks = tasks.filter(task => 
    task.dueDate && new Date(task.dueDate) < new Date() && !task.completed
  ).length;

  const dueTodayTasks = tasks.filter(task => {
    if (!task.dueDate) return false;
    const today = new Date();
    const dueDate = new Date(task.dueDate);
    return dueDate.toDateString() === today.toDateString() && !task.completed;
  }).length;

  const priorityStats = {
    high: tasks.filter(task => task.priority === 'high' && !task.completed).length,
    medium: tasks.filter(task => task.priority === 'medium' && !task.completed).length,
    low: tasks.filter(task => task.priority === 'low' && !task.completed).length,
  };

  const categoryStats = tasks.reduce((acc, task) => {
    if (!task.completed) {
      acc[task.category] = (acc[task.category] || 0) + 1;
    }
    return acc;
  }, {} as Record<string, number>);

  const topCategories = Object.entries(categoryStats)
    .sort(([, a], [, b]) => b - a)
    .slice(0, 3);

  const weeklyCompletion = () => {
    const today = new Date();
    const oneWeekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
    
    return tasks.filter(task => 
      task.completed && 
      new Date(task.updatedAt) >= oneWeekAgo
    ).length;
  };

  return (
    <div className="bg-white/60 backdrop-blur-sm rounded-2xl p-6 mb-8 border border-gray-200">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-semibold text-gray-900 flex items-center">
          <BarChart3 className="w-5 h-5 mr-2" />
          Task Statistics
        </h2>
        <div className="text-sm text-gray-500">
          Updated {new Date().toLocaleDateString()}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {/* Completion Rate */}
        <div className="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-200">
          <div className="flex items-center justify-between mb-2">
            <div className="p-2 bg-green-100 rounded-lg">
              <Target className="w-4 h-4 text-green-600" />
            </div>
            <span className="text-2xl font-bold text-green-600">
              {completionRate.toFixed(0)}%
            </span>
          </div>
          <h3 className="text-sm font-medium text-green-800">Completion Rate</h3>
          <p className="text-xs text-green-600 mt-1">
            {completedTasks} of {totalTasks} tasks completed
          </p>
        </div>

        {/* Weekly Progress */}
        <div className="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200">
          <div className="flex items-center justify-between mb-2">
            <div className="p-2 bg-blue-100 rounded-lg">
              <TrendingUp className="w-4 h-4 text-blue-600" />
            </div>
            <span className="text-2xl font-bold text-blue-600">
              {weeklyCompletion()}
            </span>
          </div>
          <h3 className="text-sm font-medium text-blue-800">This Week</h3>
          <p className="text-xs text-blue-600 mt-1">
            Tasks completed in last 7 days
          </p>
        </div>

        {/* Due Today */}
        <div className="bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl p-4 border border-orange-200">
          <div className="flex items-center justify-between mb-2">
            <div className="p-2 bg-orange-100 rounded-lg">
              <Calendar className="w-4 h-4 text-orange-600" />
            </div>
            <span className="text-2xl font-bold text-orange-600">
              {dueTodayTasks}
            </span>
          </div>
          <h3 className="text-sm font-medium text-orange-800">Due Today</h3>
          <p className="text-xs text-orange-600 mt-1">
            Tasks due today
          </p>
        </div>

        {/* Overdue */}
        <div className="bg-gradient-to-br from-red-50 to-pink-50 rounded-xl p-4 border border-red-200">
          <div className="flex items-center justify-between mb-2">
            <div className="p-2 bg-red-100 rounded-lg">
              <Clock className="w-4 h-4 text-red-600" />
            </div>
            <span className="text-2xl font-bold text-red-600">
              {overdueTasks}
            </span>
          </div>
          <h3 className="text-sm font-medium text-red-800">Overdue</h3>
          <p className="text-xs text-red-600 mt-1">
            Tasks past due date
          </p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Priority Breakdown */}
        <div className="bg-gray-50 rounded-xl p-4">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Priority Breakdown</h3>
          <div className="space-y-3">
            <div className="flex items-center justify-between">
              <div className="flex items-center space-x-2">
                <div className="w-3 h-3 bg-red-500 rounded-full"></div>
                <span className="text-sm text-gray-700">High Priority</span>
              </div>
              <div className="flex items-center space-x-2">
                <span className="text-sm font-medium text-gray-900">{priorityStats.high}</span>
                <div className="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div 
                    className="h-full bg-red-500 transition-all duration-300"
                    style={{ width: `${pendingTasks > 0 ? (priorityStats.high / pendingTasks) * 100 : 0}%` }}
                  ></div>
                </div>
              </div>
            </div>
            <div className="flex items-center justify-between">
              <div className="flex items-center space-x-2">
                <div className="w-3 h-3 bg-yellow-500 rounded-full"></div>
                <span className="text-sm text-gray-700">Medium Priority</span>
              </div>
              <div className="flex items-center space-x-2">
                <span className="text-sm font-medium text-gray-900">{priorityStats.medium}</span>
                <div className="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div 
                    className="h-full bg-yellow-500 transition-all duration-300"
                    style={{ width: `${pendingTasks > 0 ? (priorityStats.medium / pendingTasks) * 100 : 0}%` }}
                  ></div>
                </div>
              </div>
            </div>
            <div className="flex items-center justify-between">
              <div className="flex items-center space-x-2">
                <div className="w-3 h-3 bg-blue-500 rounded-full"></div>
                <span className="text-sm text-gray-700">Low Priority</span>
              </div>
              <div className="flex items-center space-x-2">
                <span className="text-sm font-medium text-gray-900">{priorityStats.low}</span>
                <div className="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div 
                    className="h-full bg-blue-500 transition-all duration-300"
                    style={{ width: `${pendingTasks > 0 ? (priorityStats.low / pendingTasks) * 100 : 0}%` }}
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Top Categories */}
        <div className="bg-gray-50 rounded-xl p-4">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Active Categories</h3>
          <div className="space-y-3">
            {topCategories.length > 0 ? (
              topCategories.map(([category, count], index) => (
                <div key={category} className="flex items-center justify-between">
                  <div className="flex items-center space-x-2">
                    <div className={`w-3 h-3 rounded-full ${
                      index === 0 ? 'bg-purple-500' :
                      index === 1 ? 'bg-indigo-500' : 'bg-cyan-500'
                    }`}></div>
                    <span className="text-sm text-gray-700">{category}</span>
                  </div>
                  <div className="flex items-center space-x-2">
                    <span className="text-sm font-medium text-gray-900">{count}</span>
                    <div className="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                      <div 
                        className={`h-full transition-all duration-300 ${
                          index === 0 ? 'bg-purple-500' :
                          index === 1 ? 'bg-indigo-500' : 'bg-cyan-500'
                        }`}
                        style={{ width: `${pendingTasks > 0 ? (count / pendingTasks) * 100 : 0}%` }}
                      ></div>
                    </div>
                  </div>
                </div>
              ))
            ) : (
              <div className="text-center py-4">
                <CheckCircle className="w-8 h-8 text-gray-400 mx-auto mb-2" />
                <p className="text-sm text-gray-500">All tasks completed!</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};