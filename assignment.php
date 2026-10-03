<?php
// THIS MUST BE THE VERY FIRST LINE - NO SPACES, NO CHARACTERS BEFORE THIS
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'student') {
    header("Location: Login.html");
    exit();
}

$student_name = $_SESSION['student_name'];
$student_id = $_SESSION['user_id']; // This should be the stID from student table
$profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'human.png';

// Database connection function
function getDBConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "skill_pro_institute";
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        return null;
    }
}

// Handle API requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_assignments':
            getStudentAssignments($student_id);
            break;
        case 'get_exams':
            getStudentExams($student_id);
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    exit();
}

function getStudentAssignments($student_id) {
    $conn = getDBConnection();
    if (!$conn) {
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    try {
        // Check if enrollments table exists
        $enrollmentTableExists = false;
        try {
            $checkTable = $conn->query("SELECT 1 FROM enrollments LIMIT 1");
            $enrollmentTableExists = true;
        } catch (Exception $e) {
            $enrollmentTableExists = false;
        }
        
        if ($enrollmentTableExists) {
            // Query with enrollment filter - only show assignments for student's enrolled courses
            $sql = "
                SELECT 
                    a.assignment_id,
                    a.assignment_title,
                    a.assignment_description,
                    a.assignment_file,
                    a.due_date,
                    a.max_marks,
                    c.course_Name,
                    m.module_Name
                FROM assignments a
                INNER JOIN course c ON a.course_ID = c.course_ID
                INNER JOIN module m ON a.module_ID = m.module_ID
                INNER JOIN enrollments e ON a.course_ID = e.course_ID
                WHERE e.student_id = :student_id
                ORDER BY a.due_date ASC
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':student_id', $student_id);
        } else {
            // Fallback: Get assignments based on student's program from student table
            $sql = "
                SELECT 
                    a.assignment_id,
                    a.assignment_title,
                    a.assignment_description,
                    a.assignment_file,
                    a.due_date,
                    a.max_marks,
                    c.course_Name,
                    m.module_Name
                FROM assignments a
                INNER JOIN course c ON a.course_ID = c.course_ID
                INNER JOIN module m ON a.module_ID = m.module_ID
                INNER JOIN student s ON c.course_ID = s.program_id
                WHERE s.stID = :student_id
                ORDER BY a.due_date ASC
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':student_id', $student_id);
        }
        
        $stmt->execute();
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($assignments);
        
    } catch(PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function getStudentExams($student_id) {
    $conn = getDBConnection();
    if (!$conn) {
        echo json_encode(['error' => 'Database connection failed']);
        return;
    }
    
    try {
        // Check if enrollments table exists
        $enrollmentTableExists = false;
        try {
            $checkTable = $conn->query("SELECT 1 FROM enrollments LIMIT 1");
            $enrollmentTableExists = true;
        } catch (Exception $e) {
            $enrollmentTableExists = false;
        }
        
        if ($enrollmentTableExists) {
            // Query with enrollment filter - only show exams for student's enrolled courses
            $sql = "
                SELECT 
                    e.exam_id,
                    e.exam_title,
                    e.exam_description,
                    e.exam_file,
                    e.exam_date,
                    e.duration,
                    e.total_marks,
                    c.course_Name,
                    m.module_Name
                FROM exams e
                INNER JOIN course c ON e.course_ID = c.course_ID
                INNER JOIN module m ON e.module_ID = m.module_ID
                INNER JOIN enrollments en ON e.course_ID = en.course_ID
                WHERE en.student_id = :student_id
                ORDER BY e.exam_date ASC
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':student_id', $student_id);
        } else {
            // Fallback: Get exams based on student's program from student table
            $sql = "
                SELECT 
                    e.exam_id,
                    e.exam_title,
                    e.exam_description,
                    e.exam_file,
                    e.exam_date,
                    e.duration,
                    e.total_marks,
                    c.course_Name,
                    m.module_Name
                FROM exams e
                INNER JOIN course c ON e.course_ID = c.course_ID
                INNER JOIN module m ON e.module_ID = m.module_ID
                INNER JOIN student s ON c.course_ID = s.program_id
                WHERE s.stID = :student_id
                ORDER BY e.exam_date ASC
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':student_id', $student_id);
        }
        
        $stmt->execute();
        $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($exams);
        
    } catch(PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Portal - Assignments & Exams</title>
    <link rel="stylesheet" href="student.css">
    <link rel="stylesheet" href="animation.css">
    <link rel="stylesheet" href="studentdashboard.css">

    <!-- icon link -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

     <style>
        :root {
            --primary-color: #34db53ff;
            --secondary-color: #29b96aff;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #34495e;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-title {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .student-info {
            color: var(--dark-color);
            font-size: 16px;
        }
        
        .tabs {
            display: flex;
            background: white;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tab {
            padding: 12px 30px;
            cursor: pointer;
            border-radius: 5px;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .tab.active {
            background: var(--primary-color);
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f2f6;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .count-badge {
            background: var(--primary-color);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 14px;
        }
        
        .assignment-item, .exam-item {
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .assignment-item:hover, .exam-item:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }
        
        .item-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .item-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 14px;
            color: #666;
        }
        
        .item-description {
            margin-bottom: 15px;
            color: #555;
            line-height: 1.5;
        }
        
        .download-btn {
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .download-btn:hover {
            background: var(--secondary-color);
        }
        
        .due-date, .exam-date {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .urgent {
            border-left: 4px solid var(--danger-color);
        }
        
        .upcoming {
            border-left: 4px solid var(--warning-color);
        }
        
        .normal {
            border-left: 4px solid var(--success-color);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .error-state {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }
        
        .retry-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .retry-btn:hover {
            background: var(--secondary-color);
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
        }

        .status-overdue {
            background: var(--danger-color);
            color: white;
        }

        .status-due-soon {
            background: var(--warning-color);
            color: white;
        }

        .status-upcoming {
            background: var(--success-color);
            color: white;
        }

        @media (max-width: 768px) {
            .item-meta {
                grid-template-columns: 1fr;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab {
                margin-bottom: 5px;
                text-align: center;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>

</head>
<body>

<header data-aos="zoom-in-down" data-aos-delay="350" data-aos-duration="1000">
    <div class="left">
        <h2>ASSIGNMENTS & EXAMS</h2>
    </div>
    <div class="right">
        <ul>
            <li> <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;"> </li>
            <li> <?php echo htmlspecialchars($student_name); ?> </li>
        </ul>
    </div>
</header>

<div class="container" data-aos="zoom-in-down" data-aos-delay="450" data-aos-duration="1000">
    <div class="container">
        <div class="header">
            <h1 class="page-title">ASSIGNMENTS & EXAMS</h1>
            <div class="student-info">
                Student ID: <strong id="studentId"><?php echo htmlspecialchars($student_id); ?></strong> | 
                Welcome back, <?php echo htmlspecialchars($student_name); ?>!
            </div>
        </div>
        
        <div class="alert alert-info">
            📚 View your assignments and exams for enrolled courses only.
        </div>
        
        <div class="tabs">
            <div class="tab active" data-tab="assignments">Assignments</div>
            <div class="tab" data-tab="exams">Exams</div>
        </div>
        
        <!-- Assignments Tab -->
        <div class="tab-content active" id="assignmentsTab">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">My Assignments</h2>
                    <span class="count-badge" id="assignmentsCount">Loading...</span>
                </div>
                <div id="assignmentsList">
                    <div class="loading">Loading assignments...</div>
                </div>
            </div>
        </div>
        
        <!-- Exams Tab -->
        <div class="tab-content" id="examsTab">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">My Exams</h2>
                    <span class="count-badge" id="examsCount">Loading...</span>
                </div>
                <div id="examsList">
                    <div class="loading">Loading exams...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Configuration - Use the actual student ID from PHP session
    const studentId = '<?php echo $student_id; ?>';
    const currentPage = '<?php echo basename($_SERVER['PHP_SELF']); ?>';
    
    // DOM Elements
    const assignmentsList = document.getElementById('assignmentsList');
    const examsList = document.getElementById('examsList');
    const assignmentsCount = document.getElementById('assignmentsCount');
    const examsCount = document.getElementById('examsCount');

    // Initialize the page
    async function init() {
        await loadAssignments();
        await loadExams();
        setupEventListeners();
    }

    // Load assignments for student
    async function loadAssignments() {
        try {
            assignmentsList.innerHTML = '<div class="loading">Loading assignments...</div>';
            
            const response = await fetch(`${currentPage}?action=get_assignments&student_id=${studentId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Check if response contains error
            if (data.error) {
                throw new Error(data.error);
            }
            
            renderAssignments(data);
        } catch (error) {
            console.error('Error loading assignments:', error);
            showError(assignmentsList, 'assignments', error);
            assignmentsCount.textContent = 'Error';
        }
    }

    // Load exams for student
    async function loadExams() {
        try {
            examsList.innerHTML = '<div class="loading">Loading exams...</div>';
            
            const response = await fetch(`${currentPage}?action=get_exams&student_id=${studentId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Check if response contains error
            if (data.error) {
                throw new Error(data.error);
            }
            
            renderExams(data);
        } catch (error) {
            console.error('Error loading exams:', error);
            showError(examsList, 'exams', error);
            examsCount.textContent = 'Error';
        }
    }

    // Show error state
    function showError(container, type, error) {
        container.innerHTML = `
            <div class="error-state">
                <div style="font-size: 24px; margin-bottom: 10px;">⚠️</div>
                <h3 style="margin: 0 0 10px 0;">Error Loading ${type.charAt(0).toUpperCase() + type.slice(1)}</h3>
                <p style="margin: 0 0 15px 0; color: #721c24;">${error.message}</p>
                <button class="retry-btn" onclick="load${type.charAt(0).toUpperCase() + type.slice(1)}()">
                    Retry Loading ${type.charAt(0).toUpperCase() + type.slice(1)}
                </button>
            </div>
        `;
    }

    // Render assignments list
    function renderAssignments(assignments) {
        assignmentsCount.textContent = `${assignments.length} Assignment${assignments.length !== 1 ? 's' : ''}`;
        
        if (assignments.length === 0) {
            assignmentsList.innerHTML = `
                <div class="empty-state">
                    <div>📝</div>
                    <h3>No Assignments</h3>
                    <p>You don't have any assignments for your enrolled courses.</p>
                </div>
            `;
            return;
        }

        let html = '';
        assignments.forEach(assignment => {
            const dueDate = new Date(assignment.due_date);
            const today = new Date();
            const timeDiff = dueDate - today;
            const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
            
            let priorityClass = 'normal';
            let statusBadge = '';
            
            if (daysDiff < 0) {
                priorityClass = 'urgent';
                statusBadge = '<span class="status-badge status-overdue">OVERDUE</span>';
            } else if (daysDiff <= 3) {
                priorityClass = 'urgent';
                statusBadge = '<span class="status-badge status-due-soon">DUE SOON</span>';
            } else if (daysDiff <= 7) {
                priorityClass = 'upcoming';
                statusBadge = '<span class="status-badge status-upcoming">UPCOMING</span>';
            }
            
            // Improved file handling
            const hasFile = assignment.assignment_file && 
                           assignment.assignment_file !== 'NULL' && 
                           assignment.assignment_file !== 'null' && 
                           assignment.assignment_file.trim() !== '';
            
            const fileDisplay = hasFile 
                ? `<a href="${assignment.assignment_file}" class="download-btn" download>
                      📎 Download Assignment File
                   </a>`
                : '<span style="color: #666; font-style: italic;">No file attached</span>';
            
            html += `
                <div class="assignment-item ${priorityClass}">
                    <div class="item-title">
                        ${assignment.assignment_title} 
                        ${statusBadge}
                    </div>
                    <div class="item-meta">
                        <div><strong>Course:</strong> ${assignment.course_Name}</div>
                        <div><strong>Module:</strong> ${assignment.module_Name}</div>
                        <div><strong>Max Marks:</strong> ${assignment.max_marks}</div>
                        <div class="due-date"><strong>Due Date:</strong> ${formatDate(assignment.due_date)}</div>
                    </div>
                    ${assignment.assignment_description ? `
                        <div class="item-description">${assignment.assignment_description}</div>
                    ` : ''}
                    ${fileDisplay}
                </div>
            `;
        });
        
        assignmentsList.innerHTML = html;
    }

    // Render exams list
    function renderExams(exams) {
        examsCount.textContent = `${exams.length} Exam${exams.length !== 1 ? 's' : ''}`;
        
        if (exams.length === 0) {
            examsList.innerHTML = `
                <div class="empty-state">
                    <div>📚</div>
                    <h3>No Exams</h3>
                    <p>You don't have any exams scheduled for your enrolled courses.</p>
                </div>
            `;
            return;
        }

        let html = '';
        exams.forEach(exam => {
            const examDate = new Date(exam.exam_date);
            const today = new Date();
            const timeDiff = examDate - today;
            const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
            
            let priorityClass = 'normal';
            let statusBadge = '';
            
            if (daysDiff <= 7) {
                priorityClass = 'urgent';
                statusBadge = '<span class="status-badge status-due-soon">SOON</span>';
            } else if (daysDiff <= 14) {
                priorityClass = 'upcoming';
                statusBadge = '<span class="status-badge status-upcoming">UPCOMING</span>';
            }
            
            // Improved file handling for exams
            const hasFile = exam.exam_file && 
                           exam.exam_file !== 'NULL' && 
                           exam.exam_file !== 'null' && 
                           exam.exam_file.trim() !== '';
            
            const fileDisplay = hasFile 
                ? `<a href="${exam.exam_file}" class="download-btn" download>
                      📎 Download Exam Paper
                   </a>`
                : '<span style="color: #666; font-style: italic;">No exam paper available</span>';
            
            html += `
                <div class="exam-item ${priorityClass}">
                    <div class="item-title">
                        ${exam.exam_title} 
                        ${statusBadge}
                    </div>
                    <div class="item-meta">
                        <div><strong>Course:</strong> ${exam.course_Name}</div>
                        <div><strong>Module:</strong> ${exam.module_Name}</div>
                        <div><strong>Duration:</strong> ${exam.duration} minutes</div>
                        <div><strong>Total Marks:</strong> ${exam.total_marks}</div>
                        <div class="exam-date"><strong>Exam Date:</strong> ${formatDateTime(exam.exam_date)}</div>
                    </div>
                    ${exam.exam_description ? `
                        <div class="item-description">${exam.exam_description}</div>
                    ` : ''}
                    ${fileDisplay}
                </div>
            `;
        });
        
        examsList.innerHTML = html;
    }

    // Helper functions
    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    function formatDateTime(dateTimeString) {
        const options = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return new Date(dateTimeString).toLocaleDateString(undefined, options);
    }

    function setupEventListeners() {
        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const tabId = tab.getAttribute('data-tab');
                switchTab(tabId);
            });
        });
    }

    function switchTab(tabId) {
        // Update active tab
        document.querySelectorAll('.tab').forEach(tab => {
            if (tab.getAttribute('data-tab') === tabId) {
                tab.classList.add('active');
            } else {
                tab.classList.remove('active');
            }
        });
        
        // Update active content
        document.querySelectorAll('.tab-content').forEach(content => {
            if (content.id === `${tabId}Tab`) {
                content.classList.add('active');
            } else {
                content.classList.remove('active');
            }
        });
    }

    // Make functions globally available for retry buttons
    window.loadAssignments = loadAssignments;
    window.loadExams = loadExams;

    // Initialize the application
    document.addEventListener('DOMContentLoaded', init);
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="Studentportal.js"></script>
<script src="animation.js"></script>

</body>
</html>