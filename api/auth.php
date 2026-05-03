<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

$base = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/');

$action = $_POST['action'] ?? '';

// ── REGISTER ──────────────────────────────────────────────────────────────
if ($action === 'register') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';
    $confirm  =      $_POST['confirm']  ?? '';

    if (!$name || !$email || !$password)
        die(json_encode(['success' => false, 'message' => 'All fields are required.']));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        die(json_encode(['success' => false, 'message' => 'Invalid email address.']));
    if (strlen($password) < 6)
        die(json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']));
    if ($password !== $confirm)
        die(json_encode(['success' => false, 'message' => 'Passwords do not match.']));

    $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $chk->bind_param('s', $email);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0)
        die(json_encode(['success' => false, 'message' => 'Email already registered.']));

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->bind_param('sss', $name, $email, $hash);

    if ($stmt->execute()) {
        // After register → go to LOGIN page (not products directly)
        echo json_encode([
            'success'  => true,
            'redirect' => $base . '/login.php?registered=1'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }
    exit;
}

// ── LOGIN ─────────────────────────────────────────────────────────────────
if ($action === 'login') {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (!$email || !$password)
        die(json_encode(['success' => false, 'message' => 'Email and password are required.']));

    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($password, $user['password']))
        die(json_encode(['success' => false, 'message' => 'Invalid email or password.']));

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name']    = $user['name'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role'];

    // Absolute redirect — works whether called from login.php, admin/login.php, anywhere
    if ($user['role'] === 'admin') {
        $redirect = $base . '/admin/dashboard.php';
    } else {
        $redirect = $base . '/user/products.php';
    }

    echo json_encode(['success' => true, 'redirect' => $redirect]);
    exit;
}

// ── LOGOUT ────────────────────────────────────────────────────────────────
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'redirect' => $base . '/login.php']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);