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

$lecturer_id = $_GET['lecturer_id'] ?? 'L001';

// Query to get assignments with course and module names
$sql = "SELECT 
            a.assignment_id,
            a.assignment_title,
            a.assignment_description,
            a.due_date,
            a.max_marks,
            a.assignment_file,
            a.created_at,
            c.course_Name,
            c.course_code,
            m.module_Name
        FROM assignments a
        JOIN course c ON a.course_ID = c.course_ID
        JOIN module m ON a.module_ID = m.module_ID
        WHERE a.lecturer_id = ?
        ORDER BY a.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();

$assignments = [];
while ($row = $result->fetch_assoc()) {
    $assignments[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($assignments);
?>