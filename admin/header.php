<?php
// admin/header.php
if (!isset($page_title)) $page_title = 'Admin';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<link rel="icon" type="image/png" href="../Ukai_logo.png">
<title><?= htmlspecialchars($page_title) ?> — UKAI-OSK Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:        #F2EDE4;
  --surface:   #EAE3D8;
  --border:    #D4C9B8;
  --text:      #1A1410;
  --muted:     #7A6E63;
  --accent:    #D4572A;
  --accent2:   #3A5A40;
  --white:     #FAF7F2;
  --tag-bg:    #E0D8CC;
  --radius:    6px;
  --sidebar-w: 220px;
  --nav-h:     56px;
  --danger:    #B83232;
  --warn:      #B87A2A;
  --info:      #1E40AF;
}

html, body { height: 100%; }

body {
  background: var(--bg);
  color: var(--text);
  font-family: 'Manrope', sans-serif;
  font-size: 14px;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ── SIDEBAR OVERLAY (mobile) ── */
.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(26,20,16,.5);
  z-index: 99;
}
.sidebar-overlay.open { display: block; }

/* ── SIDEBAR ── */
.sidebar {
  width: var(--sidebar-w);
  min-height: 100vh;
  background: var(--white);
  border-right: 1.5px solid var(--border);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0;
  z-index: 100;
  transition: transform .25s ease;
}

.sidebar-brand {
  padding: 20px 20px 16px;
  border-bottom: 1.5px solid var(--border);
}

.sidebar-brand-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}

.sidebar-brand-img {
  height: 32px;
  width: auto;
  border-radius: 8px;
  object-fit: contain;
}

.sidebar-brand-text {
  font-family: 'Syne', sans-serif;
  font-weight: 800;
  font-size: 16px;
  color: var(--text);
  letter-spacing: -0.5px;
}

.sidebar-brand-text span {
  color: var(--accent);
}

.sidebar-brand-tag {
  font-size: 9px;
  font-weight: 600;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--muted);
  margin-top: 4px;
  margin-left: 42px;
}

.sidebar-nav {
  flex: 1;
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  overflow-y: auto;
}

.sidebar-section-label {
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--muted);
  padding: 10px 8px 6px;
  margin-top: 6px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 12px;
  border-radius: var(--radius);
  text-decoration: none;
  font-size: 13px;
  font-weight: 500;
  color: var(--muted);
  transition: background .15s, color .15s;
}
.nav-item:hover { background: var(--surface); color: var(--text); }
.nav-item.active {
  background: var(--tag-bg);
  color: var(--text);
  font-weight: 600;
}
.nav-item .icon { font-size: 15px; width: 20px; text-align: center; flex-shrink: 0; }
.nav-item .badge {
  margin-left: auto;
  background: var(--accent);
  color: var(--white);
  font-size: 10px;
  font-weight: 700;
  border-radius: 99px;
  padding: 1px 7px;
  min-width: 20px;
  text-align: center;
}

