<?php
// includes/header.php
if (!isset($page_title)) $page_title = 'UKAI-OSK';
$cart_count = isset($conn) && isset($_SESSION['user_id']) ? getCartCount($conn, $_SESSION['user_id']) : 0;
$current    = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="/Ukai_logo.PNG">
<title><?= htmlspecialchars($page_title) ?> — UKAI-OSK</title>
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
  --nav-h:     58px;
}

html, body {
  min-height: 100%;
}

body {
  background: var(--bg);
  color: var(--text);
  font-family: 'Manrope', sans-serif;
  font-size: 14px;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ── NAV ── */
nav {
  position: sticky;
  top: 0;
  z-index: 100;
  height: var(--nav-h);
  background: var(--white);
  border-bottom: 1.5px solid var(--border);
  display: flex;
  align-items: center;
  padding: 0 24px;
  gap: 16px;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}

.nav-brand-logo {
  height: 36px;
  width: auto;
  border-radius: 10px;
  object-fit: contain;
}

.nav-brand-text {
  font-family: 'Syne', sans-serif;
  font-weight: 800;
  font-size: 18px;
  color: var(--text);
  letter-spacing: -0.5px;
  white-space: nowrap;
}

.nav-brand-text span {
  color: var(--accent);
}

/* Hide brand text and adjust logo on small screens if needed */
@media (max-width: 480px) {
  .nav-brand-text {
    font-size: 15px;
  }
  .nav-brand-logo {
    height: 30px;
  }
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-left: auto;
}

.nav-link {
  padding: 7px 14px;
  border-radius: var(--radius);
  text-decoration: none;
  font-size: 13px;
  font-weight: 500;
  color: var(--muted);
  transition: background .15s, color .15s;
  white-space: nowrap;
}
.nav-link:hover  { background: var(--surface); color: var(--text); }
.nav-link.active { background: var(--tag-bg); color: var(--text); font-weight: 600; }

.nav-sell {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: var(--accent2);
  color: var(--white);
  font-weight: 600;
  padding: 7px 14px;
  border-radius: var(--radius);
  text-decoration: none;
  font-size: 13px;
  font-family: 'Manrope', sans-serif;
  transition: opacity .15s;
  white-space: nowrap;
}
.nav-sell:hover { opacity: .85; }

.cart-btn {
  position: relative;
  padding: 7px 14px;
  border-radius: var(--radius);
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  color: var(--white);
  background: var(--accent);
  transition: opacity .15s;
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
}
.cart-btn:hover { opacity: .88; }
.cart-badge {
  background: var(--white);
  color: var(--accent);
  font-size: 10px;
  font-weight: 700;
  border-radius: 99px;
  padding: 1px 6px;
  min-width: 18px;
  text-align: center;
  line-height: 16px;
}

.nav-user {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: var(--muted);
}
.nav-user-name {
  font-weight: 600;
  color: var(--text);
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.btn-logout {
  padding: 6px 12px;
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  background: transparent;
  font-family: 'Manrope', sans-serif;
  font-size: 12px;
  font-weight: 600;
  color: var(--muted);
  cursor: pointer;
  transition: border-color .15s, color .15s;
  text-transform: uppercase;
  letter-spacing: .5px;
  white-space: nowrap;
}
.btn-logout:hover { border-color: var(--accent); color: var(--accent); }

/* Hamburger — hidden on desktop */
.nav-hamburger {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
  border-radius: 4px;
  color: var(--text);
  font-size: 20px;
  line-height: 1;
  margin-left: auto;
}
.nav-hamburger:hover { background: var(--surface); }

/* ── MOBILE MENU DRAWER ── */
.mobile-menu-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(26,20,16,.45);
  z-index: 150;
}
.mobile-menu-overlay.open { display: block; }

.mobile-menu {
  position: fixed;
  top: 0;
  right: -280px;
  width: 280px;
  height: 100vh;
  background: var(--white);
  border-left: 1.5px solid var(--border);
  z-index: 160;
  display: flex;
  flex-direction: column;
  transition: right .25s ease;
  padding: 20px 16px;
  gap: 4px;
  overflow-y: auto;
}
.mobile-menu.open { right: 0; }

.mobile-menu-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1.5px solid var(--border);
}
.mobile-menu-brand {
  font-family: 'Syne', sans-serif;
  font-weight: 800;
  font-size: 18px;
  color: var(--text);
  text-decoration: none;
}
.mobile-menu-brand span { color: var(--accent); }
.mobile-menu-close {
  width: 30px; height: 30px;
  border: none; background: var(--surface);
  border-radius: 50%; cursor: pointer;
  font-size: 14px; color: var(--muted);
}

