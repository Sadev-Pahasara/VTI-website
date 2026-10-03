<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skill_pro_institute";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Changed table name from 'students' to 'student'
$sql = "SELECT stID as id, first_name, last_name, email, username, password, program as course FROM student";
$result = $conn->query($sql);

$students = array();

if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Combine first_name and last_name into full name
            $row['name'] = $row['first_name'] . ' ' . $row['last_name'];
            $students[] = $row;
        }
    }
    echo json_encode($students);
} else {
    echo json_encode(["error" => "Query failed: " . $conn->error]);
}

$conn->close();
?>