.sidebar-footer {
  padding: 16px 12px;
  border-top: 1.5px solid var(--border);
}
.sidebar-user {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}
.sidebar-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--accent);
  color: var(--white);
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.sidebar-user-info { min-width: 0; }
.sidebar-user-name {
  font-size: 12px;
  font-weight: 600;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.sidebar-user-role {
  font-size: 10px;
  color: var(--muted);
  letter-spacing: .5px;
}

/* ── LAYOUT WRAPPER ── */
.layout {
  display: flex;
  flex: 1;
  min-height: 100vh;
}

/* ── MAIN ── */
.main-wrap {
  margin-left: var(--sidebar-w);
  flex: 1;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ── TOP BAR ── */
.topbar {
  height: var(--nav-h);
  background: var(--white);
  border-bottom: 1.5px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  gap: 16px;
  position: sticky;
  top: 0;
  z-index: 50;
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.topbar-title {
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 17px;
  color: var(--text);
}

.topbar-breadcrumb {
  font-size: 12px;
  color: var(--muted);
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Hamburger button — hidden on desktop */
.hamburger {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
  border-radius: 4px;
  color: var(--text);
  font-size: 20px;
  line-height: 1;
}
.hamburger:hover { background: var(--surface); }

/* ── CONTENT ── */
.content { padding: 28px; flex: 1; }

/* ── CARDS ── */
.card {
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 8px;
  padding: 24px;
}
.card-title {
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 14px;
  margin-bottom: 18px;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* ── STAT CARDS ── */
.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 28px;
}
.stat-card {
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 8px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  position: relative;
  overflow: hidden;
}
.stat-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
}
.stat-card.orange::before { background: var(--accent); }
.stat-card.green::before  { background: var(--accent2); }
.stat-card.blue::before   { background: #3B82F6; }
.stat-card.purple::before { background: #7C3AED; }

.stat-label {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--muted);
}
.stat-value {
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 28px;
  color: var(--text);
  line-height: 1;
}
.stat-sub {
  font-size: 11px;
  color: var(--muted);
}
.stat-icon {
  position: absolute;
  top: 16px; right: 16px;
  font-size: 28px;
  opacity: .15;
}

/* ── TABLE ── */
.table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  min-width: 560px;
}
thead th {
  text-align: left;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--muted);
  padding: 10px 14px;
  border-bottom: 1.5px solid var(--border);
  white-space: nowrap;
}
tbody td {
  padding: 12px 14px;
  border-bottom: 1px solid var(--border);
  vertical-align: middle;
}
tbody tr:hover { background: var(--surface); }
tbody tr:last-child td { border-bottom: none; }

/* ── BADGES ── */
.badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 99px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .3px;
}
.badge-pending    { background: #FEF3C7; color: #92400E; }
.badge-processing { background: #DBEAFE; color: #1E40AF; }
.badge-shipped    { background: #EDE9FE; color: #5B21B6; }
.badge-delivered  { background: #D1FAE5; color: #065F46; }
.badge-cancelled  { background: #FEE2E2; color: #991B1B; }
.badge-admin      { background: #FEE2E2; color: #991B1B; }
.badge-user       { background: #D1FAE5; color: #065F46; }
.badge-low        { background: #FEF3C7; color: #92400E; }
.badge-out        { background: #FEE2E2; color: #991B1B; }
.badge-ok         { background: #D1FAE5; color: #065F46; }

/* ── BUTTONS ── */
.btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px; border-radius: var(--radius);
  font-family: 'Manrope', sans-serif; font-size: 12px; font-weight: 600;
  text-decoration: none; cursor: pointer; border: none;
  transition: opacity .15s, transform .1s; letter-spacing: .3px;
}
.btn:active { transform: scale(.98); }
.btn-primary   { background: var(--accent); color: var(--white); }
.btn-secondary { background: var(--surface); color: var(--text); border: 1.5px solid var(--border); }
.btn-danger    { background: var(--danger); color: var(--white); }
.btn-green     { background: var(--accent2); color: var(--white); }
.btn-sm        { padding: 5px 10px; font-size: 11px; }
.btn:hover     { opacity: .85; }
.btn:disabled  { opacity: .5; cursor: not-allowed; }

/* ── FORM ── */
.field-label {
  display: block; font-size: 11px; font-weight: 600;
  text-transform: uppercase; letter-spacing: 1px; color: #5C3D2E; margin-bottom: 5px;
}
.field-input {
  width: 100%; padding: 10px 13px;
  border: 1.5px solid var(--border); border-radius: var(--radius);
  background: var(--bg); font-family: 'Manrope', sans-serif;
  font-size: 13px; color: var(--text); outline: none; transition: border-color .15s;
}
.field-input:focus { border-color: var(--accent); background: var(--white); }
.field-input::placeholder { color: var(--muted); }
select.field-input { cursor: pointer; }

/* ── TOAST ── */
#toast {
  position: fixed; bottom: 24px; right: 24px;
  background: var(--text); color: var(--white);
  padding: 12px 20px; border-radius: var(--radius);
  font-size: 13px; font-weight: 500; z-index: 9999;
  opacity: 0; transform: translateY(8px);
  transition: opacity .25s, transform .25s; pointer-events: none;
}
#toast.show { opacity: 1; transform: translateY(0); }
#toast.ok   { background: var(--accent2); }
#toast.err  { background: var(--danger); }

/* ── SPINNER ── */
@keyframes spin { to { transform: rotate(360deg); } }
.spin {
  display: inline-block; width: 16px; height: 16px;
  border: 2px solid rgba(0,0,0,.1); border-top-color: var(--accent);
  border-radius: 50%; animation: spin .6s linear infinite;
}

/* ── EMPTY STATE ── */
.empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
.empty-state .icon { font-size: 40px; margin-bottom: 14px; opacity: .4; }
.empty-state h3 { font-family: 'Syne', sans-serif; font-size: 17px; color: var(--text); margin-bottom: 6px; }
.empty-state p  { font-size: 13px; }

/* ── MODAL ── */
.modal-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(26,20,16,.45); z-index: 200;
  align-items: center; justify-content: center; padding: 20px;
}
.modal-overlay.open { display: flex; }
.modal-box {
  background: var(--white); border-radius: 8px; width: 100%;
  max-height: 90vh; overflow-y: auto;
  box-shadow: 0 24px 64px rgba(0,0,0,.18);
}
.modal-header {
  padding: 20px 24px; border-bottom: 1.5px solid var(--border);
  display: flex; align-items: center; gap: 12px;
}
.modal-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 16px; }
.modal-close {
  margin-left: auto; width: 30px; height: 30px; border-radius: 50%;
  border: none; background: var(--surface); cursor: pointer;
  font-size: 14px; color: var(--muted);
}
.modal-body { padding: 24px; }
.modal-footer {
  padding: 16px 24px; border-top: 1.5px solid var(--border);
  display: flex; gap: 10px; justify-content: flex-end;
}
.modal-body input, .modal-body select, .modal-body textarea {
  width: 100%; padding: 8px 12px; margin-bottom: 10px;
  border: 1.5px solid var(--border); border-radius: var(--radius);
  background: var(--bg); font-family: 'Manrope', sans-serif;
  font-size: 13px;
}

/* ── SEARCH BAR ── */
.search-bar {
  position: relative; flex: 1; max-width: 300px;
}
.search-bar input {
  width: 100%; padding: 8px 12px 8px 34px;
  border: 1.5px solid var(--border); border-radius: var(--radius);
  background: var(--bg); font-family: 'Manrope', sans-serif;
  font-size: 13px; color: var(--text); outline: none; transition: border-color .15s;
}
.search-bar input:focus { border-color: var(--accent); background: var(--white); }
.search-bar .icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; opacity: .5; }

/* ── FOOTER ── */
.admin-footer {
  background: var(--white);
  border-top: 1.5px solid var(--border);
  padding: 14px 28px;
  font-size: 11px;
  color: var(--muted);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: auto;
}
.admin-footer a {
  color: var(--accent);
  text-decoration: none;
  font-weight: 600;
}
.admin-footer a:hover { opacity: .75; }

/* ══════════════════════════════════════════
   RESPONSIVE BREAKPOINTS
══════════════════════════════════════════ */

/* ── Desktop (default styles apply above 1200px) ── */

/* ── Laptop / small desktop (≤ 1200px) ── */
@media (max-width: 1200px) {
  .content { padding: 24px; }
  .stat-grid { gap: 14px; }
}

/* ── Tablet landscape (≤ 992px) ── */
@media (max-width: 992px) {
  :root { --sidebar-w: 200px; }
  .content { padding: 20px; }
  .topbar { padding: 0 20px; }
  
  /* Stat grid - 2 columns */
  .stat-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
}

/* ── Tablet portrait (≤ 768px) ── */
@media (max-width: 768px) {
  /* Sidebar off-canvas */
  .sidebar {
    transform: translateX(-100%);
    box-shadow: 4px 0 24px rgba(0,0,0,.12);
  }
  .sidebar.open { transform: translateX(0); }
  
  /* Main takes full width */
  .main-wrap { margin-left: 0; }
  
  /* Show hamburger */
  .hamburger { display: flex; align-items: center; justify-content: center; }
  
  /* Topbar smaller */
  .topbar { padding: 0 16px; }
  .topbar-breadcrumb { font-size: 11px; }
  .topbar-title { font-size: 15px; }
  
  /* Content padding */
  .content { padding: 16px; }
  
  /* Stat grid */
  .stat-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .stat-value { font-size: 22px; }
  
  /* Cards */
  .card { padding: 16px; margin-bottom: 16px; }
  .card-title { font-size: 13px; margin-bottom: 14px; }
  
  /* Layout fixes for dashboard columns */
  .content > div[style*="grid-template-columns:1fr 320px"],
  .content > div[style*="grid-template-columns:1fr 340px"] {
    display: flex !important;
    flex-direction: column !important;
  }
  .content > div[style*="grid-template-columns:1fr 320px"] > div,
  .content > div[style*="grid-template-columns:1fr 340px"] > div {
    margin-bottom: 16px;
  }
  
  /* Table horizontal scroll */
  .table-wrap { overflow-x: auto; }
  table { min-width: 560px; }
  
  /* Modals */
  .modal-overlay { padding: 12px; align-items: flex-end; }
  .modal-box { max-height: 85vh; border-radius: 12px 12px 0 0; }
  
  /* Buttons */
  .btn { padding: 7px 12px; font-size: 11px; }
  
  /* Admin footer */
  .admin-footer { padding: 12px 16px; flex-direction: column; text-align: center; }
  
  /* Sidebar brand adjustments */
  .sidebar-brand-tag {
    margin-left: 0;
  }
}

/* ── Small mobile (≤ 480px) ── */
@media (max-width: 480px) {
  .content { padding: 12px; }
  .stat-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
  .stat-card { padding: 14px; }
  .stat-value { font-size: 20px; }
  .stat-icon { font-size: 24px; top: 12px; right: 12px; }
  .stat-label { font-size: 9px; }
  
  .topbar-actions .btn span { display: none; }
  
  /* Table font smaller */
  table { font-size: 12px; min-width: 520px; }
  thead th, tbody td { padding: 8px 10px; }
  
  .card-title { font-size: 12px; flex-wrap: wrap; }
  .card-title .btn-sm { font-size: 10px; padding: 4px 8px; }
  
  .modal-body { padding: 16px; }
  
  /* Sidebar brand adjustments for mobile */
  .sidebar-brand-img {
    height: 28px;
  }
  .sidebar-brand-text {
    font-size: 14px;
  }
}
</style>
</head>
<body>

<!-- Sidebar overlay for mobile -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <a class="sidebar-brand-logo" href="dashboard.php">
      <img class="sidebar-brand-img" src="../Ukai_logo.png" alt="UKAI-OSK">
      <span class="sidebar-brand-text">UKAI<span>-OSK</span></span>
    </a>
    <div class="sidebar-brand-tag">Admin Panel</div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section-label">Overview</div>
    <a class="nav-item <?= $current==='dashboard.php'?'active':'' ?>" href="dashboard.php">
      <span class="icon">📊</span> Dashboard
    </a>

    <div class="sidebar-section-label">Catalog</div>
    <a class="nav-item <?= $current==='products.php'?'active':'' ?>" href="products.php">
      <span class="icon">👕</span> Products
    </a>
    <a class="nav-item <?= $current==='inventory.php'?'active':'' ?>" href="inventory.php">
      <span class="icon">📦</span> Inventory
    </a>

    <div class="sidebar-section-label">Sales</div>
    <a class="nav-item <?= $current==='orders.php'?'active':'' ?>" href="orders.php">
      <span class="icon">🛍</span> Orders
      <?php
        if (isset($conn)) {
          $pb = $conn->query("SELECT COUNT(*) AS n FROM orders WHERE status='pending'")->fetch_assoc();
          if ($pb['n'] > 0) echo '<span class="badge">'.$pb['n'].'</span>';
        }
      ?>
    </a>

    <div class="sidebar-section-label">Settings</div>
    <a class="nav-item" href="../index.php" target="_blank">
      <span class="icon">🛒</span> View Shop
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['name'] ?? 'A', 0, 1)) ?></div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></div>
        <div class="sidebar-user-role">Administrator</div>
      </div>
    </div>
    <button class="btn btn-secondary" style="width:100%;justify-content:center;font-size:11px;" onclick="adminLogout()">
      Sign Out
    </button>
  </div>
</aside>

<!-- MAIN -->
<div class="main-wrap">

<!-- Topbar -->
<div class="topbar">
  <div class="topbar-left">
    <button class="hamburger" onclick="openSidebar()">☰</button>
    <div>
      <div class="topbar-breadcrumb" id="topbar-sub"></div>
    </div>
  </div>
  <div class="topbar-actions" id="topbar-actions"></div>
</div>

<div id="toast"></div>

<script>
let _toastTimer;
function showToast(msg, type='') {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className = 'show' + (type ? ' ' + type : '');
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => t.className = '', 2800);
}
async function adminLogout() {
  const fd = new FormData(); fd.append('action', 'logout');
  await fetch('../api/auth.php', { method:'POST', body:fd });
  window.location.href = '../index.php';
}
function esc(s) {
  if (!s && s !== 0) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function openSidebar() {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('sidebar-overlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-overlay').classList.remove('open');
  document.body.style.overflow = '';
}
// Close sidebar on nav link click (mobile)
document.querySelectorAll && document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.sidebar .nav-item').forEach(a => {
    a.addEventListener('click', () => {
      if (window.innerWidth <= 768) closeSidebar();
    });
  });
  // Set topbar sub text
  const sub = document.getElementById('topbar-sub');
  if (sub) {
    const bc = document.querySelector('.topbar-breadcrumb');
    if (bc) sub.textContent = bc.textContent;
  }
  const actions = document.getElementById('topbar-actions');
  if (actions) {
    const originalActions = document.querySelector('.topbar-actions');
    if (originalActions && originalActions !== actions) {
      actions.innerHTML = originalActions.innerHTML;
    }
  }
});
</script>