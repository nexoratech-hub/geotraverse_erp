<?php
// serve_file.php - WITH DEBUGGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

// If no ID provided, try to get from POST
if (!$id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? intval($input['id']) : 0;
    $type = isset($input['type']) ? $input['type'] : '';
}

if (!$id) {
    die("Missing document ID. Please provide id parameter.");
}

$host = 'localhost';
$dbname = 'geotraverse_erp';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $fileName = '';
    $dbTable = '';
    
    // Determine which table to query
    if ($type === 'project') {
        $dbTable = 'project_documents';
        $stmt = $pdo->prepare("SELECT file_name FROM project_documents WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($type === 'uploaded_report') {
        $dbTable = 'uploaded_reports';
        $stmt = $pdo->prepare("SELECT file_name FROM uploaded_reports WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Try both tables
        $stmt = $pdo->prepare("SELECT file_name, 'project' as source FROM project_documents WHERE id = ? UNION SELECT file_name, 'report' as source FROM uploaded_reports WHERE id = ?");
        $stmt->execute([$id, $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $type = $row['source'];
        }
    }
    
    if (!$row) {
        die("No document found with ID: $id in table: $dbTable");
    }
    
    $fileName = $row['file_name'];
    $baseDir = 'C:/xampp/htdocs/geotraverse/frontend/assets/uploads/';
    
    // Search paths
    $searchPaths = [
        $baseDir . 'reports/' . $fileName,
        $baseDir . 'projects/projects_documents/' . $fileName,
        $baseDir . 'reports/' . basename($fileName),
        $baseDir . 'projects/projects_documents/' . basename($fileName)
    ];
    
    $filePath = null;
    foreach ($searchPaths as $path) {
        if (file_exists($path)) {
            $filePath = $path;
            break;
        }
    }
    
    // If not found, search by pattern
    if (!$filePath) {
        $pattern = pathinfo($fileName, PATHINFO_FILENAME);
        $folders = [$baseDir . 'reports/', $baseDir . 'projects/projects_documents/'];
        
        foreach ($folders as $folder) {
            if (is_dir($folder)) {
                $files = scandir($folder);
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                        if (strpos($file, $pattern) !== false || strpos($pattern, pathinfo($file, PATHINFO_FILENAME)) !== false) {
                            $filePath = $folder . $file;
                            break 2;
                        }
                    }
                }
            }
        }
    }
    
    if (!$filePath || !file_exists($filePath)) {
        // Show debugging info
        echo "DEBUG INFO:\n";
        echo "ID: $id\n";
        echo "Type: $type\n";
        echo "File name from DB: $fileName\n";
        echo "Search pattern: " . pathinfo($fileName, PATHINFO_FILENAME) . "\n\n";
        
        echo "Files in reports folder:\n";
        $reportsDir = $baseDir . 'reports/';
        if (is_dir($reportsDir)) {
            $files = scandir($reportsDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    echo "- $file\n";
                }
            }
        } else {
            echo "Directory not found: $reportsDir\n";
        }
        
        echo "\nFiles in projects_documents folder:\n";
        $docsDir = $baseDir . 'projects/projects_documents/';
        if (is_dir($docsDir)) {
            $files = scandir($docsDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    echo "- $file\n";
                }
            }
        } else {
            echo "Directory not found: $docsDir\n";
        }
        exit;
    }
    
    // Serve the file
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if ($ext === 'pdf') {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
    } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        header('Content-Type: image/' . $ext);
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
    } else {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    }
    readfile($filePath);
    exit;
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>