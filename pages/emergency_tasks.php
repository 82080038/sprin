<?php
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../includes/components/header.php';
require_once __DIR__ . '/../includes/components/footer.php';

// Auth check
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Emergency Task Management';
$activeMenu = 'emergency';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SPRIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../public/assets/css/main.css" rel="stylesheet">
    <style>
        .priority-critical { 
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white; 
            border: none;
        }
        .priority-high { 
            background: linear-gradient(135deg, #fd7e14, #e8680a);
            color: white; 
            border: none;
        }
        .priority-medium { 
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: black; 
            border: none;
        }
        .priority-low { 
            background: linear-gradient(135deg, #28a745, #218838);
            color: white; 
            border: none;
        }
        .status-pending { background-color: #ffc107; color: black; }
        .status-assigned { background-color: #17a2b8; color: white; }
        .status-in_progress { background-color: #007bff; color: white; }
        .status-completed { background-color: #28a745; color: white; }
        .status-cancelled { background-color: #6c757d; color: white; }
        .task-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .task-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .task-card.critical { border-left-color: #dc3545; }
        .task-card.high { border-left-color: #fd7e14; }
        .task-card.medium { border-left-color: #ffc107; }
        .task-card.low { border-left-color: #28a745; }
        .personnel-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        .personnel-card:hover {
            border-color: #007bff;
            box-shadow: 0 2px 4px rgba(0,123,255,0.2);
        }
        .wellness-excellent { color: #28a745; }
        .wellness-good { color: #17a2b8; }
        .wellness-fair { color: #ffc107; }
        .wellness-poor { color: #dc3545; }
        .conflict-indicator {
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        .stats-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/components/header.php'; ?>
    
    <main class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2><i class="bi bi-exclamation-triangle-fill text-danger"></i> Emergency Task Management</h2>
                <p class="text-muted">Manajemen tugas darurat dan penggantian personil untuk operasi khusus</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-list-task fs-1 text-primary"></i>
                        <h3 class="mt-2" id="totalTasks">-</h3>
                        <p class="mb-0">Total Tasks</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-history fs-1 text-warning"></i>
                        <h3 class="mt-2" id="pendingTasks">-</h3>
                        <p class="mb-0">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-person-check fs-1 text-info"></i>
                        <h3 class="mt-2" id="assignedTasks">-</h3>
                        <p class="mb-0">Assigned</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-circle fs-1 text-danger"></i>
                        <h3 class="mt-2" id="criticalTasks">-</h3>
                        <p class="mb-0">Critical</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <button class="btn btn-danger" onclick="showCreateTaskModal()">
                    <i class="bi bi-plus-circle"></i> Create Emergency Task
                </button>
                <button class="btn btn-info ms-2" onclick="showAvailablePersonnel()">
                    <i class="bi bi-people"></i> Find Available Personnel
                </button>
                <button class="btn btn-warning ms-2" onclick="checkConflicts()">
                    <i class="bi bi-shield-exclamation"></i> Check Conflicts
                </button>
            </div>
        </div>

        <!-- Task List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="bi bi-list-check"></i> Emergency Tasks</h5>
                        <div>
                            <select class="form-select form-select-sm d-inline-block w-auto" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="assigned">Assigned</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <select class="form-select form-select-sm d-inline-block w-auto ms-2" id="filterPriority">
                                <option value="">All Priority</option>
                                <option value="critical">Critical</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="taskList">
                            <div class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Task Modal -->
        <div class="modal fade" id="createTaskModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Emergency Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createTaskForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="taskName" class="form-label">Task Name *</label>
                                        <input type="text" class="form-control" id="taskName" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="taskType" class="form-label">Task Type *</label>
                                        <select class="form-select" id="taskType" required>
                                            <option value="">Select Type</option>
                                            <option value="urgent">Urgent</option>
                                            <option value="critical">Critical</option>
                                            <option value="emergency">Emergency</option>
                                            <option value="recall">Recall</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="priorityLevel" class="form-label">Priority Level *</label>
                                        <select class="form-select" id="priorityLevel" required>
                                            <option value="">Select Priority</option>
                                            <option value="critical">Critical</option>
                                            <option value="high">High</option>
                                            <option value="medium">Medium</option>
                                            <option value="low">Low</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="requiredPersonnel" class="form-label">Required Personnel *</label>
                                        <input type="number" class="form-control" id="requiredPersonnel" min="1" value="1" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="startTime" class="form-label">Start Time *</label>
                                        <input type="datetime-local" class="form-control" id="startTime" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="estimatedDuration" class="form-label">Duration (hours)</label>
                                        <input type="number" step="0.5" class="form-control" id="estimatedDuration" min="0.5">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="replacementReason" class="form-label">Replacement Reason (if applicable)</label>
                                <textarea class="form-control" id="replacementReason" rows="2"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="createEmergencyTask()">Create Task</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Personnel Modal -->
        <div class="modal fade" id="availablePersonnelModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Available Personnel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="personnelBagian" class="form-label">Filter by Bagian</label>
                                <select class="form-select" id="personnelBagian">
                                    <option value="">All Bagian</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="personnelDateTime" class="form-label">Date/Time</label>
                                <input type="datetime-local" class="form-control" id="personnelDateTime">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-primary" onclick="loadAvailablePersonnel()">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>
                        <div id="availablePersonnelList">
                            <div class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Details Modal -->
        <div class="modal fade" id="taskDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Task Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="taskDetailsContent">
                        <!-- Task details will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="assignPersonnelBtn" onclick="showAssignmentModal()">Assign Personnel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Modal -->
        <div class="modal fade" id="assignmentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Personnel to Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="assignmentForm">
                            <input type="hidden" id="assignTaskId">
                            <div class="mb-3">
                                <label for="assignPersonnel" class="form-label">Select Personnel *</label>
                                <select class="form-select" id="assignPersonnel" required>
                                    <option value="">Select Personnel</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="assignmentNotes" class="form-label">Assignment Notes</label>
                                <textarea class="form-control" id="assignmentNotes" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="assignPersonnelToTask()">Assign</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentTaskId = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadTaskList();
            loadBagianOptions();
            
            // Set up event listeners
            document.getElementById('filterStatus').addEventListener('change', loadTaskList);
            document.getElementById('filterPriority').addEventListener('change', loadTaskList);
            
            // Set default datetime
            const now = new Date();
            document.getElementById('startTime').value = now.toISOString().slice(0, 16);
            document.getElementById('personnelDateTime').value = now.toISOString().slice(0, 16);
            
            // Auto-refresh every 2 minutes
            setInterval(loadTaskList, 120000);
        });

        // Load task list
        function loadTaskList() {
            const status = document.getElementById('filterStatus').value;
            const priority = document.getElementById('filterPriority').value;
            
            let url = '../api/unified-api.php?resource=emergency&action=get_tasks';
            const params = new URLSearchParams();
            
            if (status) params.append('status', status);
            if (priority) params.append('priority', priority);
            
            if (params.toString()) {
                url += '&' + params.toString();
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayTaskList(data.data);
                        updateStatistics(data.data);
                    }
                })
                .catch(error => console.error('Error loading tasks:', error));
        }

        // Display task list
        function displayTaskList(tasks) {
            const container = document.getElementById('taskList');
            
            if (tasks.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">No emergency tasks found</div>';
                return;
            }
            
            container.innerHTML = tasks.map(task => createTaskCard(task)).join('');
        }

        // Create task card
        function createTaskCard(task) {
            const priorityClass = `priority-${task.priority_level}`;
            const statusClass = `status-${task.status}`;
            const cardClass = `task-card ${task.priority_level}`;
            
            return `
                <div class="${cardClass} p-3 mb-3" onclick="showTaskDetails(${task.id})">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="mb-0 me-2">${task.task_name}</h6>
                                <span class="badge ${priorityClass}">${task.priority_level.toUpperCase()}</span>
                                <span class="badge ${statusClass} ms-1">${task.status.replace('_', ' ').toUpperCase()}</span>
                                ${task.conflicts_count > 0 ? '<span class="conflict-indicator ms-1">!</span>' : ''}
                            </div>
                            <p class="mb-1"><strong>Code:</strong> ${task.task_code}</p>
                            <p class="mb-1"><strong>Type:</strong> ${task.task_type}</p>
                            <p class="mb-1"><strong>Location:</strong> ${task.location || 'Not specified'}</p>
                            <p class="mb-1"><strong>Required:</strong> ${task.required_personnel} personnel</p>
                            ${task.description ? `<p class="mb-1 text-muted">${task.description}</p>` : ''}
                        </div>
                        <div class="col-md-4 text-end">
                            <p class="mb-1"><strong>Start:</strong> ${formatDateTime(task.start_time)}</p>
                            <p class="mb-1"><strong>Duration:</strong> ${task.estimated_duration || 'N/A'} hours</p>
                            ${task.assigned_name ? `<p class="mb-1"><strong>Assigned:</strong> ${task.assigned_name}</p>` : ''}
                            <small class="text-muted">Created: ${formatDateTime(task.created_at)}</small>
                        </div>
                    </div>
                </div>
            `;
        }

        // Update statistics
        function updateStatistics(tasks) {
            document.getElementById('totalTasks').textContent = tasks.length;
            document.getElementById('pendingTasks').textContent = tasks.filter(t => t.status === 'pending').length;
            document.getElementById('assignedTasks').textContent = tasks.filter(t => t.status === 'assigned').length;
            document.getElementById('criticalTasks').textContent = tasks.filter(t => t.priority_level === 'critical').length;
        }

        // Load bagian options
        function loadBagianOptions() {
            fetch('../api/unified-api.php?resource=bagian&action=list')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const selects = ['personnelBagian', 'assignPersonnelBagian'];
                        selects.forEach(selectId => {
                            const select = document.getElementById(selectId);
                            if (select) {
                                data.data.forEach(b => {
                                    const option = document.createElement('option');
                                    option.value = b.id;
                                    option.textContent = b.nama_bagian;
                                    select.appendChild(option);
                                });
                            }
                        });
                    }
                })
                .catch(error => console.error('Error loading bagian:', error));
        }

        // Show create task modal
        function showCreateTaskModal() {
            const modal = new bootstrap.Modal(document.getElementById('createTaskModal'));
            modal.show();
        }

        // Create emergency task
        function createEmergencyTask() {
            const formData = new FormData(document.getElementById('createTaskForm'));
            
            fetch('../api/emergency_task_api.php?action=create_emergency_task', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Emergency task created successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('createTaskModal')).hide();
                        document.getElementById('createTaskForm').reset();
                        loadTaskList();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error creating task:', error);
                    alert('Error creating emergency task');
                });
        }

        // Show available personnel modal
        function showAvailablePersonnel() {
            const modal = new bootstrap.Modal(document.getElementById('availablePersonnelModal'));
            modal.show();
            loadAvailablePersonnel();
        }

        // Load available personnel
        function loadAvailablePersonnel() {
            const bagianId = document.getElementById('personnelBagian').value;
            const dateTime = document.getElementById('personnelDateTime').value;
            
            let url = '../api/unified-api.php?resource=emergency&action=get_available_personnel';
            const params = new URLSearchParams();
            
            if (bagianId) params.append('bagian_id', bagianId);
            if (dateTime) params.append('datetime', dateTime);
            
            if (params.toString()) {
                url += '&' + params.toString();
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayAvailablePersonnel(data.data);
                    }
                })
                .catch(error => console.error('Error loading personnel:', error));
        }

        // Display available personnel
        function displayAvailablePersonnel(personnel) {
            const container = document.getElementById('availablePersonnelList');
            
            if (personnel.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">No available personnel found</div>';
                return;
            }
            
            container.innerHTML = personnel.map(p => createPersonnelCard(p)).join('');
        }

        // Create personnel card
        function createPersonnelCard(person) {
            const wellnessClass = getWellnessClass(person.wellness_score || 100);
            const fatigueClass = getFatigueClass(person.fatigue_level || 'low');
            
            return `
                <div class="personnel-card">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="mb-1">${person.nama}</h6>
                            <p class="mb-1"><strong>NRP:</strong> ${person.nrp}</p>
                            <p class="mb-1"><strong>Bagian:</strong> ${person.nama_bagian || '-'}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="badge ${wellnessClass}">Wellness: ${person.wellness_score || 100}</span>
                            <span class="badge ${fatigueClass} ms-1">${person.fatigue_level || 'Low'}</span>
                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="selectPersonnel('${person.nrp}', '${person.nama}')">
                                <i class="bi bi-check"></i> Select
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // Get wellness class
        function getWellnessClass(score) {
            if (score >= 90) return 'wellness-excellent';
            if (score >= 75) return 'wellness-good';
            if (score >= 60) return 'wellness-fair';
            return 'wellness-poor';
        }

        // Get fatigue class
        function getFatigueClass(level) {
            switch(level) {
                case 'critical': return 'fatigue-critical';
                case 'high': return 'fatigue-high';
                case 'medium': return 'fatigue-medium';
                default: return 'fatigue-low';
            }
        }

        // Select personnel
        function selectPersonnel(nrp, nama) {
            document.getElementById('assignPersonnel').value = nrp;
            bootstrap.Modal.getInstance(document.getElementById('availablePersonnelModal')).hide();
            
            if (currentTaskId) {
                showAssignmentModal();
            }
        }

        // Show task details
        function showTaskDetails(taskId) {
            currentTaskId = taskId;
            
            fetch(`../api/emergency_task_api.php?action=get_task_details&task_id=${taskId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayTaskDetails(data.data);
                    }
                })
                .catch(error => console.error('Error loading task details:', error));
        }

        // Display task details
        function displayTaskDetails(task) {
            const content = document.getElementById('taskDetailsContent');
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Task Information</h6>
                        <p><strong>Code:</strong> ${task.task_code}</p>
                        <p><strong>Name:</strong> ${task.task_name}</p>
                        <p><strong>Type:</strong> ${task.task_type}</p>
                        <p><strong>Priority:</strong> <span class="badge priority-${task.priority_level}">${task.priority_level.toUpperCase()}</span></p>
                        <p><strong>Status:</strong> <span class="badge status-${task.status}">${task.status.replace('_', ' ').toUpperCase()}</span></p>
                        <p><strong>Location:</strong> ${task.location || 'Not specified'}</p>
                        <p><strong>Required Personnel:</strong> ${task.required_personnel}</p>
                        <p><strong>Duration:</strong> ${task.estimated_duration || 'N/A'} hours</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Timing</h6>
                        <p><strong>Start Time:</strong> ${formatDateTime(task.start_time)}</p>
                        <p><strong>End Time:</strong> ${task.end_time ? formatDateTime(task.end_time) : 'Not set'}</p>
                        <p><strong>Created:</strong> ${formatDateTime(task.created_at)}</p>
                        <p><strong>Created By:</strong> ${task.created_by}</p>
                        ${task.assigned_name ? `<p><strong>Assigned To:</strong> ${task.assigned_name}</p>` : ''}
                    </div>
                </div>
                ${task.description ? `<div class="mt-3"><h6>Description</h6><p>${task.description}</p></div>` : ''}
                ${task.replacement_reason ? `<div class="mt-3"><h6>Replacement Reason</h6><p>${task.replacement_reason}</p></div>` : ''}
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('taskDetailsModal'));
            modal.show();
        }

        // Show assignment modal
        function showAssignmentModal() {
            const modal = new bootstrap.Modal(document.getElementById('assignmentModal'));
            document.getElementById('assignTaskId').value = currentTaskId;
            modal.show();
        }

        // Assign personnel to task
        function assignPersonnelToTask() {
            const formData = new FormData(document.getElementById('assignmentForm'));
            
            fetch('../api/emergency_task_api.php?action=assign_personnel', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Personnel assigned successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('assignmentModal')).hide();
                        bootstrap.Modal.getInstance(document.getElementById('taskDetailsModal')).hide();
                        loadTaskList();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error assigning personnel:', error);
                    alert('Error assigning personnel');
                });
        }

        // Check conflicts
        function checkConflicts() {
            fetch('../api/emergency_task_api.php?action=check_conflicts')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayConflicts(data.data);
                    }
                })
                .catch(error => console.error('Error checking conflicts:', error));
        }

        // Display conflicts
        function displayConflicts(conflicts) {
            if (conflicts.length === 0) {
                alert('No conflicts detected!');
                return;
            }
            
            let conflictList = conflicts.map(c => 
                `Schedule ${c.schedule_id}: ${c.conflict_type} - ${c.resolution_status}`
            ).join('\n');
            
            alert(`Conflicts detected:\n${conflictList}`);
        }

        // Format datetime
        function formatDateTime(dateTime) {
            if (!dateTime) return 'N/A';
            return new Date(dateTime).toLocaleString();
        }
    </script>
</body>
</html>
