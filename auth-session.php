<?php
// auth-session.php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'login' || $action === 'signup') {
    $user_id    = $_POST['user_id']    ?? null;
    $user_name  = $_POST['user_name']  ?? null;
    $user_email = $_POST['user_email'] ?? null;
    $user_role  = $_POST['user_role']  ?? null;

    if (!$user_id || !$user_name || !$user_email || !$user_role) {
        echo json_encode(['success' => false, 'message' => 'Missing required user data']);
        exit;
    }

    $_SESSION['user_id']    = $user_id;
    $_SESSION['user_name']  = $user_name;
    $_SESSION['user_email'] = $user_email;
    $_SESSION['user_role']  = $user_role;
    $_SESSION['logged_in']  = true;

    echo json_encode(['success' => true, 'message' => 'User logged in successfully']);
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
exit;