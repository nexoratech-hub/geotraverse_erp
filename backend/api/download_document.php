<?php
// download_document.php
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="document.pdf"');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

if (!$id) {
    echo "Invalid ID";
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'geotraverse_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($type === 'uploaded_report') {
        $stmt = $pdo->prepare("SELECT file_name FROM uploaded_reports WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $fileName = $row['file_name'];
            $filePath = 'C:/xampp/htdocs/geotraverse/frontend/assets/uploads/reports/' . basename($fileName);
            
            if (file_exists($filePath)) {
                header('Content-Disposition: inline; filename="' . basename($fileName) . '"');
                readfile($filePath);
                exit;
            } else {
                echo "File not found: " . $filePath;
            }
        } else {
            echo "Report not found";
        }
    } elseif ($type === 'project') {
        $stmt = $pdo->prepare("SELECT file_name FROM project_documents WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $fileName = $row['file_name'];
            $filePath = 'C:/xampp/htdocs/geotraverse/frontend/assets/uploads/projects/projects_documents/' . basename($fileName);
            
            if (file_exists($filePath)) {
                header('Content-Disposition: inline; filename="' . basename($fileName) . '"');
                readfile($filePath);
                exit;
            } else {
                echo "File not found: " . $filePath;
            }
        } else {
            echo "Document not found";
        }
    } else {
        echo "Invalid type";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>