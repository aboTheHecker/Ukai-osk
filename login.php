<?php
require_once 'includes/session.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') header('Location: admin/dashboard.php');
    else header('Location: user/products.php');
    exit;
}

$registered = isset($_GET['registered']);
$expired    = isset($_GET['expired']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — UKAI-OSK</title>
<link rel="icon" type="image/png" href="../Ukai_logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --bg:     #F2EDE4;
  --surface:#EAE3D8;
  --border: #D4C9B8;
  --text:   #1A1410;
  --muted:  #7A6E63;
  --accent: #D4572A;
  --green:  #3A5A40;
  --white:  #FAF7F2;
}
body {
  min-height: 100vh;
  background: var(--bg);
  background-image:
    radial-gradient(ellipse at 25% 60%, rgba(212,87,42,.08) 0%, transparent 55%),
    radial-gradient(ellipse at 80% 20%, rgba(58,90,64,.06) 0%, transparent 50%);
  font-family: 'Manrope', sans-serif;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
.back-link {
  position: fixed;
  top: 20px; left: 24px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 500;
  color: var(--muted);
  text-decoration: none;
  transition: color .15s;
}
.back-link:hover { color: var(--text); }
.container { width: 100%; max-width: 420px; }
.brand {
  text-align: center;
  margin-bottom: 28px;
}
.brand a {
  font-family: 'Syne', sans-serif;
  font-size: 28px;
  font-weight: 800;
  color: var(--text);
  text-decoration: none;
  letter-spacing: -0.5px;
}
.brand a span { color: var(--accent); }
.brand p { font-size: 13px; color: var(--muted); margin-top: 4px; }
.card {
  background: var(--white);
  border-radius: 12px;
  padding: 36px;
  border: 1.5px solid var(--border);
  box-shadow: 0 4px 40px rgba(26,20,16,.08);
  position: relative;
  overflow: hidden;
}
.card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--accent), #B84020, var(--green));
}
.card-title {
  font-family: 'Syne', sans-serif;
  font-size: 22px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 4px;
}
.card-sub {
  font-size: 13px;
  color: var(--muted);
  margin-bottom: 24px;
}
.card-sub a { color: var(--accent); text-decoration: none; font-weight: 600; }
.card-sub a:hover { text-decoration: underline; }
.alert {
  display: none;
  padding: 10px 14px;
  border-radius: 6px;
  font-size: 13px;
  margin-bottom: 16px;
}
.alert.show     { display: block; }
.alert.error    { background: #FEE; border-left: 3px solid #C0392B; color: #6B2020; }
.alert.success  { background: #EFF6EC; border-left: 3px solid var(--green); color: #2A4523; }
.form-group { margin-bottom: 14px; }
.form-group label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: #5C3D2E;
  margin-bottom: 6px;
}
.form-group input {
  width: 100%;
  padding: 11px 14px;
  border: 1.5px solid var(--border);
  border-radius: 6px;
  background: var(--bg);
  font-family: 'Manrope', sans-serif;
  font-size: 14px;
  color: var(--text);
  outline: none;
  transition: border-color .2s, background .2s;
}
.form-group input:focus { border-color: var(--accent); background: var(--white); }
.form-group input::placeholder { color: var(--muted); }
.btn-submit {
  width: 100%;
  padding: 13px;
  margin-top: 8px;
  background: var(--accent);
  color: white;
  border: none;
  border-radius: 6px;
  font-family: 'Syne', sans-serif;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: .5px;
  cursor: pointer;
  transition: opacity .2s;
}
.btn-submit:hover    { opacity: .88; }
.btn-submit:disabled { opacity: .5; cursor: not-allowed; }
.divider {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 20px 0 16px;
  color: var(--muted);
  font-size: 12px;
}
.divider::before, .divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border);
}
.link-row {
  text-align: center;
  font-size: 13px;
  color: var(--muted);
}
.link-row a { color: var(--accent); font-weight: 600; text-decoration: none; }
.link-row a:hover { text-decoration: underline; }
.admin-link {
  display: block;
  text-align: center;
  margin-top: 20px;
  font-size: 12px;
  color: var(--muted);
}
.admin-link a { color: var(--muted); text-decoration: none; }
.admin-link a:hover { color: var(--text); text-decoration: underline; }
@keyframes spin { to { transform: rotate(360deg); } }
.spinner {
  display: inline-block;
  width: 13px; height: 13px;
  border: 2px solid rgba(255,255,255,.4);
  border-top-color: white;
  border-radius: 50%;
  animation: spin .7s linear infinite;
  vertical-align: middle;
  margin-right: 6px;
}
</style>
</head>
<body>

<a class="back-link" href="index.php">← Back to Home</a>

<div class="container">
  <div class="brand">
    <a href="index.php">UKAI<span>-OSK</span></a>
    <p>Pre-loved fashion, real finds.</p>
  </div>

  <div class="card">
    <div class="card-title">Welcome back 👋</div>
    <div class="card-sub">
      Don't have an account? <a href="register.php">Create one free</a>
    </div>

    <?php if ($registered): ?>
    <div class="alert success show">🎉 Account created! You can now sign in.</div>
    <?php elseif ($expired): ?>
    <div class="alert error show">Session expired. Please sign in again.</div>
    <?php endif; ?>

    <div class="alert error" id="alert"></div>

    <div class="form-group">
      <label>Email Address</label>
      <input type="email" id="email" placeholder="you@example.com" autocomplete="email">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" id="password" placeholder="••••••••" autocomplete="current-password">
    </div>
    <button class="btn-submit" id="btn-login" onclick="handleLogin()">Sign In</button>

    <div class="divider">or</div>
    <div class="link-row">
      New here? <a href="register.php">Create a free account</a>
    </div>
  </div>
</div>

<script>
document.getElementById('btn-login').dataset.label = 'Sign In';

document.addEventListener('keydown', e => {
  if (e.key === 'Enter') handleLogin();
});

function showAlert(msg) {
  const el = document.getElementById('alert');
  el.textContent = msg;
  el.className = 'alert error show';
}

function setLoading(on) {
  const btn = document.getElementById('btn-login');
  btn.disabled = on;
  btn.innerHTML = on ? '<span class="spinner"></span>Signing in...' : btn.dataset.label;
}

async function handleLogin() {
  const email    = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;

  if (!email || !password) return showAlert('Please fill in all fields.');

  setLoading(true);
  try {
    const fd = new FormData();
    fd.append('action',   'login');
    fd.append('email',    email);
    fd.append('password', password);

    const res  = await fetch('api/auth.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success) {
      window.location.href = data.redirect;
    } else {
      showAlert(data.message);
      setLoading(false);
    }
  } catch(e) {
    showAlert('Something went wrong. Please try again.');
    setLoading(false);
  }
}
</script>
</body>
</html>