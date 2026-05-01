<?php
require_once '../includes/session.php';

// Already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}
// Logged in as user? Send them to shop
if (isset($_SESSION['user_id'])) {
    header('Location: ../user/products.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — UKAI-OSK</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --bg:     #F2EDE4;
  --border: #D4C9B8;
  --text:   #1A1410;
  --muted:  #7A6E63;
  --accent: #D4572A;
  --white:  #FAF7F2;
  --dark:   #100C08;
  --dark2:  #1E140E;
}

body {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1fr 1fr;
  font-family: 'Manrope', sans-serif;
  color: var(--text);
  overflow: hidden;
}

/* ── LEFT PANEL (dark, branded) ── */
.left-panel {
  background: var(--dark);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 48px;
  position: relative;
  overflow: hidden;
}
.left-panel::before {
  content: '';
  position: absolute;
  top: -120px; right: -80px;
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(212,87,42,.25) 0%, transparent 70%);
  border-radius: 50%;
  pointer-events: none;
}
.left-panel::after {
  content: '';
  position: absolute;
  bottom: -80px; left: -60px;
  width: 320px; height: 320px;
  background: radial-gradient(circle, rgba(58,90,64,.2) 0%, transparent 70%);
  border-radius: 50%;
  pointer-events: none;
}
.panel-brand {
  position: relative;
  z-index: 1;
}
.panel-logo {
  font-family: 'Syne', sans-serif;
  font-size: 28px;
  font-weight: 800;
  color: white;
  text-decoration: none;
  letter-spacing: -0.5px;
  display: block;
  margin-bottom: 8px;
}
.panel-logo span { color: var(--accent); }
.panel-tag {
  display: inline-block;
  background: rgba(212,87,42,.2);
  color: var(--accent);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  padding: 4px 12px;
  border-radius: 20px;
  border: 1px solid rgba(212,87,42,.3);
}
.panel-content {
  position: relative;
  z-index: 1;
}
.panel-content h2 {
  font-family: 'Syne', sans-serif;
  font-size: 36px;
  font-weight: 800;
  color: white;
  line-height: 1.1;
  letter-spacing: -1px;
  margin-bottom: 16px;
}
.panel-content h2 span { color: var(--accent); }
.panel-content p {
  font-size: 14px;
  color: rgba(255,255,255,.45);
  line-height: 1.7;
  max-width: 320px;
}
.panel-features {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.panel-feature {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.06);
  border-radius: 8px;
}
.panel-feature-icon {
  width: 36px; height: 36px;
  background: rgba(212,87,42,.15);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  flex-shrink: 0;
}
.panel-feature-text strong { display: block; font-size: 13px; color: white; font-weight: 600; }
.panel-feature-text small  { font-size: 11px; color: rgba(255,255,255,.35); }

