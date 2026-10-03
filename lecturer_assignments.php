<?php
session_start();

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: Login.html");
    exit();
}

$lecturer_id = $_SESSION['user_id'];
$lecturer_name = $_SESSION['lecturer_name'];

// Database connection
$host = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "skill_pro_institute";

$conn = mysqli_connect($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get lecturer's module from lecturers table
$lecturer_sql = "SELECT module FROM lecturers WHERE lecid = ?";
$lecturer_stmt = $conn->prepare($lecturer_sql);
$lecturer_stmt->bind_param("s", $lecturer_id);
$lecturer_stmt->execute();
$lecturer_result = $lecturer_stmt->get_result();
$lecturer_data = $lecturer_result->fetch_assoc();
$lecturer_stmt->close();

$lecturer_modules = [];
$submissions = [];

if ($lecturer_data && !empty($lecturer_data['module'])) {
    // Get module details
    $module_sql = "SELECT module_ID, module_Name, course_ID FROM module WHERE module_ID = ?";
    $module_stmt = $conn->prepare($module_sql);
    $module_stmt->bind_param("s", $lecturer_data['module']);
    $module_stmt->execute();
    $module_result = $module_stmt->get_result();
    $module_data = $module_result->fetch_assoc();
    $module_stmt->close();

    if ($module_data) {
        $lecturer_modules[] = [
            'module_ID' => $module_data['module_ID'],
            'module_Name' => $module_data['module_Name'],
            'course_ID' => $module_data['course_ID'],
            'course_Name' => '' // We'll get this separately
        ];

        // Get course name
        $course_sql = "SELECT course_Name FROM course WHERE course_ID = ?";
        $course_stmt = $conn->prepare($course_sql);
        $course_stmt->bind_param("s", $module_data['course_ID']);
        $course_stmt->execute();
        $course_result = $course_stmt->get_result();
        $course_data = $course_result->fetch_assoc();
        $course_stmt->close();

        if ($course_data) {
            $lecturer_modules[0]['course_Name'] = $course_data['course_Name'];
        }

        // Get assignment submissions for lecturer's module
        $submissions_sql = "
            SELECT s.*, st.first_name, st.last_name, st.stID, c.course_Name, m.module_Name
            FROM assignment_submissions s
            JOIN student st ON s.student_id = st.stID
            JOIN course c ON s.course_id = c.course_ID
            JOIN module m ON s.module_id = m.module_ID
            WHERE s.module_id = ?
            ORDER BY s.submitted_at DESC
        ";
        
        $submissions_stmt = $conn->prepare($submissions_sql);
        $submissions_stmt->bind_param("s", $lecturer_data['module']);
        $submissions_stmt->execute();
        $submissions_result = $submissions_stmt->get_result();
        $submissions = $submissions_result->fetch_all(MYSQLI_ASSOC);
        $submissions_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Lecturer - Assignment Submissions</title>
    <link rel="stylesheet" href="lecturer.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>

        body {
                font-family: "Segoe UI", sans-serif;
                background: url('bg1.jpg') center/cover no-repeat;
                margin: 0;
                padding: 20px;
                height: 1060px;
            }

        .container {
            max-width: 1200px;
            margin: 100px auto 50px;
            padding: 30px;
        }
        
        .page-title {
            color: #2c5030ff;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        
        .submissions-grid {
            display: grid;
            gap: 20px;
        }
        
        .submission-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 5px solid #50db34ff;
        }
        
        .submission-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .student-info {
            font-weight: 600;
            color: #2e502cff;
        }
        
        .course-info {
            color: #7f8d82ff;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .submission-date {
            color: #95a69aff;
            font-size: 14px;
            text-align: right;
        }
        
        .assignment-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c5037ff;
        }
        
        .assignment-description {
            color: #5d7e5dff;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .download-btn {
            background: #27ae60;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
            font-size: 14px;
        }
        
        .download-btn:hover {
            background: #219a52;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8d86ff;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .lecturer-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: 600;
            color: #2c5034ff;
        }
        
        .info-value {
            color: #7f8d86ff;
        }
        
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            flex: 1;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #34db47ff;
        }
        
        .stat-label {
            color: #7f8d80ff;
            font-size: 14px;
        }
    </style>
</head>
<body>

<header>
    <div class="left">
        <h2>Assignment Submissions</h2>
    </div>
    <div class="right">
        <ul>
            <li><?php echo htmlspecialchars($lecturer_name); ?> (Lecturer)</li>
        </ul>
    </div>
</header>

<div class="container">
    <h1 class="page-title">📝 Student Assignment Submissions</h1>
    
    <?php if (empty($lecturer_modules)): ?>
        <div class="empty-state">
            <i class="ri-file-list-3-line"></i>
            <h3>No Module Assigned</h3>
            <p>You are not assigned to any module yet.</p>
        </div>
    <?php else: ?>
        <!-- Lecturer Information -->
        <div class="lecturer-info">
            <h3>Your Assigned Module</h3>
            <?php foreach ($lecturer_modules as $module): ?>
                <div class="info-item">
                    <span class="info-label">Module:</span>
                    <span class="info-value"><?php echo htmlspecialchars($module['module_Name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Course:</span>
                    <span class="info-value"><?php echo htmlspecialchars($module['course_Name']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Module ID:</span>
                    <span class="info-value"><?php echo htmlspecialchars($module['module_ID']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($lecturer_modules); ?></div>
                <div class="stat-label">Assigned Modules</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($submissions); ?></div>
                <div class="stat-label">Total Submissions</div>
            </div>
        </div>
        
        <?php if (empty($submissions)): ?>
            <div class="empty-state">
                <i class="ri-inbox-line"></i>
                <h3>No Submissions Yet</h3>
                <p>No students have submitted assignments for your module yet.</p>
            </div>
        <?php else: ?>
            <div class="submissions-grid">
                <?php foreach ($submissions as $submission): ?>
                    <div class="submission-card">
                        <div class="submission-header">
                            <div>
                                <div class="student-info">
                                    <?php echo htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']); ?>
                                    (<?php echo htmlspecialchars($submission['stID']); ?>)
                                </div>
                                <div class="course-info">
                                    <?php echo htmlspecialchars($submission['course_Name'] . ' - ' . $submission['module_Name']); ?>
                                </div>
                            </div>
                            <div class="submission-date">
                                Submitted: <?php echo date('M j, Y g:i A', strtotime($submission['submitted_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="assignment-title">
                            <?php echo htmlspecialchars($submission['assignment_title']); ?>
                        </div>
                        
                        <?php if (!empty($submission['assignment_description'])): ?>
                            <div class="assignment-description">
                                <?php echo nl2br(htmlspecialchars($submission['assignment_description'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" 
                           class="download-btn" download target="_blank">
                            <i class="ri-download-line"></i> Download Assignment File
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>