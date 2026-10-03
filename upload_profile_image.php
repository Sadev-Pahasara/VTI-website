<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['username']) && $_SESSION['role'] === 'student') {
    $username = $_SESSION['username'];
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/profiles/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $fileName = 'profile_' . $username . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['profile_image']['type'], $allowedTypes)) {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                // Update database with new profile image path using username
                $host = "localhost";
                $username_db = "root";
                $password_db = "";
                $dbname = "skill_pro_institute";
                
                $conn = mysqli_connect($host, $username_db, $password_db, $dbname);
                
                if (!$conn->connect_error) {
                    $sql = "UPDATE student SET profile_image = ? WHERE username = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $uploadPath, $username);
                    $stmt->execute();
                    $stmt->close();
                    $conn->close();
                    
                    echo json_encode(['success' => true, 'image_path' => $uploadPath]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'File upload failed']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid file type']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
}
?>