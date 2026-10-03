<?php
session_start();

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$dbname = "skill_pro_institute";

// Create connection
$conn = mysqli_connect("localhost","root","","skill_pro_institute");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $first_name = $conn->real_escape_string($_POST['first_name'] ?? '');
    $last_name = $conn->real_escape_string($_POST['last_name'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $username = $conn->real_escape_string($_POST['username'] ?? '');
    $password = $conn->real_escape_string($_POST['password'] ?? '');
    $confirm_password = $conn->real_escape_string($_POST['confirm_password'] ?? '');
    $mobile_number = $conn->real_escape_string($_POST['mobile_number'] ?? '');
    $whatsapp_number = $conn->real_escape_string($_POST['whatsapp_number'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $nic = $conn->real_escape_string($_POST['nic'] ?? '');
    $home_district = $conn->real_escape_string($_POST['home_district'] ?? '');
    $gender = $conn->real_escape_string($_POST['gender'] ?? '');
    $faculty = $conn->real_escape_string($_POST['faculty'] ?? '');
    $enrollment_method = $conn->real_escape_string($_POST['enrollment_method'] ?? '');
    $branch = $conn->real_escape_string($_POST['branch'] ?? '');
    $program = $conn->real_escape_string($_POST['program'] ?? '');
    $intake = $conn->real_escape_string($_POST['intake'] ?? '');
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password)) {
        die("Required fields are missing.");
    }
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }
    
    // Check if email or username already exists
    $check_sql = "SELECT ID FROM prereg_students WHERE email = '$email' OR username = '$username'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows > 0) {
        die("Email or username already exists.");
    }
    
    // Generate Student ID
    $student_id = generateStudentID($conn, $branch, $faculty, $program, $nic);
    
    // Handle profile image - Store only filename, not base64
    $profile_image_filename = null;
    $upload_dir = "C:/Xampp/htdocs/VTI website/Upload/";
    
    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir) && !mkdir($upload_dir, 0777, true)) {
        die("Failed to create upload directory.");
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        die("Upload directory is not writable.");
    }
    
    // Process file upload if present
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['profile_image']['name'];
        $file_tmp = $_FILES['profile_image']['tmp_name'];
        $file_size = $_FILES['profile_image']['size'];
        $file_type = $_FILES['profile_image']['type'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file type
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        if (!in_array($file_ext, $allowed_extensions)) {
            die("Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.");
        }
        
        // Validate file size (max 2MB)
        $max_file_size = 2 * 1024 * 1024; // 2MB in bytes
        if ($file_size > $max_file_size) {
            die("File size too large. Maximum allowed is 2MB.");
        }
        
        // Generate secure filename
        $new_file_name = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $username) . '.' . $file_ext;
        $file_path = $upload_dir . $new_file_name;
        
        // Move uploaded file
        if (move_uploaded_file($file_tmp, $file_path)) {
            $profile_image_filename = $new_file_name;
        } else {
            die("Error uploading file. Please try again.");
        }
    } elseif (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_errors = array(
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        );
        
        $error_code = $_FILES['profile_image']['error'];
        $error_message = $upload_errors[$error_code] ?? 'Unknown upload error';
        die("File upload error: " . $error_message);
    }
    
    // ❌ No password hashing — storing plain text password
    $plain_password = $password;
    
    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO prereg_students (
        ID, profile_image_filename, first_name, last_name, email, username, password, 
        mobile_number, whatsapp_number, date_of_birth, nic, home_district, 
        gender, faculty, enrollment_method, branch, program, intake, status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ssssssssssssssssss", 
        $student_id, $profile_image_filename, $first_name, $last_name, $email, 
        $username, $plain_password, $mobile_number, $whatsapp_number, 
        $date_of_birth, $nic, $home_district, $gender, $faculty, 
        $enrollment_method, $branch, $program, $intake
    );
    
    if ($stmt->execute()) {
        echo "<script>
            alert('Registration successful! Your Student ID is: $student_id. Your account will be approved within 2 hours.');
            window.location.href = 'form.html';
        </script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: form.html");
    exit();
}

// Function to generate Student ID
function generateStudentID($conn, $branch, $faculty, $program, $nic) {
    $branch_code = $branch;
    
    // Get faculty code
    $faculty_sql = "SELECT faculty_Code FROM course WHERE faculty = ? LIMIT 1";
    $stmt = $conn->prepare($faculty_sql);
    $stmt->bind_param("s", $faculty);
    $stmt->execute();
    $faculty_result = $stmt->get_result();
    $faculty_code = $faculty_result->num_rows > 0 ? $faculty_result->fetch_assoc()['faculty_Code'] : 'FC000';
    $stmt->close();
    
    // Get course code
    $course_sql = "SELECT course_code FROM course WHERE course_Name = ?";
    $stmt = $conn->prepare($course_sql);
    $stmt->bind_param("s", $program);
    $stmt->execute();
    $course_result = $stmt->get_result();
    $course_code = $course_result->num_rows > 0 ? $course_result->fetch_assoc()['course_code'] : 'CR000';
    $stmt->close();
    
    // Get last 6 digits of NIC
    $nic_last_6 = substr($nic, -6);
    
    // Generate student ID
    $student_id = $branch_code . "/" . $faculty_code . "/" . $course_code . "/" . $nic_last_6;
    
    return $student_id;
}
?>