/* ── RIGHT PANEL (form) ── */
.right-panel {
  background: var(--bg);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 48px;
  position: relative;
}
.back-link {
  position: absolute;
  top: 24px; left: 28px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 500;
  color: var(--muted);
  text-decoration: none;
  transition: color .15s;
}
.back-link:hover { color: var(--text); }
.form-wrap { width: 100%; max-width: 360px; }
.form-title {
  font-family: 'Syne', sans-serif;
  font-size: 26px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 4px;
}
.form-sub {
  font-size: 13px;
  color: var(--muted);
  margin-bottom: 28px;
}
.alert {
  display: none;
  padding: 10px 14px;
  border-radius: 6px;
  font-size: 13px;
  margin-bottom: 16px;
}
.alert.show  { display: block; }
.alert.error { background: #FEE; border-left: 3px solid #C0392B; color: #6B2020; }
.form-group { margin-bottom: 14px; }
.form-group label {
  display: block;
  font-size: 10px;
  font-weight: 700;
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
  background: var(--white);
  font-family: 'Manrope', sans-serif;
  font-size: 14px;
  color: var(--text);
  outline: none;
  transition: border-color .2s;
}
.form-group input:focus { border-color: var(--accent); }
.form-group input::placeholder { color: rgba(122,110,99,.5); }
.btn-submit {
  width: 100%;
  padding: 13px;
  margin-top: 6px;
  background: var(--dark);
  color: white;
  border: none;
  border-radius: 6px;
  font-family: 'Syne', sans-serif;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: background .2s;
  letter-spacing: .3px;
}
.btn-submit:hover    { background: #2C1A0E; }
.btn-submit:disabled { background: var(--muted); cursor: not-allowed; }
.notice {
  margin-top: 20px;
  padding: 12px 14px;
  background: rgba(212,87,42,.06);
  border: 1px solid rgba(212,87,42,.15);
  border-radius: 6px;
  font-size: 11px;
  color: var(--muted);
  line-height: 1.6;
}
.notice strong { color: var(--accent); }
.customer-link {
  display: block;
  text-align: center;
  margin-top: 16px;
  font-size: 12px;
  color: var(--muted);
  text-decoration: none;
  transition: color .15s;
}
.customer-link:hover { color: var(--text); }
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
@media (max-width: 768px) {
  body { grid-template-columns: 1fr; }
  .left-panel { display: none; }
  .right-panel { padding: 80px 28px 40px; min-height: 100vh; }
}
</style>
</head>
<body>

<!-- LEFT: Branded panel -->
<div class="left-panel">
  <div class="panel-brand">
    <a class="panel-logo" href="../index.php">UKAI<span>-OSK</span></a>
    <span class="panel-tag">Admin Portal</span>
  </div>

  <div class="panel-content">
    <h2>Manage your<br><span>ukay-ukay</span><br>store.</h2>
    <p>
      Monitor orders, manage products, track inventory,
      and grow your pre-loved fashion business — all from one dashboard.
    </p>
  </div>

  <div class="panel-features">
    <div class="panel-feature">
      <div class="panel-feature-icon">📊</div>
      <div class="panel-feature-text">
        <strong>Dashboard Analytics</strong>
        <small>Revenue, orders & customer stats</small>
      </div>
    </div>
    <div class="panel-feature">
      <div class="panel-feature-icon">📦</div>
      <div class="panel-feature-text">
        <strong>Inventory Management</strong>
        <small>Track stock per size & color</small>
      </div>
    </div>
    <div class="panel-feature">
      <div class="panel-feature-icon">🛍</div>
      <div class="panel-feature-text">
        <strong>Order Fulfillment</strong>
        <small>Update status, view customer info</small>
      </div>
    </div>
  </div>
</div>

<!-- RIGHT: Login form -->
<div class="right-panel">
  <a class="back-link" href="../index.php">← Back to store</a>

  <div class="form-wrap">
    <div class="form-title">Admin Sign In</div>
    <div class="form-sub">Enter your administrator credentials to continue.</div>

    <div class="alert error" id="alert"></div>

    <div class="form-group">
      <label>Email Address</label>
      <input type="email" id="email" placeholder="admin@shop.com" autocomplete="email">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" id="password" placeholder="••••••••" autocomplete="current-password">
    </div>

    <button class="btn-submit" id="btn-login" onclick="handleAdminLogin()">
      Sign In to Dashboard
    </button>

    <div class="notice">
      <strong>Admin access only.</strong> Unauthorized login attempts are logged.
      If you're a customer, please use the <a href="../login.php" style="color:var(--accent);text-decoration:none;">customer portal</a>.
    </div>

    <a class="customer-link" href="../login.php">← Customer login</a>
  </div>
</div>

<script>
document.getElementById('btn-login').dataset.label = 'Sign In to Dashboard';

document.addEventListener('keydown', e => {
  if (e.key === 'Enter') handleAdminLogin();
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

async function handleAdminLogin() {
  const email    = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;

  if (!email || !password) return showAlert('Please fill in all fields.');

  setLoading(true);
  try {
    const fd = new FormData();
    fd.append('action',   'login');
    fd.append('email',    email);
    fd.append('password', password);

    const res  = await fetch('../api/auth.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success) {
      if (data.redirect && data.redirect.includes('/admin/')) {
        window.location.href = data.redirect;
      } else {
        showAlert('Access denied. This portal is for admins only.');
        // Destroy the session via logout
        const fd2 = new FormData();
        fd2.append('action', 'logout');
        await fetch('../api/auth.php', { method: 'POST', body: fd2 });
        setLoading(false);
      }
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