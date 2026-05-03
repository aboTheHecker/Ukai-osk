<?php
require_once 'includes/session.php';

// Already logged in? Skip landing
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') header('Location: admin/dashboard.php');
    else header('Location: user/products.php');
    exit;
}

// Pull category count for display
require_once 'includes/db.php';
$cats_res = $conn->query("SELECT category, COUNT(*) AS cnt FROM products GROUP BY category ORDER BY cnt DESC LIMIT 6");
$categories = $cats_res ? $cats_res->fetch_all(MYSQLI_ASSOC) : [];

$total_products = (int)($conn->query("SELECT COUNT(*) AS n FROM products")->fetch_assoc()['n'] ?? 0);
$total_users    = (int)($conn->query("SELECT COUNT(*) AS n FROM users WHERE role='user'")->fetch_assoc()['n'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UKAI-OSK — Pre-Loved Fashion</title>
<link rel="icon" type="image/png" href="/Ukai_logo.PNG">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Manrope:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:      #F2EDE4;
  --surface: #EAE3D8;
  --border:  #D4C9B8;
  --text:    #1A1410;
  --muted:   #7A6E63;
  --accent:  #D4572A;
  --green:   #3A5A40;
  --white:   #FAF7F2;
  --dark:    #100C08;
}

body {
  font-family: 'Manrope', sans-serif;
  background: var(--bg);
  color: var(--text);
  overflow-x: hidden;
}

/* ── NAVBAR ─────────────────── */
.nav {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 48px;
  height: 64px;
  background: rgba(250,247,242,0.92);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border);
}
.nav-logo {
  font-family: 'Syne', sans-serif;
  font-weight: 800;
  font-size: 20px;
  color: var(--text);
  text-decoration: none;
}
.nav-logo span { color: var(--accent); }
.nav-links { display: flex; align-items: center; gap: 8px; }
.nav-link {
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 500;
  color: var(--muted);
  text-decoration: none;
  border-radius: 6px;
  transition: color .15s, background .15s;
}
.nav-link:hover { color: var(--text); background: var(--surface); }
.nav-cta {
  padding: 9px 20px;
  background: var(--accent);
  color: var(--white);
  font-size: 13px;
  font-weight: 600;
  border-radius: 6px;
  text-decoration: none;
  transition: opacity .15s;
  margin-left: 4px;
}
.nav-cta:hover { opacity: .85; }

/* ── HERO ───────────────────── */
.hero {
  min-height: 100vh;
  display: flex;
  align-items: center;
  position: relative;
  overflow: hidden;
  padding: 100px 48px 60px;
}
.hero-bg {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse at 70% 40%, rgba(212,87,42,0.10) 0%, transparent 55%),
    radial-gradient(ellipse at 20% 80%, rgba(58,90,64,0.07) 0%, transparent 50%),
    var(--bg);
  z-index: 0;
}
/* Decorative blobs */
.hero-blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: .35;
  z-index: 0;
  pointer-events: none;
}
.blob-1 { width: 500px; height: 500px; background: #D4572A; top: -100px; right: -100px; }
.blob-2 { width: 400px; height: 400px; background: #3A5A40; bottom: -80px; left: 10%; }

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 640px;
}
.hero-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 99px;
  padding: 5px 14px 5px 10px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: var(--accent);
  margin-bottom: 24px;
}
.hero-eyebrow .dot {
  width: 6px; height: 6px;
  background: var(--accent);
  border-radius: 50%;
  animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%       { opacity: .4; transform: scale(0.7); }
}
.hero h1 {
  font-family: 'Syne', sans-serif;
  font-size: clamp(48px, 7vw, 88px);
  font-weight: 800;
  line-height: .95;
  letter-spacing: -2px;
  color: var(--text);
  margin-bottom: 24px;
}
.hero h1 .line-accent { color: var(--accent); display: block; }
.hero h1 .line-outline {
  -webkit-text-stroke: 2px var(--text);
  color: transparent;
  display: block;
}
.hero-sub {
  font-size: 16px;
  color: var(--muted);
  line-height: 1.6;
  max-width: 440px;
  margin-bottom: 36px;
}
.hero-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.btn-hero-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 15px 30px;
  background: var(--accent);
  color: white;
  font-family: 'Syne', sans-serif;
  font-size: 14px;
  font-weight: 700;
  border-radius: 8px;
  text-decoration: none;
  transition: transform .2s, box-shadow .2s;
  box-shadow: 0 4px 24px rgba(212,87,42,.3);
}
.btn-hero-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 32px rgba(212,87,42,.4);
}
.btn-hero-secondary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 15px 28px;
  background: transparent;
  color: var(--text);
  font-family: 'Syne', sans-serif;
  font-size: 14px;
  font-weight: 600;
  border-radius: 8px;
  text-decoration: none;
  border: 1.5px solid var(--border);
  transition: border-color .2s, background .2s;
}
.btn-hero-secondary:hover { border-color: var(--text); background: var(--surface); }

