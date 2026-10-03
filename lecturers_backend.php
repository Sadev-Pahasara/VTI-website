<?php
header('Content-Type: application/json');

// Database connection
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

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Get the action parameter
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_lecturers':
            getLecturers($conn);
            break;
            
        case 'save_lecturer':
            saveLecturer($conn);
            break;
            
        case 'delete_lecturer':
            deleteLecturer($conn);
            break;
            
        case 'search_lecturers':
            searchLecturers($conn);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();

// Get all lecturers
function getLecturers($conn) {
    $sql = "SELECT * FROM lecturers ORDER BY lecid";
    $result = $conn->query($sql);
    
    $lecturers = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $lecturers[] = $row;
        }
    }
    
    echo json_encode($lecturers);
}

// Save lecturer (add or update)
function saveLecturer($conn) {
    // Get form data
    $editing_id = $_POST['editing_id'] ?? null;
    $lecturerId = $_POST['lecturerId'] ?? '';
    $firstName = $_POST['lecturerFName'] ?? '';
    $lastName = $_POST['lecturerLName'] ?? '';
    $email = $_POST['lecturerEmail'] ?? '';
    $username = $_POST['lecturerusername'] ?? '';
    $password = $_POST['lecturerpassword'] ?? '';
    $branch = $_POST['branch'] ?? '';
    $faculty = $_POST['faculty'] ?? '';
    $course = $_POST['course'] ?? '';
    $module = $_POST['module'] ?? '';
    $mobileNumber = $_POST['lecturerNumber'] ?? '';

    // Validate required fields
    if (empty($lecturerId) || empty($firstName) || empty($lastName) || empty($email) || 
        empty($username) || empty($password) || empty($branch) || empty($faculty) || 
        empty($mobileNumber)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }

    // Check if editing or adding new
    if ($editing_id) {
        // Update existing lecturer
        $stmt = $conn->prepare("UPDATE lecturers SET lecid=?, first_name=?, last_name=?, email=?, username=?, password=?, branch=?, faculty=?, course=?, module=?, mobile_number=? WHERE lecid=?");
        $stmt->bind_param("ssssssssssss", $lecturerId, $firstName, $lastName, $email, $username, $password, $branch, $faculty, $course, $module, $mobileNumber, $editing_id);
    } else {
        // Check if lecturer ID already exists
        $checkStmt = $conn->prepare("SELECT lecid FROM lecturers WHERE lecid = ?");
        $checkStmt->bind_param("s", $lecturerId);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Lecturer ID already exists']);
            $checkStmt->close();
            return;
        }
        $checkStmt->close();

        // Insert new lecturer
        $stmt = $conn->prepare("INSERT INTO lecturers (lecid, first_name, last_name, email, username, password, branch, faculty, course, module, mobile_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $lecturerId, $firstName, $lastName, $email, $username, $password, $branch, $faculty, $course, $module, $mobileNumber);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Lecturer saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving lecturer: ' . $stmt->error]);
    }
    
    $stmt->close();
}

// Delete lecturer
function deleteLecturer($conn) {
    $lecid = $_POST['lecid'] ?? '';
    
    if (empty($lecid)) {
        echo json_encode(['success' => false, 'message' => 'Lecturer ID is required']);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM lecturers WHERE lecid = ?");
    $stmt->bind_param("s", $lecid);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Lecturer deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting lecturer: ' . $stmt->error]);
    }
    
    $stmt->close();
}

// Search lecturers
function searchLecturers($conn) {
    $query = $_GET['query'] ?? '';
    
    if (empty($query)) {
        echo json_encode([]);
        return;
    }

    $searchTerm = "%$query%";
    $stmt = $conn->prepare("SELECT * FROM lecturers WHERE lecid LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?");
    $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $lecturers = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $lecturers[] = $row;
        }
    }
    
    echo json_encode($lecturers);
    $stmt->close();
}


?>