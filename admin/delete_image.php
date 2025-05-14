<?php
// Configurare
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// Verifică dacă este o cerere de ștergere
if (!isset($_POST['action']) || $_POST['action'] !== 'delete') {
    $response['message'] = 'Acțiune invalidă';
    echo json_encode($response);
    exit;
}

// Verifică dacă a fost furnizată calea fișierului
if (!isset($_POST['image_path'])) {
    $response['message'] = 'Cale imagine lipsă';
    echo json_encode($response);
    exit;
}

$imagePath = $_POST['image_path'];

// Verificare de securitate - asigură-te că calea este în directorul de uploads
if (strpos($imagePath, '../uploads/') !== 0) {
    $response['message'] = 'Cale imagine invalidă';
    echo json_encode($response);
    exit;
}

// Verifică dacă fișierul există
if (!file_exists($imagePath)) {
    $response['message'] = 'Fișierul nu există';
    echo json_encode($response);
    exit;
}

// Încearcă să ștergi fișierul
if (unlink($imagePath)) {
    $response['success'] = true;
    $response['message'] = 'Fișier șters cu succes';
} else {
    $response['message'] = 'Nu s-a putut șterge fișierul';
}

echo json_encode($response);
?> 