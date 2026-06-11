<?php
echo "Testing dailywork API<br><br>";

// Test database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'geotraverse_erp';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo "❌ Database connection failed: " . $conn->connect_error;
} else {
    echo "✅ Database connected successfully<br>";
    
    // Check if dailywork table exists
    $result = $conn->query("SHOW TABLES LIKE 'dailywork'");
    if ($result->num_rows > 0) {
        echo "✅ dailywork table exists<br>";
        
        // Get table structure
        $result = $conn->query("DESCRIBE dailywork");
        echo "<br>Table structure:<br>";
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
        }
    } else {
        echo "❌ dailywork table does NOT exist<br>";
    }
    
    $conn->close();
}
?>