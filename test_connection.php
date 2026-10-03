<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Database Connection</h2>";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skill_pro_institute";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>✅ Database connected successfully!</p>";
}

// Check if students table exists
$result = $conn->query("SHOW TABLES LIKE 'students'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Students table exists!</p>";
    
    // Check table structure
    $structure = $conn->query("DESCRIBE student");
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["Field"] . "</td>";
        echo "<td>" . $row["Type"] . "</td>";
        echo "<td>" . $row["Null"] . "</td>";
        echo "<td>" . $row["Key"] . "</td>";
        echo "<td>" . $row["Default"] . "</td>";
        echo "<td>" . $row["Extra"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show sample data
    $sql = "SELECT * FROM student LIMIT 5";
    $students = $conn->query($sql);
    
    if ($students->num_rows > 0) {
        echo "<p style='color: green;'>✅ Found " . $students->num_rows . " student:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>stID</th><th>first_name</th><th>last_name</th><th>email</th><th>username</th><th>password</th><th>program</th></tr>";
        while($row = $students->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["stID"] . "</td>";
            echo "<td>" . $row["first_name"] . "</td>";
            echo "<td>" . $row["last_name"] . "</td>";
            echo "<td>" . $row["email"] . "</td>";
            echo "<td>" . $row["username"] . "</td>";
            echo "<td>" . $row["password"] . "</td>";
            echo "<td>" . $row["program"] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No students found in table. The table exists but is empty.</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Students table does NOT exist in the database.</p>";
}

$conn->close();
?>