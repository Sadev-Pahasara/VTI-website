<?php
session_start();
header('Content-Type: application/json');

// Database configuration
$host = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "skill_pro_institute";

// Create connection
$conn = mysqli_connect($host, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$username = $_SESSION['username'];

// Get student data using username
$sql = "SELECT * FROM student WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $student = $result->fetch_assoc();
    echo json_encode(['success' => true, 'student' => $student]);
} else {
    echo json_encode(['success' => false, 'error' => 'Student not found']);
}

$stmt->close();
$conn->close();
?>