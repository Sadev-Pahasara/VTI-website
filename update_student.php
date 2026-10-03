<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skill_pro_institute";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$username = $_POST['username'];
$password = $_POST['password'];
$program = $_POST['program'];

// Changed table name from 'students' to 'student'
$sql = "UPDATE student SET first_name=?, last_name=?, email=?, username=?, password=?, program=? WHERE stID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssi", $first_name, $last_name, $email, $username, $password, $program, $id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>