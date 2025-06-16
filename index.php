<?php
session_start();

// Initialize tasks array in session if not exists
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (!empty($_POST['title'])) {
                    $task = [
                        'id' => uniqid(),
                        'title' => htmlspecialchars($_POST['title']),
                        'description' => htmlspecialchars($_POST['description'] ?? ''),
                        'priority' => $_POST['priority'] ?? 'medium',
                        'category' => $_POST['category'] ?? 'Personal',
                        'due_date' => $_POST['due_date'] ?? '',
                        'completed' => false,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    array_unshift($_SESSION['tasks'], $task);
                }
                break;
                
            case 'toggle':
                $taskId = $_POST['task_id'];
                foreach ($_SESSION['tasks'] as &$task) {
                    if ($task['id'] === $taskId) {
                        $task['completed'] = !$task['completed'];
                        break;
                    }
                }
                break;
                
            case 'delete':
                $taskId = $_POST['task_id'];
                $_SESSION['tasks'] = array_filter($_SESSION['tasks'], function($task) use ($taskId) {
                    return $task['id'] !== $taskId;
                });
                $_SESSION['tasks'] = array_values($_SESSION['tasks']); // Re-index array
                break;
                
            case 'edit':
                $taskId = $_POST['task_id'];
                foreach ($_SESSION['tasks'] as &$task) {
                    if ($task['id'] === $taskId) {
                        $task['title'] = htmlspecialchars($_POST['title']);
                        $task['description'] = htmlspecialchars($_POST['description'] ?? '');
                        $task['priority'] = $_POST['priority'] ?? 'medium';
                        $task['category'] = $_POST['category'] ?? 'Personal';
                        $task['due_date'] = $_POST['due_date'] ?? '';
                        break;
                    }
                }
                break;
        }
    }
    
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get filter parameters
$searchTerm = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';
$showCompleted = isset($_GET['show_completed']) ? $_GET['show_completed'] === '1' : true;

// Filter tasks
$filteredTasks = $_SESSION['tasks'];

if (!empty($searchTerm)) {
    $filteredTasks = array_filter($filteredTasks, function($task) use ($searchTerm) {
        return stripos($task['title'], $searchTerm) !== false || 
               stripos($task['description'], $searchTerm) !== false;
    });
}

if (!empty($categoryFilter)) {
    $filteredTasks = array_filter($filteredTasks, function($task) use ($categoryFilter) {
        return $task['category'] === $categoryFilter;
    });
}

if (!empty($priorityFilter)) {
    $filteredTasks = array_filter($filteredTasks, function($task) use ($priorityFilter) {
        return $task['priority'] === $priorityFilter;
    });
}

if (!$showCompleted) {
    $filteredTasks = array_filter($filteredTasks, function($task) {
        return !$task['completed'];
    });
}

// Calculate statistics
$totalTasks = count($_SESSION['tasks']);
$completedTasks = count(array_filter($_SESSION['tasks'], function($task) {
    return $task['completed'];
}));
$overdueTasks = count(array_filter($_SESSION['tasks'], function($task) {
    return !$task['completed'] && !empty($task['due_date']) && $task['due_date'] < date('Y-m-d');
}));