/* Hero stats */
.hero-stats {
  display: flex;
  gap: 32px;
  margin-top: 48px;
  padding-top: 32px;
  border-top: 1px solid var(--border);
}
.hero-stat-num {
  font-family: 'Syne', sans-serif;
  font-size: 28px;
  font-weight: 800;
  color: var(--text);
  display: block;
}
.hero-stat-label {
  font-size: 11px;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 1px;
  display: block;
  margin-top: 2px;
}

/* Hero floating cards */
.hero-cards {
  position: absolute;
  right: 6%;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  flex-direction: column;
  gap: 14px;
  z-index: 1;
}
.hero-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 4px 20px rgba(26,20,16,.08);
  animation: float 4s ease-in-out infinite;
}
.hero-card:nth-child(2) { animation-delay: -1.5s; margin-left: 24px; }
.hero-card:nth-child(3) { animation-delay: -3s; }
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50%       { transform: translateY(-8px); }
}
.hero-card-icon { font-size: 28px; }
.hero-card-text strong { display: block; font-size: 13px; font-weight: 600; color: var(--text); }
.hero-card-text small  { font-size: 11px; color: var(--muted); }

/* ── SECTION COMMON ─────────── */
section { padding: 80px 48px; }
.section-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  color: var(--accent);
  margin-bottom: 12px;
}
.section-title {
  font-family: 'Syne', sans-serif;
  font-size: clamp(28px, 4vw, 42px);
  font-weight: 800;
  color: var(--text);
  letter-spacing: -1px;
  line-height: 1.1;
  margin-bottom: 12px;
}
.section-sub {
  font-size: 15px;
  color: var(--muted);
  line-height: 1.6;
  max-width: 480px;
}

/* ── CATEGORIES ─────────────── */
.categories-section { background: var(--white); }
.categories-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 36px;
  flex-wrap: wrap;
  gap: 16px;
}
.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 14px;
}
.category-card {
  background: var(--bg);
  border: 1.5px solid var(--border);
  border-radius: 10px;
  padding: 24px 20px;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  gap: 8px;
  transition: all .2s;
  position: relative;
  overflow: hidden;
}
.category-card::before {
  content: '';
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 3px;
  background: var(--accent);
  transform: scaleX(0);
  transition: transform .2s;
}
.category-card:hover {
  border-color: var(--accent);
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(212,87,42,.12);
}
.category-card:hover::before { transform: scaleX(1); }
.category-icon { font-size: 32px; margin-bottom: 4px; }
.category-name {
  font-family: 'Syne', sans-serif;
  font-size: 15px;
  font-weight: 700;
  color: var(--text);
}
.category-count {
  font-size: 11px;
  color: var(--muted);
}

/* Category icons map */
<?php
$catIcons = [
  'Tops' => '👕', 'Bottoms' => '👖', 'Dresses' => '👗',
  'Jackets' => '🧥', 'Shoes' => '👟', 'Accessories' => '👜',
  'Bags' => '🎒', 'Outerwear' => '🧣',
];
?>

/* ── HOW IT WORKS ─────────────── */
.how-section { background: var(--bg); }
.steps-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 24px;
  margin-top: 40px;
}
.step-card {
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 10px;
  padding: 28px 24px;
  position: relative;
}
.step-num {
  font-family: 'Syne', sans-serif;
  font-size: 48px;
  font-weight: 800;
  color: var(--border);
  line-height: 1;
  margin-bottom: 12px;
}
.step-icon { font-size: 28px; margin-bottom: 12px; display: block; }
.step-title {
  font-family: 'Syne', sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 8px;
}
.step-desc { font-size: 13px; color: var(--muted); line-height: 1.6; }

