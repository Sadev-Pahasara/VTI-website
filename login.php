<?php
session_start();

// Database configuration
$host = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "skill_pro_institute";

// Create connection
$conn = mysqli_connect($host, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = trim($_POST['username']);
    $password_input = $_POST['password'];
    
    // Validate inputs
    if (empty($user_input) || empty($password_input)) {
        showError("Please fill in all fields.");
    }
    
    // Try Admin login from database table
    $admin = tryAdminLogin($user_input, $password_input, $conn);
    if ($admin) {
        $_SESSION['user_id'] = $admin['admin_ID'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['role'] = 'admin';
        $_SESSION['loggedin'] = true;
        $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
        $_SESSION['email'] = $admin['Email'];
        $conn->close(); // Close connection here
        header("Location: adminportal.html");
        exit();
    }
    
    // Try Student login
    $student = tryStudentLogin($user_input, $password_input, $conn);
    if ($student) {
        $_SESSION['user_id'] = $student['stID'];
        $_SESSION['username'] = $student['username'];
        $_SESSION['role'] = 'student';
        $_SESSION['loggedin'] = true;
        $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
        $_SESSION['email'] = $student['email'];
        $_SESSION['faculty'] = $student['faculty'];
        $_SESSION['program'] = $student['program'];
        $_SESSION['profile_image'] = $student['profile_image'] ?? 'human.png'; // Added fallback
        $conn->close(); // Close connection here
        header("Location: preloading2forstpanel.html");
        exit();
    }
    
    // Try Lecturer login
        $lecturer = tryLecturerLogin($user_input, $password_input, $conn);
        if ($lecturer) {
            $_SESSION['user_id'] = $lecturer['lecid'];
            $_SESSION['username'] = $lecturer['username'];
            $_SESSION['role'] = 'lecturer';
            $_SESSION['loggedin'] = true;
            $_SESSION['lecturer_name'] = $lecturer['first_name'] . ' ' . $lecturer['last_name'];
            $_SESSION['email'] = $lecturer['email'];
            $_SESSION['faculty'] = $lecturer['faculty'];
            $_SESSION['branch'] = $lecturer['branch'];
            $_SESSION['course'] = $lecturer['course'];
            $_SESSION['module'] = $lecturer['module'];
            $conn->close();
            header("Location: lecturerportal.html");
            exit();
        }
    
    // If no login succeeded
    $conn->close(); // Close connection here
    showError("Invalid credentials! Please check your username/email and password.");
} else {
    header("Location: Login.html");
    exit();
}

// Your functions remain the same...
function tryAdminLogin($username, $password, $conn) {
    $sql = "SELECT admin_ID, first_name, last_name, Email, username, password FROM admin WHERE username = ? OR Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password']) || $password === $admin['password']) {
            $stmt->close();
            return $admin;
        }
    }
    $stmt->close();
    return false;
}

function tryLecturerLogin($username, $password, $conn) {
    $sql = "SELECT lecid, first_name, last_name, email, username, password, faculty, branch, course, module FROM lecturers WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $lecturer = $result->fetch_assoc();
            if (password_verify($password, $lecturer['password']) || $password === $lecturer['password']) {
                $stmt->close();
                return $lecturer;
            }
        }
        $stmt->close();
    }
    return false;
}

function tryStudentLogin($username, $password, $conn) {
    $sql = "SELECT stID, first_name, last_name, email, username, password, faculty, program, profile_image FROM student WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $student = $result->fetch_assoc();
            if (password_verify($password, $student['password']) || $password === $student['password']) {
                $stmt->close();
                return $student;
            }
        }
        $stmt->close();
    }
    return false;
}

function showError($message) {
    echo "<script>alert('$message'); window.location.href = 'Login.html';</script>";
    exit();
}
?>