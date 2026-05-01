<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireAdmin();
$page_title = 'Dashboard';

// ── Stats ──────────────────────────────────────────────────────────────────
$stats = [];

$r = $conn->query("SELECT COUNT(*) AS n FROM orders");
$stats['total_orders'] = (int)$r->fetch_assoc()['n'];

$r = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS n FROM orders WHERE status != 'cancelled'");
$stats['revenue'] = (float)$r->fetch_assoc()['n'];

$r = $conn->query("SELECT COUNT(*) AS n FROM users WHERE role='user'");
$stats['customers'] = (int)$r->fetch_assoc()['n'];

$r = $conn->query("SELECT COUNT(*) AS n FROM products");
$stats['products'] = (int)$r->fetch_assoc()['n'];

$r = $conn->query("SELECT COUNT(*) AS n FROM orders WHERE status='pending'");
$stats['pending'] = (int)$r->fetch_assoc()['n'];

// ── Revenue this month ─────────────────────────────────────────────────────
$r = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS n FROM orders WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW()) AND status!='cancelled'");
$stats['month_revenue'] = (float)$r->fetch_assoc()['n'];

// ── Recent orders ──────────────────────────────────────────────────────────
$recent_orders = $conn->query(
  "SELECT o.id, o.total_amount, o.status, o.payment_method, o.created_at,
          u.name AS customer_name,
          (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) AS item_count
   FROM orders o
   JOIN users u ON u.id = o.user_id
   ORDER BY o.created_at DESC LIMIT 8"
)->fetch_all(MYSQLI_ASSOC);

// ── Low stock variants ─────────────────────────────────────────────────────
$low_stock = $conn->query(
  "SELECT pv.id AS variant_id, pv.stock, p.name AS product_name, p.id AS product_id,
          ps.size_label, pc.color_name, pc.color_hex
   FROM product_variants pv
   JOIN products p ON p.id = pv.product_id
   JOIN product_sizes ps ON ps.id = pv.size_id
   JOIN product_colors pc ON pc.id = pv.color_id
   WHERE pv.stock <= 3
   ORDER BY pv.stock ASC LIMIT 8"
)->fetch_all(MYSQLI_ASSOC);

// ── Revenue chart (last 7 days) ────────────────────────────────────────────
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
  $date = date('Y-m-d', strtotime("-$i days"));
  $r = $conn->prepare("SELECT COALESCE(SUM(total_amount),0) AS rev FROM orders WHERE DATE(created_at)=? AND status!='cancelled'");
  $r->bind_param('s', $date);
  $r->execute();
  $chart_data[] = [
    'date'  => date('D', strtotime($date)),
    'rev'   => (float)$r->get_result()->fetch_assoc()['rev']
  ];
}

// ── Top categories ─────────────────────────────────────────────────────────
$top_cats = $conn->query(
  "SELECT p.category, COUNT(oi.id) AS sold, COALESCE(SUM(oi.quantity * oi.price),0) AS revenue
   FROM order_items oi JOIN products p ON p.id = oi.product_id
   GROUP BY p.category ORDER BY sold DESC LIMIT 5"
)->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<div class="topbar">
  <div>
    <div class="topbar-title">Dashboard</div>
    <div class="topbar-breadcrumb">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?> 👋</div>
  </div>
  <div class="topbar-actions">
    <a href="products.php?action=add" class="btn btn-primary">＋ Add Product</a>
  </div>
</div>