/* ── MARQUEE / TICKER ─────────── */
.ticker {
  background: var(--dark);
  color: var(--white);
  overflow: hidden;
  padding: 14px 0;
}
.ticker-inner {
  display: flex;
  gap: 48px;
  animation: ticker 20s linear infinite;
  white-space: nowrap;
  width: max-content;
}
.ticker-inner:hover { animation-play-state: paused; }
@keyframes ticker { from { transform: translateX(0); } to { transform: translateX(-50%); } }
.ticker-item {
  font-family: 'Syne', sans-serif;
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  opacity: .7;
  display: flex;
  align-items: center;
  gap: 12px;
}
.ticker-dot { width: 5px; height: 5px; background: var(--accent); border-radius: 50%; flex-shrink: 0; }

/* ── WHY UKAI-OSK ─────────────── */
.why-section { background: var(--dark); color: var(--white); }
.why-section .section-title { color: var(--white); }
.why-section .section-sub   { color: rgba(255,255,255,.5); }
.why-section .section-label { color: var(--accent); }
.why-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1px;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.06);
  border-radius: 12px;
  overflow: hidden;
  margin-top: 40px;
}
.why-item {
  background: var(--dark);
  padding: 32px 28px;
  transition: background .2s;
}
.why-item:hover { background: rgba(255,255,255,.04); }
.why-icon { font-size: 32px; margin-bottom: 14px; display: block; }
.why-title {
  font-family: 'Syne', sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: white;
  margin-bottom: 8px;
}
.why-desc { font-size: 13px; color: rgba(255,255,255,.45); line-height: 1.6; }

/* ── CTA ─────────────────────── */
.cta-section {
  background: var(--accent);
  text-align: center;
  padding: 80px 48px;
}
.cta-section .section-label { color: rgba(255,255,255,.7); }
.cta-title {
  font-family: 'Syne', sans-serif;
  font-size: clamp(32px, 5vw, 56px);
  font-weight: 800;
  color: white;
  letter-spacing: -1px;
  margin-bottom: 16px;
}
.cta-sub {
  font-size: 16px;
  color: rgba(255,255,255,.75);
  margin-bottom: 36px;
  max-width: 440px;
  margin-left: auto;
  margin-right: auto;
}
.cta-buttons { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.btn-cta-white {
  padding: 15px 32px;
  background: white;
  color: var(--accent);
  font-family: 'Syne', sans-serif;
  font-size: 14px;
  font-weight: 700;
  border-radius: 8px;
  text-decoration: none;
  transition: transform .15s, box-shadow .15s;
}
.btn-cta-white:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.15); }
.btn-cta-outline {
  padding: 15px 28px;
  background: transparent;
  color: white;
  font-family: 'Syne', sans-serif;
  font-size: 14px;
  font-weight: 600;
  border-radius: 8px;
  text-decoration: none;
  border: 1.5px solid rgba(255,255,255,.5);
  transition: border-color .2s, background .2s;
}
.btn-cta-outline:hover { border-color: white; background: rgba(255,255,255,.08); }

/* ── FOOTER ─────────────────── */
footer {
  background: var(--dark);
  color: rgba(255,255,255,.4);
  padding: 28px 48px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}
footer .footer-brand {
  font-family: 'Syne', sans-serif;
  font-size: 18px;
  font-weight: 800;
  color: white;
}
footer .footer-brand span { color: var(--accent); }
footer p { font-size: 12px; }
.footer-links { display: flex; gap: 20px; }
.footer-links a {
  font-size: 12px;
  color: rgba(255,255,255,.4);
  text-decoration: none;
  transition: color .15s;
}
.footer-links a:hover { color: white; }