.mobile-nav-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 14px;
  border-radius: var(--radius);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  color: var(--muted);
  transition: background .15s, color .15s;
}
.mobile-nav-link:hover  { background: var(--surface); color: var(--text); }
.mobile-nav-link.active { background: var(--tag-bg); color: var(--text); font-weight: 600; }

.mobile-menu-footer {
  margin-top: auto;
  padding-top: 16px;
  border-top: 1.5px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.mobile-user-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--text);
}
.mobile-user-role {
  font-size: 11px;
  color: var(--muted);
}

/* ── SHARED UTILS ── */
.page-wrap { max-width: 1400px; margin: 0 auto; padding: 32px 24px; flex: 1; }
.page-title {
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 26px;
  letter-spacing: -0.5px;
  margin-bottom: 24px;
}
.page-title small {
  font-family: 'Manrope', sans-serif;
  font-size: 13px;
  font-weight: 400;
  color: var(--muted);
  margin-left: 8px;
  letter-spacing: 0;
}

/* Toast */
#toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  background: var(--text);
  color: var(--white);
  padding: 12px 20px;
  border-radius: var(--radius);
  font-size: 13px;
  font-weight: 500;
  z-index: 9999;
  opacity: 0;
  transform: translateY(8px);
  transition: opacity .25s, transform .25s;
  pointer-events: none;
  max-width: 280px;
}
#toast.show { opacity: 1; transform: translateY(0); }
#toast.ok   { background: var(--accent2); }
#toast.err  { background: #B83232; }

/* Spinner */
@keyframes spin { to { transform: rotate(360deg); } }
.spin {
  display: inline-block;
  width: 16px; height: 16px;
  border: 2px solid rgba(0,0,0,.1);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin .6s linear infinite;
}

/* Empty state */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  color: var(--muted);
}
.empty-state .icon { font-size: 48px; margin-bottom: 16px; opacity: .5; }
.empty-state h3 { font-family: 'Syne', sans-serif; font-size: 18px; color: var(--text); margin-bottom: 8px; }
.empty-state p  { font-size: 13px; margin-bottom: 20px; }

/* Btn */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 20px;
  border-radius: var(--radius);
  font-family: 'Manrope', sans-serif;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  border: none;
  transition: opacity .15s, transform .1s;
  letter-spacing: .3px;
}
.btn:active { transform: scale(.98); }
.btn-primary   { background: var(--accent);  color: var(--white); }
.btn-secondary { background: var(--surface); color: var(--text); border: 1.5px solid var(--border); }
.btn-green     { background: var(--accent2); color: var(--white); }
.btn:hover     { opacity: .88; }
.btn:disabled  { opacity: .5; cursor: not-allowed; }

/* Field inputs */
.field-label {
  display: block;
  font-size: 11px; font-weight: 600;
  text-transform: uppercase; letter-spacing: 1px;
  color: #5C3D2E; margin-bottom: 5px;
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

/* ============================================
   PRODUCT GRID - RESPONSIVE
   ============================================ */
   
/* DEFAULT: Desktop 4 columns */
#products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

/* Laptop (1024px - 1200px) - 3 columns */
@media (max-width: 1200px) {
  #products-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
  }
}

/* Tablet (768px - 1024px) - 2 columns */
@media (max-width: 1024px) {
  #products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
}

/* Mobile (≤ 768px) - 2 columns with hamburger menu */
@media (max-width: 768px) {
  nav { padding: 0 16px; gap: 10px; }

  /* Hide desktop nav items */
  .nav-links,
  .nav-sell,
  .nav-user,
  .btn-logout { display: none; }

  /* Show hamburger */
  .nav-hamburger { display: flex; align-items: center; justify-content: center; }

  /* Cart btn — icon only */
  .cart-btn { padding: 7px 10px; }
  .cart-btn-text { display: none; }

  .page-wrap { padding: 16px; }
  .page-title { font-size: 22px; }

  #products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
}

