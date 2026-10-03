<?php
// Database configuration
$host = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "skill_pro_institute";

// Create connection
$conn = new mysqli($host, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set headers for proper content type
header("Content-Type: text/html; charset=UTF-8");

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Get all inquiries - return JSON for GET requests
        header("Content-Type: application/json; charset=UTF-8");
        $sql = "SELECT * FROM inquiries ORDER BY id DESC";
        $result = $conn->query($sql);
        
        $inquiries = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $inquiries[] = $row;
            }
        }
        
        echo json_encode($inquiries);
        break;
        
    case 'POST':
        // Handle form submission from contact form
        $name = $_POST['name'];
        $email = $_POST['email'];
        $message = $_POST['message'];
        $date = date('Y-m-d');
        $status = 'pending';
        
        // Use prepared statements to prevent SQL injection
        $sql = "INSERT INTO inquiries (name, email, message, date, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $email, $message, $date, $status);
        
        if ($stmt->execute()) {
            echo "<script>alert('Inquiry submitted successfully');</script>";
        } else {
            echo "<script>alert('Error: " . addslashes($stmt->error) . "');</script>";
        }
        $stmt->close();
        break;
        
    case 'PUT':
        // Update inquiry status - return JSON for API calls
        header("Content-Type: application/json; charset=UTF-8");
        parse_str(file_get_contents("php://input"), $data);
        
        $id = isset($data['id']) ? intval($data['id']) : 0;
        $status = isset($data['status']) ? $data['status'] : '';
        
        if ($id > 0 && !empty($status)) {
            // Use prepared statement for security
            $sql = "UPDATE inquiries SET status=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("si", $status, $id);
                
                if ($stmt->execute()) {
                    echo json_encode(["message" => "Inquiry updated successfully"]);
                } else {
                    echo json_encode(["error" => "Error: " . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(["error" => "Error: Database preparation failed"]);
            }
        } else {
            echo json_encode(["error" => "Error: Invalid ID or status"]);
        }
        break;
        
    case 'DELETE':
        // Delete inquiry - return JSON for API calls
        header("Content-Type: application/json; charset=UTF-8");
        parse_str(file_get_contents("php://input"), $data);
        
        $id = isset($data['id']) ? intval($data['id']) : 0;
        
        if ($id > 0) {
            // Use prepared statement for security
            $sql = "DELETE FROM inquiries WHERE id=?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    echo json_encode(["message" => "Inquiry deleted successfully"]);
                } else {
                    echo json_encode(["error" => "Error: " . $stmt->error]);
                }
                $stmt->close();
            } else {
                echo json_encode(["error" => "Error: Database preparation failed"]);
            }
        } else {
            echo json_encode(["error" => "Error: Invalid ID"]);
        }
        break;
        
    default:
        echo "<script>
            alert('Error: Invalid request method');
            window.history.back();
        </script>";
        break;
}

$conn->close();
?>