$categories = ['Personal', 'Work', 'Health', 'Learning', 'Shopping', 'Finance', 'Travel', 'Home'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Beautiful Task Manager</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .total { color: #3b82f6; }
        .completed { color: #10b981; }
        .overdue { color: #ef4444; }

        .controls {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .controls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #374151;
        }

        .form-control {
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.875rem;
        }

        .tasks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .task-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .task-card.completed {
            opacity: 0.7;
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .task-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .task-title.completed {
            text-decoration: line-through;
            color: #9ca3af;
        }

        .task-description {
            color: #6b7280;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .task-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .priority-high { background: #fee2e2; color: #dc2626; }
        .priority-medium { background: #fef3c7; color: #d97706; }
        .priority-low { background: #dbeafe; color: #2563eb; }

        .category-badge {
            background: #f3f4f6;
            color: #374151;
        }

        .due-date {
            background: #ecfdf5;
            color: #059669;
        }

        .due-date.overdue {
            background: #fee2e2;
            color: #dc2626;
        }

        .task-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .checkbox {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #9ca3af;
        }

        .form-grid {
            display: grid;
            gap: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #374151;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .controls-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .tasks-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>TaskFlow</h1>
            <p>Manage your tasks efficiently with style</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number total"><?php echo $totalTasks; ?></div>
                <div class="stat-label">Total Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-number completed"><?php echo $completedTasks; ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number overdue"><?php echo $overdueTasks; ?></div>
                <div class="stat-label">Overdue</div>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls">
            <form method="GET" class="controls-grid">
                <div class="form-group">
                    <label for="search">Search Tasks</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Search by title or description..." 
                           value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category; ?>" 
                                    <?php echo $categoryFilter === $category ? 'selected' : ''; ?>>
                                <?php echo $category; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" class="form-control">
                        <option value="">All Priorities</option>
                        <option value="high" <?php echo $priorityFilter === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $priorityFilter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $priorityFilter === 'low' ? 'selected' : ''; ?>>Low</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="openModal()">Add Task</button>
                    </div>
                </div>
            </form>
            
            <div style="margin-top: 15px;">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" class="checkbox" 
                           <?php echo $showCompleted ? 'checked' : ''; ?>
                           onchange="toggleCompleted(this)">
                    Show completed tasks
                </label>
            </div>
        </div>

        <!-- Tasks -->
        <?php if (empty($filteredTasks)): ?>
            <div class="empty-state">
                <h3>No tasks found</h3>
                <p><?php echo empty($_SESSION['tasks']) ? 'Get started by creating your first task!' : 'Try adjusting your filters to see more tasks.'; ?></p>
                <?php if (empty($_SESSION['tasks'])): ?>
                    <button class="btn btn-primary" onclick="openModal()">Create Your First Task</button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="tasks-grid">
                <?php foreach ($filteredTasks as $task): ?>
                    <div class="task-card <?php echo $task['completed'] ? 'completed' : ''; ?>">
                        <div class="task-header">
                            <div>
                                <h3 class="task-title <?php echo $task['completed'] ? 'completed' : ''; ?>">
                                    <?php echo $task['title']; ?>
                                </h3>
                                <?php if (!empty($task['description'])): ?>
                                    <p class="task-description"><?php echo $task['description']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="task-meta">
                            <span class="badge priority-<?php echo $task['priority']; ?>">
                                <?php echo ucfirst($task['priority']); ?>
                            </span>
                            <span class="badge category-badge">
                                <?php echo $task['category']; ?>
                            </span>
                            <?php if (!empty($task['due_date'])): ?>
                                <?php 
                                $isOverdue = !$task['completed'] && $task['due_date'] < date('Y-m-d');
                                ?>
                                <span class="badge due-date <?php echo $isOverdue ? 'overdue' : ''; ?>">
                                    <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                                    <?php echo $isOverdue ? ' (Overdue)' : ''; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="task-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" class="btn btn-secondary btn-sm">
                                    <?php echo $task['completed'] ? 'Mark Incomplete' : 'Mark Complete'; ?>
                                </button>
                            </form>
                            
                            <button class="btn btn-secondary btn-sm" 
                                    onclick="editTask('<?php echo $task['id']; ?>')">
                                Edit
                            </button>
                            
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('Are you sure you want to delete this task?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add/Edit Task Modal -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add New Task</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            
            <form method="POST" id="taskForm" class="form-grid">
                <input type="hidden" name="action" value="add" id="formAction">
                <input type="hidden" name="task_id" value="" id="taskId">
                
                <div class="form-group">
                    <label for="title">Task Title *</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           placeholder="Enter task title..." required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" 
                              placeholder="Add task description..." rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority" class="form-control">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category; ?>" 
                                        <?php echo $category === 'Personal' ? 'selected' : ''; ?>>
                                    <?php echo $category; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" class="form-control">
                </div>
                
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Add Task</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Task data for JavaScript
        const tasks = <?php echo json_encode($_SESSION['tasks']); ?>;
        
        function openModal() {
            document.getElementById('taskModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Add New Task';
            document.getElementById('formAction').value = 'add';
            document.getElementById('submitBtn').textContent = 'Add Task';
            document.getElementById('taskForm').reset();
            document.getElementById('taskId').value = '';
        }
        
        function closeModal() {
            document.getElementById('taskModal').classList.remove('active');
        }
        
        function editTask(taskId) {
            const task = tasks.find(t => t.id === taskId);
            if (!task) return;
            
            document.getElementById('taskModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Edit Task';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('submitBtn').textContent = 'Update Task';
            document.getElementById('taskId').value = taskId;
            
            document.getElementById('title').value = task.title;
            document.getElementById('description').value = task.description || '';
            document.getElementById('priority').value = task.priority;
            document.getElementById('category').value = task.category;
            document.getElementById('due_date').value = task.due_date || '';
        }
        
        function toggleCompleted(checkbox) {
            const url = new URL(window.location);
            if (checkbox.checked) {
                url.searchParams.set('show_completed', '1');
            } else {
                url.searchParams.set('show_completed', '0');
            }
            window.location.href = url.toString();
        }
        
        // Close modal when clicking outside
        document.getElementById('taskModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>