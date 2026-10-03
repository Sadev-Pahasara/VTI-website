<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
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

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // UPDATE EXAM
    handleUpdateExam($conn);
} elseif ($method === 'DELETE') {
    // DELETE EXAM
    handleDeleteExam($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function handleUpdateExam($conn) {
    // Get form data
    $exam_id = $_POST['exam_id'] ?? '';
    $lecturer_id = $_POST['lecturer_id'] ?? '';
    $course_ID = $_POST['course_code'] ?? '';
    $module_id = $_POST['module_id'] ?? '';
    $exam_title = $_POST['exam_title'] ?? '';
    $exam_description = $_POST['exam_description'] ?? '';
    $exam_date = $_POST['exam_date'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $total_marks = $_POST['total_marks'] ?? '';

    // Validate required fields
    if (empty($exam_id) || empty($lecturer_id) || empty($course_ID) || empty($module_id) || empty($exam_title) || empty($exam_date) || empty($duration) || empty($total_marks)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }

    // Verify exam exists and belongs to lecturer
    $verify_sql = "SELECT exam_id FROM exams WHERE exam_id = ? AND lecturer_id = ?";
    $stmt = $conn->prepare($verify_sql);
    $stmt->bind_param("ss", $exam_id, $lecturer_id);
    $stmt->execute();
    $verify_result = $stmt->get_result();
    $verify_row = $verify_result->fetch_assoc();
    $stmt->close();

    if (empty($verify_row)) {
        echo json_encode(['success' => false, 'message' => 'Exam not found or you do not have permission to edit it']);
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
            
            // Update with file
            $sql = "UPDATE exams SET course_ID = ?, module_ID = ?, exam_title = ?, exam_description = ?, exam_date = ?, duration = ?, total_marks = ?, exam_file = ? WHERE exam_id = ? AND lecturer_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssiiss", $course_ID, $module_id, $exam_title, $exam_description, $exam_date, $duration, $total_marks, $exam_file, $exam_id, $lecturer_id);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
            exit;
        }
    } else {
        // Update without changing file
        $sql = "UPDATE exams SET course_ID = ?, module_ID = ?, exam_title = ?, exam_description = ?, exam_date = ?, duration = ?, total_marks = ? WHERE exam_id = ? AND lecturer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssiiss", $course_ID, $module_id, $exam_title, $exam_description, $exam_date, $duration, $total_marks, $exam_id, $lecturer_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Exam updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating exam: ' . $stmt->error]);
    }

    $stmt->close();
}

function handleDeleteExam($conn) {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $exam_id = $input['exam_id'] ?? '';
    $lecturer_id = $input['lecturer_id'] ?? '';

    // Validate required fields
    if (empty($exam_id) || empty($lecturer_id)) {
        echo json_encode(['success' => false, 'message' => 'Exam ID and Lecturer ID are required']);
        exit;
    }

    // Verify exam exists and belongs to lecturer
    $verify_sql = "SELECT exam_id, exam_file FROM exams WHERE exam_id = ? AND lecturer_id = ?";
    $stmt = $conn->prepare($verify_sql);
    $stmt->bind_param("ss", $exam_id, $lecturer_id);
    $stmt->execute();
    $verify_result = $stmt->get_result();
    $verify_row = $verify_result->fetch_assoc();
    $stmt->close();

    if (empty($verify_row)) {
        echo json_encode(['success' => false, 'message' => 'Exam not found or you do not have permission to delete it']);
        exit;
    }

    // Delete exam file if exists
    if (!empty($verify_row['exam_file']) && file_exists($verify_row['exam_file'])) {
        unlink($verify_row['exam_file']);
    }

    // Delete exam from database
    $sql = "DELETE FROM exams WHERE exam_id = ? AND lecturer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $exam_id, $lecturer_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Exam deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting exam: ' . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>