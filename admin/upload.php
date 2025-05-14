<?php
// Configurare
$targetDir = "../uploads/"; // Directorul unde vor fi stocate imaginile
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Asigură-te că directorul există
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Verifică dacă există subdirectoarele pentru categorie și subcategorie
if (isset($_POST['category']) && isset($_POST['subcategory'])) {
    $category = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['category']); // Sanitizare nume
    $subcategory = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['subcategory']); // Sanitizare nume
    
    $categoryDir = $targetDir . $category . '/';
    $subcategoryDir = $categoryDir . $subcategory . '/';
    
    if (!file_exists($categoryDir)) {
        mkdir($categoryDir, 0755, true);
    }
    
    if (!file_exists($subcategoryDir)) {
        mkdir($subcategoryDir, 0755, true);
    }
    
    $targetDir = $subcategoryDir;
}

// Răspuns
header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'files' => []];

// Verifică dacă există fișiere încărcate
if (!isset($_FILES['images'])) {
    $response['message'] = 'Nu au fost trimise fișiere';
    echo json_encode($response);
    exit;
}

// Procesează fiecare fișier
$files = $_FILES['images'];
$fileCount = is_array($files['name']) ? count($files['name']) : 1;

// Obține URL-ul de bază al site-ului pentru a construi URL-uri corecte
$serverUrlPrefix = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$projectPath = dirname(dirname($_SERVER['REQUEST_URI']));
$baseUrl = $serverUrlPrefix . $projectPath . '/';

for ($i = 0; $i < $fileCount; $i++) {
    $fileName = $files['name'][$i];
    $fileTmpName = $files['tmp_name'][$i];
    $fileSize = $files['size'][$i];
    $fileType = $files['type'][$i];
    $fileError = $files['error'][$i];
    
    // Verifică erori
    if ($fileError !== UPLOAD_ERR_OK) {
        continue;
    }
    
    // Verifică tipul fișierului
    if (!in_array($fileType, $allowedTypes)) {
        continue;
    }
    
    // Verifică dimensiunea fișierului
    if ($fileSize > $maxFileSize) {
        continue;
    }
    
    // Generează un nume unic pentru fișier
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $targetPath = $targetDir . $newFileName;
    
    // Mută fișierul în directorul țintă
    if (move_uploaded_file($fileTmpName, $targetPath)) {
        // Construiește URL-ul corect pentru imagine
        $relativePath = str_replace('../', '', $targetPath);
        $fullUrl = $baseUrl . $relativePath;
        
        $response['files'][] = [
            'name' => $fileName,
            'url' => $fullUrl, // URL complet
            'path' => $targetPath,
            'size' => $fileSize,
            'type' => $fileType
        ];
    }
}

// Actualizează răspunsul
if (count($response['files']) > 0) {
    $response['success'] = true;
    $response['message'] = 'Fișiere încărcate cu succes';
} else {
    $response['message'] = 'Nu s-a putut încărca niciun fișier';
}

echo json_encode($response);
?> 