<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lecturer Profile</title>
  <link rel="stylesheet" href="lecprofile.css">
  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<?php
session_start();

// Check if user is logged in and is a lecturer
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: Login.html");
    exit();
}

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

// Fetch lecturer data
$lecturer_id = $_SESSION['user_id'];
$sql = "SELECT lecid, first_name, last_name, email, username, faculty, branch, course, module, mobile_number, 
               profile_picture, designation, address, office 
        FROM lecturers WHERE lecid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $lecturer = $result->fetch_assoc();
    
    // Check if profile is locked (if any of the one-time fields is filled)
    $profile_locked = !empty($lecturer['address']) || !empty($lecturer['office']) || !empty($lecturer['designation']);
} else {
    die("Lecturer not found!");
}
$stmt->close();

// Handle profile updates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $update_fields = array();
    $update_values = array();
    $update_types = "";
    
    // Handle image upload (always allowed)
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "uploads/lecturers/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION);
        $new_filename = "lecturer_" . $lecturer_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check if file is an actual image
        $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $update_fields[] = "profile_picture = ?";
                $update_values[] = $new_filename;
                $update_types .= "s";
                $success_message = "Profile image updated successfully!";
            } else {
                $error_message = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error_message = "File is not an image.";
        }
    }
    
    // Only allow other updates if profile is not locked
    if (!$profile_locked) {
        // Handle mobile number
        if (isset($_POST['mobile_number']) && $_POST['mobile_number'] != $lecturer['mobile_number']) {
            $update_fields[] = "mobile_number = ?";
            $update_values[] = $_POST['mobile_number'];
            $update_types .= "s";
        }
        
        // Handle one-time fields
        if (isset($_POST['address']) && !empty($_POST['address'])) {
            $update_fields[] = "address = ?";
            $update_values[] = $_POST['address'];
            $update_types .= "s";
        }
        
        if (isset($_POST['office']) && !empty($_POST['office'])) {
            $update_fields[] = "office = ?";
            $update_values[] = $_POST['office'];
            $update_types .= "s";
        }
        
        if (isset($_POST['designation']) && !empty($_POST['designation'])) {
            $update_fields[] = "designation = ?";
            $update_values[] = $_POST['designation'];
            $update_types .= "s";
        }
    }
    
    // Update database if there are fields to update
    if (!empty($update_fields)) {
        $update_values[] = $lecturer_id; // Add lecturer ID for WHERE clause
        $update_types .= "s"; // Add type for WHERE clause parameter
        
        $update_sql = "UPDATE lecturers SET " . implode(", ", $update_fields) . " WHERE lecid = ?";
        $stmt = $conn->prepare($update_sql);
        
        if ($stmt) {
            $stmt->bind_param($update_types, ...$update_values);
            
            if ($stmt->execute()) {
                if (empty($success_message)) {
                    $success_message = "Profile updated successfully!";
                }
                // Refresh lecturer data
                $sql = "SELECT lecid, first_name, last_name, email, username, faculty, branch, course, module, mobile_number, 
                               profile_picture, designation, address, office 
                        FROM lecturers WHERE lecid = ?";
                $stmt2 = $conn->prepare($sql);
                $stmt2->bind_param("s", $lecturer_id);
                $stmt2->execute();
                $result = $stmt2->get_result();
                $lecturer = $result->fetch_assoc();
                $stmt2->close();
                
                // Update lock status
                $profile_locked = !empty($lecturer['address']) || !empty($lecturer['office']) || !empty($lecturer['designation']);
            } else {
                $error_message = "Error updating profile: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error_message = "Error preparing update statement: " . $conn->error;
        }
    } elseif (empty($success_message) && empty($error_message) && !$profile_locked) {
        $info_message = "No changes made to the profile.";
    }
}

$conn->close();
?>

