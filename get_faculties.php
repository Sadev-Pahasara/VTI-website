<?php
$conn = mysqli_connect("localhost","root","","skill_pro_institute");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$enrollment_method = $_GET['enrollment_method'] ?? '';

if ($enrollment_method === 'Online') {
    $sql = "SELECT DISTINCT faculty FROM course WHERE faculty = 'Short Courses & Certifications'";
} else {
    $sql = "SELECT DISTINCT faculty FROM course";
}

$result = $conn->query($sql);

$faculties = array();
while($row = $result->fetch_assoc()) {
    $faculties[] = $row;
}

echo json_encode($faculties);
$conn->close();
?>