<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
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

$course_id = $_GET['course_id'] ?? '';

if (empty($course_id)) {
    echo json_encode([]);
    exit;
}

// Query to get modules for a specific course from module table
$sql = "SELECT module_ID, module_Code, module_Name FROM module WHERE course_ID = ? ORDER BY module_Code";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $course_id);
$stmt->execute();
$result = $stmt->get_result();

$modules = [];
while ($row = $result->fetch_assoc()) {
    $modules[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($modules);
?>