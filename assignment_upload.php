<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'student') {
    header("Location: Login.html");
    exit();
}

$student_name = $_SESSION['student_name'];
$student_id = $_SESSION['user_id'];
$profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'human.png';

// Database connection
$host = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "skill_pro_institute";

$conn = mysqli_connect($host, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get student's enrolled courses with modules
$enrolled_courses_sql = "
    SELECT DISTINCT c.course_ID, c.course_Name, m.module_ID, m.module_Name 
    FROM enrollments e 
    JOIN course c ON e.course_ID = c.course_ID 
    JOIN module m ON c.course_ID = m.course_ID
    WHERE e.student_id = ?
    ORDER BY c.course_Name, m.module_Name
";
$stmt = $conn->prepare($enrolled_courses_sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$courses_result = $stmt->get_result();
$enrolled_modules = $courses_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle assignment submission
$upload_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['assignment_file'])) {
    $module_id = $_POST['module_id'];
    $assignment_title = $_POST['assignment_title'];
    $assignment_description = $_POST['assignment_description'];
    
    // Get course_id from selected module
    $course_sql = "SELECT course_ID FROM module WHERE module_ID = ?";
    $course_stmt = $conn->prepare($course_sql);
    $course_stmt->bind_param("s", $module_id);
    $course_stmt->execute();
    $course_result = $course_stmt->get_result();
    $module_data = $course_result->fetch_assoc();
    $course_id = $module_data['course_ID'];
    $course_stmt->close();
    
    // File upload handling
    $target_dir = "uploads/assignments/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . "_" . basename($_FILES["assignment_file"]["name"]);
    $target_file = $target_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check file size (5MB max)
    if ($_FILES["assignment_file"]["size"] > 5000000) {
        $upload_message = "Sorry, your file is too large. Maximum size is 5MB.";
    } 
    // Allow certain file formats
    elseif (!in_array($file_type, ['pdf', 'doc', 'docx', 'zip', 'rar'])) {
        $upload_message = "Sorry, only PDF, DOC, DOCX, ZIP, RAR files are allowed.";
    } 
    else {
        if (move_uploaded_file($_FILES["assignment_file"]["tmp_name"], $target_file)) {
            // Insert submission into database
            $insert_sql = "
                INSERT INTO assignment_submissions 
                (student_id, course_id, module_id, assignment_title, assignment_description, file_path, submitted_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssssss", $student_id, $course_id, $module_id, $assignment_title, $assignment_description, $target_file);
            
            if ($insert_stmt->execute()) {
                $upload_message = "Assignment submitted successfully!";
                // Clear form fields
                $_POST = array();
            } else {
                $upload_message = "Error submitting assignment. Please try again.";
            }
            $insert_stmt->close();
        } else {
            $upload_message = "Sorry, there was an error uploading your file.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Submit Assignment</title>
    <link rel="stylesheet" href="student.css">
    <link rel="stylesheet" href="animation.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        .container {
            max-width: 800px;
            margin: 100px auto 50px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .page-title {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        
        .upload-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            border: 2px dashed #3498db;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            outline: none;
        }
        
        .file-upload {
            border: 2px dashed #3498db;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            background: #f8f9fa;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .file-upload:hover {
            background: #e3f2fd;
        }
        
        .file-upload i {
            font-size: 48px;
            color: #3498db;
            margin-bottom: 10px;
        }
        
        .submit-btn {
            background: #27ae60;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }
        
        .submit-btn:hover {
            background: #219a52;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .module-select {
            background: white;
            border: 2px solid #e1e8ed;
        }
        
        .module-option {
            padding: 10px;
        }
    </style>
</head>
<body>

<header data-aos="zoom-in-down" data-aos-delay="350" data-aos-duration="1000">
    <div class="left">
        <h2>Submit Assignment</h2>
    </div>
    <div class="right">
        <ul>
            <li><img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;" onerror="this.src='human.png'"></li>
            <li><?php echo htmlspecialchars($student_name); ?></li>
        </ul>
    </div>
</header>

<div class="container" data-aos="zoom-in-down" data-aos-delay="450" data-aos-duration="1000">
    <h1 class="page-title">📤 Submit Your Assignment</h1>
    
    <?php if ($upload_message): ?>
        <div class="message <?php echo strpos($upload_message, 'successfully') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($upload_message); ?>
        </div>
    <?php endif; ?>
    
    <form class="upload-form" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="module_id">Select Module:</label>
            <select id="module_id" name="module_id" class="module-select" required>
                <option value="">-- Choose Module --</option>
                <?php foreach ($enrolled_modules as $module): ?>
                    <option value="<?php echo htmlspecialchars($module['module_ID']); ?>" 
                            <?php echo (isset($_POST['module_id']) && $_POST['module_id'] == $module['module_ID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($module['course_Name'] . ' - ' . $module['module_Name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="assignment_title">Assignment Title:</label>
            <input type="text" id="assignment_title" name="assignment_title" required 
                   placeholder="Enter assignment title" 
                   value="<?php echo isset($_POST['assignment_title']) ? htmlspecialchars($_POST['assignment_title']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="assignment_description">Description:</label>
            <textarea id="assignment_description" name="assignment_description" rows="4" 
                      placeholder="Describe your assignment (optional)"><?php echo isset($_POST['assignment_description']) ? htmlspecialchars($_POST['assignment_description']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Upload Assignment File:</label>
            <div class="file-upload" onclick="document.getElementById('assignment_file').click()">
                <i class="ri-upload-cloud-2-line"></i>
                <p>Click to upload your assignment file</p>
                <p><small>Allowed formats: PDF, DOC, DOCX, ZIP, RAR (Max: 5MB)</small></p>
                <input type="file" id="assignment_file" name="assignment_file" 
                       accept=".pdf,.doc,.docx,.zip,.rar" required style="display: none;" 
                       onchange="updateFileName(this)">
            </div>
            <div id="file-name" style="margin-top: 10px; font-style: italic; color: #666;"></div>
        </div>
        
        <button type="submit" class="submit-btn">
            <i class="ri-send-plane-line"></i> Submit Assignment
        </button>
    </form>
</div>

<script>
function updateFileName(input) {
    const fileNameDiv = document.getElementById('file-name');
    if (input.files.length > 0) {
        fileNameDiv.textContent = 'Selected file: ' + input.files[0].name;
    } else {
        fileNameDiv.textContent = '';
    }
}

// Add some animation to file upload area
document.querySelector('.file-upload').addEventListener('dragover', function(e) {
    e.preventDefault();
    this.style.background = '#e3f2fd';
});

document.querySelector('.file-upload').addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.style.background = '#f8f9fa';
});

document.querySelector('.file-upload').addEventListener('drop', function(e) {
    e.preventDefault();
    this.style.background = '#f8f9fa';
    const fileInput = document.getElementById('assignment_file');
    fileInput.files = e.dataTransfer.files;
    updateFileName(fileInput);
});
</script>

<script src="animation.js"></script>
</body>
</html>