/* Small Mobile (≤ 480px) */
@media (max-width: 480px) {
  .page-title { font-size: 20px; }
  .page-wrap { padding: 12px; }

  #products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }

  #toast { right: 12px; bottom: 12px; left: 12px; max-width: none; }
}
</style>
</head>
<body>

<!-- Mobile menu overlay -->
<div class="mobile-menu-overlay" id="mobile-overlay" onclick="closeMobileMenu()"></div>

<!-- Mobile drawer -->
<div class="mobile-menu" id="mobile-menu">
  <div class="mobile-menu-header">
    <a class="sidebar-brand-logo" href="dashboard.php">
      <img class="sidebar-brand-img" src="/Ukai_logo.PNG" alt="UKAI-OSK">
      <span class="sidebar-brand-text">UKAI<span>-OSK</span></span>
    </a>
    <button class="mobile-menu-close" onclick="closeMobileMenu()">✕</button>
  </div>

  <a class="mobile-nav-link <?= $current==='products.php'?'active':'' ?>" href="products.php">🛍 Shop</a>
  <a class="mobile-nav-link <?= $current==='my_listings.php'?'active':'' ?>" href="my_listings.php">🏷️ My Listings</a>
  <a class="mobile-nav-link <?= $current==='orders.php'?'active':'' ?>" href="orders.php">📦 My Orders</a>
  <a class="mobile-nav-link <?= $current==='sell.php'?'active':'' ?>" href="sell.php" style="color:var(--accent2);font-weight:600;">＋ Sell Item</a>

  <div class="mobile-menu-footer">
    <div>
      <div class="mobile-user-name"><?= htmlspecialchars($_SESSION['name'] ?? 'Guest') ?></div>
      <div class="mobile-user-role">Logged in</div>
    </div>
    <button class="btn btn-secondary" style="width:100%;justify-content:center;" onclick="logout()">Logout</button>
  </div>
</div>

<!-- NAV -->
<nav>
  <a class="nav-brand" href="products.php">
    <img class="nav-brand-logo" src="/Ukai_logo.PNG" alt="UKAI-OSK">
    <span class="nav-brand-text">UKAI<span>-OSK</span></span>
  </a>

  <div class="nav-links">
    <a class="nav-link <?= $current==='products.php'?'active':'' ?>" href="products.php">Shop</a>
    <a class="nav-link <?= $current==='my_listings.php'?'active':'' ?>" href="my_listings.php">My Listings</a>
    <a class="nav-link <?= $current==='orders.php'?'active':'' ?>" href="orders.php">My Orders</a>
  </div>

  <a class="nav-sell" href="sell.php">&#xFF0B; Sell Item</a>

  <a class="cart-btn" href="cart.php" id="nav-cart-link">
    🛍 <span class="cart-btn-text">Cart</span>
    <span class="cart-badge" id="nav-cart-count"><?= $cart_count ?></span>
  </a>

  <div class="nav-user">
    <span class="nav-user-name"><?= htmlspecialchars($_SESSION['name'] ?? 'Guest') ?></span>
    <button class="btn-logout" onclick="logout()">Logout</button>
  </div>

  <!-- Hamburger (mobile only) -->
  <button class="nav-hamburger" onclick="openMobileMenu()">☰</button>
</nav>

<div id="toast"></div>

<script>
let _toastTimer;
function showToast(msg, type='') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'show' + (type ? ' ' + type : '');
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => t.className = '', 2800);
}

function updateCartBadge(n) {
  const el = document.getElementById('nav-cart-count');
  if (el) el.textContent = n;
}

async function logout() {
  const fd = new FormData();
  fd.append('action', 'logout');
  await fetch('../api/auth.php', { method:'POST', body:fd });
  window.location.href = '../index.php';
}

function openMobileMenu() {
  document.getElementById('mobile-menu').classList.add('open');
  document.getElementById('mobile-overlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeMobileMenu() {
  document.getElementById('mobile-menu').classList.remove('open');
  document.getElementById('mobile-overlay').classList.remove('open');
  document.body.style.overflow = '';
}

// Close mobile menu on link click
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.mobile-nav-link').forEach(function(a) {
    a.addEventListener('click', closeMobileMenu);
  });
});
</script>