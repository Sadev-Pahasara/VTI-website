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

// Query to get exams with course and module names
$sql = "SELECT 
            e.exam_id,
            e.exam_title,
            e.exam_description,
            e.exam_date,
            e.duration,
            e.total_marks,
            e.exam_file,
            e.created_at,
            c.course_Name,
            m.module_Name
        FROM exams e
        JOIN course c ON e.course_ID = c.course_ID
        JOIN module m ON e.module_ID = m.module_ID
        WHERE e.lecturer_id = ?
        ORDER BY e.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();

$exams = [];
while ($row = $result->fetch_assoc()) {
    $exams[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($exams);
?>