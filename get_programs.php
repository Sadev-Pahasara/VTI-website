<?php
$conn = mysqli_connect("localhost","root","","skill_pro_institute");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$faculty = $_GET['faculty'] ?? '';

$sql = "SELECT course_Name FROM course WHERE faculty = '$faculty'";
$result = $conn->query($sql);

$programs = array();
while($row = $result->fetch_assoc()) {
    $programs[] = $row;
}

echo json_encode($programs);
$conn->close();
?>