<div class="profile-container">

  <!-- Display messages -->
  <?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
  <?php endif; ?>
  
  <?php if (isset($error_message)): ?>
    <div class="alert alert-error"><?php echo $error_message; ?></div>
  <?php endif; ?>
  
  <?php if (isset($info_message)): ?>
    <div class="alert alert-info"><?php echo $info_message; ?></div>
  <?php endif; ?>
  
  <?php if ($profile_locked): ?>
    
  <?php endif; ?>

  <!-- Header -->
  <div class="profile-header">
    <form method="POST" enctype="multipart/form-data" class="image-upload-form">
      <div class="image-upload-container">
        <?php if (!empty($lecturer['profile_picture'])): ?>
          <img src="uploads/lecturers/<?php echo htmlspecialchars($lecturer['profile_picture']); ?>" 
               alt="Lecturer Photo" 
               class="profile-pic" 
               id="profile-image">
        <?php else: ?>
          <img src="lecturer.png" 
               alt="Lecturer Photo" 
               class="profile-pic" 
               id="profile-image">
        <?php endif; ?>
        <label for="file-input" class="image-upload-label">
          <i class="ri-camera-line"></i>
          Change Photo
        </label>
        <input type="file" id="file-input" name="profile_image" accept="image/*" style="display: none;" onchange="this.form.submit()">
      </div>
    </form>
    <div style="margin-left: -100px;">
      <h2><?php echo htmlspecialchars($lecturer['first_name'] . ' ' . $lecturer['last_name']); ?></h2>
      <p>Lecturer ID: <?php echo htmlspecialchars($lecturer['lecid']); ?></p>
    </div>
  </div>

  <form method="POST" enctype="multipart/form-data">
  
  <!-- About Section -->
  <h3 class="section-title">ABOUT</h3>
  <div class="form-group">
    <label><i class="ri-phone-line"></i> Phone :</label>
    <input type="text" name="mobile_number" value="<?php echo htmlspecialchars($lecturer['mobile_number'] ?? ''); ?>" 
           <?php echo $profile_locked ? 'readonly' : ''; ?>>
  </div>
  <div class="form-group">
    <label><i class="ri-mail-line"></i> Email :</label>
    <input type="email" value="<?php echo htmlspecialchars($lecturer['email']); ?>" readonly>
  </div>
  <div class="form-group">
    <label><i class="ri-user-line"></i> User Name :</label>
    <input type="text" value="<?php echo htmlspecialchars($lecturer['username']); ?>" readonly>
  </div>

  <hr>

  <!-- Address Section -->
  <h3 class="section-title">ADDRESS</h3>
  <div class="form-group">
    <label><i class="ri-map-pin-line"></i> Address :</label>
    <input type="text" name="address" value="<?php echo htmlspecialchars($lecturer['address'] ?? ''); ?>" 
           <?php echo $profile_locked ? 'readonly' : 'placeholder="Enter your address"'; ?>>
  </div>
  <div class="form-group">
    <label><i class="ri-building-2-line"></i> Faculty :</label>
    <input type="text" value="<?php echo htmlspecialchars($lecturer['faculty']); ?>" readonly>
  </div>
  <div class="form-group">
    <label><i class="ri-home-2-line"></i> Office :</label>
    <input type="text" name="office" value="<?php echo htmlspecialchars($lecturer['office'] ?? ''); ?>" 
           <?php echo $profile_locked ? 'readonly' : 'placeholder="Enter your office"'; ?>>
  </div>

  <hr>

  <!-- Lecturer Details -->
  <h3 class="section-title">LECTURER DETAILS</h3>
  <div class="form-group">
    <label><i class="ri-book-2-line"></i> Branch :</label>
    <input type="text" value="<?php echo htmlspecialchars($lecturer['branch']); ?>" readonly>
  </div>
  <div class="form-group">
    <label><i class="ri-book-2-line"></i> Courses :</label>
    <input type="text" value="<?php echo htmlspecialchars($lecturer['course']); ?>" readonly>
  </div>
  <div class="form-group">
    <label><i class="ri-book-2-line"></i> Modules :</label>
    <input type="text" value="<?php echo htmlspecialchars($lecturer['module']); ?>" readonly>
  </div>
  <div class="form-group">
    <label><i class="ri-trophy-line"></i> Designation :</label>
    <input type="text" name="designation" value="<?php echo htmlspecialchars($lecturer['designation'] ?? ''); ?>" 
           <?php echo $profile_locked ? 'readonly' : 'placeholder="Enter your designation"'; ?>>
  </div>

  <?php if (!$profile_locked): ?>
    <button type="submit" class="save-btn"><i class="ri-save-3-line"></i> Save Profile</button>
    <small class="save-notice">After saving, all fields except profile picture will be locked permanently.</small>
  <?php else: ?>
    <div class="locked-message">
      <i class="ri-lock-fill"></i> Profile is locked. Only profile picture can be changed.
    </div>
  <?php endif; ?>

  </form>

</div>

</body>
</html>