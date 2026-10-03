<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

echo json_encode([
    'student_name' => $_SESSION['student_name'],
    'profile_image' => $_SESSION['profile_image'] ?? 'human.png'
]);
?>