/* ── RESPONSIVE ─────────────── */
@media (max-width: 768px) {
  .nav    { padding: 0 20px; }
  .hero   { padding: 100px 20px 60px; }
  .hero-cards { display: none; }
  section { padding: 60px 20px; }
  .cta-section { padding: 60px 20px; }
  footer  { padding: 24px 20px; flex-direction: column; text-align: center; }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="nav">
  <a class="mobile-menu-brand" href="products.php">
      <img src="/Ukai_logo.PNG" alt="UKAI-OSK" style="height: 28px; width: auto; border-radius: 8px; vertical-align: middle; margin-right: 6px;">
    </a>
  <a class="nav-logo" href="index.php">UKAI<span>-OSK</span></a>
  <div class="nav-links">
    <a class="nav-link" href="#categories">Categories</a>
    <a class="nav-link" href="#how">How It Works</a>
    <a class="nav-link" href="login.php">Sign In</a>
    <a class="nav-cta"  href="register.php">Start Shopping →</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-blob blob-1"></div>
  <div class="hero-blob blob-2"></div>

  <div class="hero-content">
    <div class="hero-eyebrow">
      <span class="dot"></span>
      Online Ukay Ukay — Philippines
    </div>
    <h1>
      <span>Pre-loved</span>
      <span class="line-accent">fashion.</span>
      <span class="line-outline">Real finds.</span>
    </h1>
    <p class="hero-sub">
      Discover curated second-hand clothing, shoes, and accessories at unbeatable prices.
      Good condition. Great style. Sustainable choices.
    </p>
    <div class="hero-actions">
      <a class="btn-hero-primary" href="register.php">
        🛍 Shop Now
      </a>
      <a class="btn-hero-secondary" href="login.php">
        Sign In
      </a>
    </div>
    <div class="hero-stats">
      <div>
        <span class="hero-stat-num"><?= $total_products ?>+</span>
        <span class="hero-stat-label">Items Listed</span>
      </div>
      <div>
        <span class="hero-stat-num"><?= $total_users ?>+</span>
        <span class="hero-stat-label">Happy Shoppers</span>
      </div>
      <div>
        <span class="hero-stat-num">₱99+</span>
        <span class="hero-stat-label">Starting Price</span>
      </div>
    </div>
  </div>

  <!-- Floating info cards -->
  <div class="hero-cards">
    <div class="hero-card">
      <span class="hero-card-icon">✅</span>
      <div class="hero-card-text">
        <strong>Quality Checked</strong>
        <small>Every item is verified</small>
      </div>
    </div>
    <div class="hero-card">
      <span class="hero-card-icon">🚀</span>
      <div class="hero-card-text">
        <strong>Fast Delivery</strong>
        <small>Nationwide shipping</small>
      </div>
    </div>
    <div class="hero-card">
      <span class="hero-card-icon">♻️</span>
      <div class="hero-card-text">
        <strong>Eco-Friendly</strong>
        <small>Reduce fashion waste</small>
      </div>
    </div>
  </div>
</section>

<!-- TICKER -->
<div class="ticker">
  <div class="ticker-inner">
    <?php
    $tickerItems = ['Vintage Denim', 'Y2K Fashion', 'Pre-loved Tops', 'Ukay Finds', 'Thrifted Outfits', 'Sustainable Style', 'Branded Preloved', 'Summer Dresses', 'Sneakers', 'Blazers'];
    $all = array_merge($tickerItems, $tickerItems); // duplicate for seamless loop
    foreach ($all as $item): ?>
    <span class="ticker-item">
      <span class="ticker-dot"></span>
      <?= htmlspecialchars($item) ?>
    </span>
    <?php endforeach; ?>
  </div>
</div>

<!-- CATEGORIES -->
<section id="categories" class="categories-section">
  <div class="categories-header">
    <div>
      <div class="section-label">Browse by Type</div>
      <div class="section-title">Shop by<br>Category</div>
    </div>
    <a href="login.php" style="font-size:13px;font-weight:600;color:var(--accent);text-decoration:none;">
      View all items →
    </a>
  </div>

  <?php if ($categories): ?>
  <div class="categories-grid">
    <?php
    foreach ($categories as $cat):
      $icons = ['Tops'=>'👕','Bottoms'=>'👖','Dresses'=>'👗','Jackets'=>'🧥','Shoes'=>'👟','Accessories'=>'👜','Bags'=>'🎒','Outerwear'=>'🧣'];
      $icon  = $icons[$cat['category']] ?? '🏷️';
    ?>
    <a class="category-card" href="login.php">
      <div class="category-icon"><?= $icon ?></div>
      <div class="category-name"><?= htmlspecialchars($cat['category']) ?></div>
      <div class="category-count"><?= $cat['cnt'] ?> item<?= $cat['cnt'] != 1 ? 's' : '' ?></div>
    </a>
    <?php endforeach; ?>
    <a class="category-card" href="login.php" style="justify-content:center;text-align:center;background:var(--surface);border-style:dashed;">
      <div class="category-icon">✨</div>
      <div class="category-name">All Items</div>
      <div class="category-count">Browse everything</div>
    </a>
  </div>
  <?php else: ?>
  <div style="text-align:center;padding:40px;color:var(--muted);">No products yet. Check back soon!</div>
  <?php endif; ?>
</section>

<!-- HOW IT WORKS -->
<section id="how" class="how-section">
  <div class="section-label">Simple Process</div>
  <div class="section-title">How UKAI-OSK Works</div>
  <p class="section-sub">Start shopping pre-loved fashion in just a few easy steps.</p>

  <div class="steps-grid">
    <div class="step-card">
      <div class="step-num">01</div>
      <span class="step-icon">📝</span>
      <div class="step-title">Create an Account</div>
      <p class="step-desc">Sign up for free in under a minute. No fees, no commitment.</p>
    </div>
    <div class="step-card">
      <div class="step-num">02</div>
      <span class="step-icon">🔍</span>
      <div class="step-title">Browse & Discover</div>
      <p class="step-desc">Search by category, size, or color. Filter to find exactly what you want.</p>
    </div>
    <div class="step-card">
      <div class="step-num">03</div>
      <span class="step-icon">🛒</span>
      <div class="step-title">Add to Cart</div>
      <p class="step-desc">Pick your size and color, add items to your cart, and review before checkout.</p>
    </div>
    <div class="step-card">
      <div class="step-num">04</div>
      <span class="step-icon">📦</span>
      <div class="step-title">Order & Receive</div>
      <p class="step-desc">Checkout securely, choose your payment method, and wait for delivery!</p>
    </div>
  </div>
</section>

<!-- WHY UKAI-OSK -->
<section class="why-section">
  <div class="section-label">Why Us</div>
  <div class="section-title">The smart way<br>to shop fashion</div>

  <div class="why-grid">
    <div class="why-item">
      <span class="why-icon">💸</span>
      <div class="why-title">Unbeatable Prices</div>
      <p class="why-desc">Pre-loved fashion means premium brands at a fraction of retail price.</p>
    </div>
    <div class="why-item">
      <span class="why-icon">🔍</span>
      <div class="why-title">Quality Checked</div>
      <p class="why-desc">Every item is described with its condition — Good, Very Good, or Excellent.</p>
    </div>
    <div class="why-item">
      <span class="why-icon">🎨</span>
      <div class="why-title">Size & Color Variants</div>
      <p class="why-desc">Pick the exact size and color you want. Real-time stock availability shown.</p>
    </div>
    <div class="why-item">
      <span class="why-icon">♻️</span>
      <div class="why-title">Sustainable Fashion</div>
      <p class="why-desc">Give pre-loved clothes a second life. Shop responsibly, reduce waste.</p>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="section-label">Ready to find your fit?</div>
  <div class="cta-title">Start your ukay<br>journey today</div>
  <p class="cta-sub">Join hundreds of Filipinos already finding amazing pre-loved fashion on UKAI-OSK.</p>
  <div class="cta-buttons">
    <a class="btn-cta-white" href="register.php">Create Free Account</a>
    <a class="btn-cta-outline" href="login.php">Already have an account</a>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-brand"><a href="admin/login.php" style="text-decoration:none; color:white;"> UKAI<span>-OSK</span></a></div>
  <p>© <?= date('Y') ?> UKAI-OSK. Pre-loved fashion, real savings.</p>
  <div class="footer-links">
    <a href="login.php">Shop</a>
    <a href="register.php">Register</a>
  </div>
</footer>

</body>
</html>