<div class="content">

  <!-- STAT CARDS -->
  <div class="stat-grid">
    <div class="stat-card orange">
      <div class="stat-icon">💰</div>
      <div class="stat-label">Total Revenue</div>
      <div class="stat-value">₱<?= number_format($stats['revenue'], 0) ?></div>
      <div class="stat-sub">₱<?= number_format($stats['month_revenue'], 0) ?> this month</div>
    </div>
    <div class="stat-card green">
      <div class="stat-icon">🛍</div>
      <div class="stat-label">Total Orders</div>
      <div class="stat-value"><?= $stats['total_orders'] ?></div>
      <div class="stat-sub"><?= $stats['pending'] ?> pending</div>
    </div>
    <div class="stat-card blue">
      <div class="stat-icon">👥</div>
      <div class="stat-label">Customers</div>
      <div class="stat-value"><?= $stats['customers'] ?></div>
      <div class="stat-sub">Registered users</div>
    </div>
    <div class="stat-card purple">
      <div class="stat-icon">👕</div>
      <div class="stat-label">Products</div>
      <div class="stat-value"><?= $stats['products'] ?></div>
      <div class="stat-sub">Listed in shop</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;margin-bottom:20px;">

    <!-- Revenue Chart -->
    <div class="card">
      <div class="card-title">📈 Revenue — Last 7 Days</div>
      <div style="display:flex;align-items:flex-end;gap:8px;height:140px;margin-top:8px;" id="bar-chart">
        <?php
        $maxRev = max(array_column($chart_data, 'rev') ?: [1]);
        foreach ($chart_data as $d):
          $pct = $maxRev > 0 ? max(4, round(($d['rev'] / $maxRev) * 100)) : 4;
        ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
          <div style="font-size:9px;color:var(--muted);">₱<?= number_format($d['rev']) ?></div>
          <div style="
            width:100%;background:<?= $d['rev']  > 0 ? 'var(--accent)' : 'var(--border)' ?>;
            height:<?= $pct ?>%;border-radius:4px 4px 0 0;
            transition:height .3s;min-height:4px;
          "></div>
          <div style="font-size:10px;font-weight:600;color:var(--muted);"><?= $d['date'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Top Categories -->
    <div class="card">
      <div class="card-title">🏷️ Top Categories</div>
      <?php if ($top_cats): foreach ($top_cats as $cat): ?>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;font-size:12px;">
        <span style="font-weight:600;"><?= htmlspecialchars($cat['category']) ?></span>
        <div style="text-align:right;">
          <span style="color:var(--accent);font-weight:700;">₱<?= number_format($cat['revenue']) ?></span>
          <span style="color:var(--muted);margin-left:6px;"><?= $cat['sold'] ?> sold</span>
        </div>
      </div>
      <?php endforeach; else: ?>
      <div style="color:var(--muted);font-size:13px;">No sales yet.</div>
      <?php endif; ?>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;">

    <!-- Recent Orders -->
    <div class="card">
      <div class="card-title" style="justify-content:space-between;">
        <span>🛍 Recent Orders</span>
        <a href="orders.php" class="btn btn-secondary btn-sm">View All</a>
      </div>
      <?php if ($recent_orders): ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Customer</th>
              <th>Items</th>
              <th>Total</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($recent_orders as $o): ?>
            <tr style="cursor:pointer;" onclick="window.location='orders.php'">
              <td style="font-weight:700;color:var(--muted);">#<?= $o['id'] ?></td>
              <td style="font-weight:500;"><?= htmlspecialchars($o['customer_name']) ?></td>
              <td style="color:var(--muted);"><?= $o['item_count'] ?></td>
              <td style="font-weight:700;color:var(--accent);">₱<?= number_format($o['total_amount'], 2) ?></td>
              <td style="color:var(--muted);text-transform:uppercase;font-size:11px;"><?= htmlspecialchars($o['payment_method']) ?></td>
              <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
              <td style="color:var(--muted);font-size:11px;"><?= date('M j', strtotime($o['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="empty-state">
        <div class="icon">📭</div>
        <h3>No orders yet</h3>
        <p>Orders will appear here once customers start buying.</p>
      </div>
      <?php endif; ?>
    </div>

    <!-- Low Stock Alerts -->
    <div class="card">
      <div class="card-title" style="justify-content:space-between;">
        <span>⚠️ Low Stock Alerts</span>
        <a href="inventory.php" class="btn btn-secondary btn-sm">Manage</a>
      </div>
      <?php if ($low_stock): foreach ($low_stock as $v): ?>
      <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border);">
        <span style="width:12px;height:12px;border-radius:50%;background:<?= htmlspecialchars($v['color_hex']) ?>;flex-shrink:0;border:1px solid var(--border);"></span>
        <div style="flex:1;min-width:0;">
          <div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
            <?= htmlspecialchars($v['product_name']) ?>
          </div>
          <div style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($v['size_label']) ?> · <?= htmlspecialchars($v['color_name']) ?></div>
        </div>
        <span class="badge <?= $v['stock'] == 0 ? 'badge-out' : 'badge-low' ?>">
          <?= $v['stock'] == 0 ? 'Out' : $v['stock'].' left' ?>
        </span>
      </div>
      <?php endforeach; else: ?>
      <div style="color:var(--muted);font-size:13px;text-align:center;padding:24px;">
        ✅ All variants are well-stocked!
      </div>
      <?php endif; ?>
    </div>
  </div>

</div><!-- .content -->

<?php include 'footer.php'; ?>