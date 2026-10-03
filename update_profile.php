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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['username']) && $_SESSION['role'] === 'student') {
    $username = $_SESSION['username'];
    
    // Get form data
    $mobile_number = $_POST['mobile_number'] ?? '';
    $address = $_POST['address'] ?? '';
    $home_district = $_POST['home_district'] ?? '';
    $postcode = $_POST['postcode'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $nic = $_POST['nic'] ?? '';
    $intake = $_POST['intake'] ?? '';
    
    // Update student data in database using username
    $sql = "UPDATE student SET 
            mobile_number = ?, 
            address = ?, 
            home_district = ?, 
            postcode = ?, 
            date_of_birth = ?, 
            nic = ?, 
            intake = ? 
            WHERE username = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssissss", $mobile_number, $address, $home_district, $postcode, $date_of_birth, $nic, $intake, $username);
    
    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href = 'profile.html';</script>";
    } else {
        echo "<script>alert('Error updating profile: " . $conn->error . "'); window.location.href = 'profile.html';</script>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: Login.html");
    exit();
}
?>