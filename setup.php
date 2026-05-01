<?php
/**
 * SETUP SCRIPT — Visit this ONCE to initialize the database.
 * Delete or rename this file after running.
 */

$host = 'localhost';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Create DB
$conn->query("CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db('ecommerce_db');

// Run SQL file
$sql = file_get_contents(__DIR__ . '/db/ecommerce.sql');
// Split by semicolons, skip comments
$queries = array_filter(array_map('trim', explode(';', $sql)));
$errors  = [];

foreach ($queries as $q) {
    if ($q && !str_starts_with(ltrim($q), '--')) {
        if (!$conn->query($q)) {
            $errors[] = $conn->error . " | Query: " . substr($q, 0, 80);
        }
    }
}

// Create admin account
$admin_email    = 'admin@shop.com';
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_name     = 'Shop Admin';

$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param('s', $admin_email);
$check->execute();
$exists = $check->get_result()->num_rows > 0;

if (!$exists) {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param('sss', $admin_name, $admin_email, $admin_password);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Setup — EcoShop</title>
<style>
  body { font-family: sans-serif; max-width: 600px; margin: 60px auto; padding: 20px; background: #f5f5f5; }
  .card { background: white; border-radius: 12px; padding: 32px; box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
  h1 { color: #2D1810; margin-top: 0; }
  .ok  { color: #2A7A4B; }
  .err { color: #C0392B; background: #FEE; padding: 8px 12px; border-radius: 6px; margin: 6px 0; font-size: 13px; }
  .cred { background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 16px; margin: 16px 0; }
  a.btn { display:inline-block; margin-top:16px; padding: 12px 24px; background:#D4472A; color:white; border-radius:8px; text-decoration:none; font-weight:600; }
</style>
</head>
<body>
<div class="card">
  <h1>🛒 EcoShop Setup</h1>

  <?php if (empty($errors)): ?>
    <p class="ok">✅ Database and tables created successfully.</p>
    <?php if (!$exists): ?>
      <p class="ok">✅ Admin account created.</p>
    <?php else: ?>
      <p class="ok">ℹ️ Admin account already exists.</p>
    <?php endif; ?>
    <p class="ok">✅ Sample products loaded.</p>

    <div class="cred">
      <strong>Admin Login:</strong><br>
      📧 Email: <code>admin@shop.com</code><br>
      🔑 Password: <code>admin123</code>
    </div>

    <p>⚠️ <strong>Delete or rename <code>setup.php</code></strong> after setup is complete!</p>
    <a class="btn" href="index.php">Go to EcoShop →</a>
  <?php else: ?>
    <p style="color:#C0392B;">⚠️ Some queries had issues (this may be okay if tables already exist):</p>
    <?php foreach ($errors as $e): ?>
      <div class="err"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
    <a class="btn" href="index.php">Continue Anyway →</a>
  <?php endif; ?>
</div>
</body>
</html>