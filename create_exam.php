<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skill_pro_institute";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get form data
$lecturer_id = $_POST['lecturer_id'] ?? '';
$course_ID = $_POST['course_code'] ?? ''; // This now contains course_ID
$module_id = $_POST['module_id'] ?? '';
$exam_title = $_POST['exam_title'] ?? '';
$exam_description = $_POST['exam_description'] ?? '';
$exam_date = $_POST['exam_date'] ?? '';
$duration = $_POST['duration'] ?? '';
$total_marks = $_POST['total_marks'] ?? '';

// Validate required fields
if (empty($lecturer_id) || empty($course_ID) || empty($module_id) || empty($exam_title) || empty($exam_date) || empty($duration) || empty($total_marks)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit;
}

// Handle file upload
$exam_file = null;
if (isset($_FILES['exam_file']) && $_FILES['exam_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/exams/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileExtension = pathinfo($_FILES['exam_file']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $exam_title) . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    // Check file type
    $allowedTypes = ['doc', 'docx', 'pdf'];
    if (!in_array(strtolower($fileExtension), $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Only DOC, DOCX, and PDF files are allowed']);
        exit;
    }
    
    // Move uploaded file
    if (move_uploaded_file($_FILES['exam_file']['tmp_name'], $filePath)) {
        $exam_file = $filePath;
    }
}

// Verify course exists
$course_sql = "SELECT course_ID FROM course WHERE course_ID = ?";
$stmt = $conn->prepare($course_sql);
$stmt->bind_param("s", $course_ID);
$stmt->execute();
$course_result = $stmt->get_result();
$course_row = $course_result->fetch_assoc();
$stmt->close();

if (empty($course_row)) {
    echo json_encode(['success' => false, 'message' => 'Invalid course selected']);
    exit;
}

// Verify module exists and belongs to the course
$module_sql = "SELECT module_ID FROM module WHERE module_ID = ? AND course_ID = ?";
$stmt = $conn->prepare($module_sql);
$stmt->bind_param("ss", $module_id, $course_ID);
$stmt->execute();
$module_result = $stmt->get_result();
$module_row = $module_result->fetch_assoc();
$stmt->close();

if (empty($module_row)) {
    echo json_encode(['success' => false, 'message' => 'Invalid module selected for this course']);
    exit;
}

// Insert exam into database
$sql = "INSERT INTO exams (lecturer_id, course_ID, module_ID, exam_title, exam_description, exam_date, duration, total_marks, exam_file) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssiis", $lecturer_id, $course_ID, $module_id, $exam_title, $exam_description, $exam_date, $duration, $total_marks, $exam_file);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Exam created successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating exam: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>