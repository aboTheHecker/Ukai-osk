<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireAdmin();
$page_title = 'Inventory';

$search = trim($_GET['search'] ?? '');
$filter = trim($_GET['filter'] ?? ''); // 'low', 'out', 'ok'

$where = []; $params = []; $types = '';
if ($search) {
  $where[] = "(p.name LIKE ? OR ps.size_label LIKE ? OR pc.color_name LIKE ?)";
  $like = "%$search%"; $params[]=$like; $params[]=$like; $params[]=$like; $types.='sss';
}
if ($filter === 'out')  { $where[] = "pv.stock = 0"; }
if ($filter === 'low')  { $where[] = "pv.stock > 0 AND pv.stock <= 3"; }
if ($filter === 'ok')   { $where[] = "pv.stock > 3"; }

$sql = "SELECT pv.id AS variant_id, pv.stock,
               p.id AS product_id, p.name AS product_name, p.image_url, p.category,
               ps.size_label, pc.color_name, pc.color_hex
        FROM product_variants pv
        JOIN products p ON p.id = pv.product_id
        JOIN product_sizes ps ON ps.id = pv.size_id
        JOIN product_colors pc ON pc.id = pv.color_id";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY pv.stock ASC, p.name ASC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$variants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Counts
$total_variants = count($variants);
$out_count      = count(array_filter($variants, fn($v) => $v['stock'] == 0));
$low_count      = count(array_filter($variants, fn($v) => $v['stock'] > 0 && $v['stock'] <= 3));
$ok_count       = count(array_filter($variants, fn($v) => $v['stock'] > 3));

include 'header.php';
?>

<div class="topbar">
  <div>
    <div class="topbar-title">Inventory</div>
    <div class="topbar-breadcrumb"><?= $total_variants ?> variants · <?= $out_count ?> out of stock · <?= $low_count ?> low</div>
  </div>
</div>

<div class="content">

  <!-- Summary cards -->
  <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);max-width:600px;margin-bottom:20px;">
    <div class="stat-card orange">
      <div class="stat-icon">⚠️</div>
      <div class="stat-label">Low Stock</div>
      <div class="stat-value"><?= $low_count ?></div>
      <div class="stat-sub">1–3 units left</div>
    </div>
    <div class="stat-card" style="border-top-color:#B83232">
      <div class="stat-icon">❌</div>
      <div class="stat-label">Out of Stock</div>
      <div class="stat-value"><?= $out_count ?></div>
      <div class="stat-sub">0 units</div>
    </div>
    <div class="stat-card green">
      <div class="stat-icon">✅</div>
      <div class="stat-label">Well Stocked</div>
      <div class="stat-value"><?= $ok_count ?></div>
      <div class="stat-sub">4+ units</div>
    </div>
  </div>

  <div class="card">

    <!-- Filters -->
    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
      <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <div class="search-bar">
          <span class="icon">🔍</span>
          <input type="text" name="search" placeholder="Product, size, color..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <?php if ($filter): ?><input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>"><?php endif; ?>
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
        <?php if ($search || $filter): ?><a href="inventory.php" class="btn btn-secondary btn-sm">✕ Clear</a><?php endif; ?>
      </form>

      <div style="margin-left:auto;display:flex;gap:6px;flex-wrap:wrap;">
        <?php foreach ([''=>'All','low'=>'⚠️ Low','out'=>'❌ Out','ok'=>'✅ OK'] as $val=>$label): ?>
        <a href="?filter=<?= $val ?><?= $search?'&search='.urlencode($search):'' ?>"
           style="padding:5px 12px;border-radius:99px;font-size:11px;font-weight:600;text-decoration:none;
                  background:<?= $filter===$val?'var(--accent)':'var(--surface)' ?>;
                  color:<?= $filter===$val?'var(--white)':'var(--muted)' ?>;
                  border:1.5px solid <?= $filter===$val?'var(--accent)':'var(--border)' ?>;">
          <?= $label ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($variants): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Size</th>
            <th>Color</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Adjust</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($variants as $v): ?>
          <?php
            $stock = (int)$v['stock'];
            $sc = $stock === 0 ? 'badge-out' : ($stock <= 3 ? 'badge-low' : 'badge-ok');
            $sl = $stock === 0 ? 'Out of stock' : ($stock <= 3 ? 'Low stock' : 'In stock');
          ?>
          <tr id="row-<?= $v['variant_id'] ?>">
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:38px;border-radius:3px;overflow:hidden;background:var(--surface);flex-shrink:0;">
                  <?php if ($v['image_url']): ?>
                  <img src="<?= htmlspecialchars($v['image_url']) ?>" style="width:100%;height:100%;object-fit:cover;"
                       onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:12px;\'>👗</div>'">
                  <?php else: ?>
                  <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:12px;">👗</div>
                  <?php endif; ?>
                </div>
                <div>
                  <div style="font-weight:600;font-size:13px;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= htmlspecialchars($v['product_name']) ?>
                  </div>
                  <div style="font-size:10px;color:var(--muted);"><?= htmlspecialchars($v['category']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <span style="background:var(--tag-bg);padding:3px 10px;border-radius:4px;font-size:11px;font-weight:600;">
                <?= htmlspecialchars($v['size_label']) ?>
              </span>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:6px;">
                <span style="width:12px;height:12px;border-radius:50%;background:<?= htmlspecialchars($v['color_hex']) ?>;
                             display:inline-block;border:1px solid var(--border);"></span>
                <span style="font-size:12px;"><?= htmlspecialchars($v['color_name']) ?></span>
              </div>
            </td>
            <td>
              <span id="stock-val-<?= $v['variant_id'] ?>" style="font-weight:700;font-size:14px;
                color:<?= $stock===0?'#B83232':($stock<=3?'#B87A2A':'var(--text)') ?>;">
                <?= $stock ?>
              </span>
            </td>
            <td><span class="badge <?= $sc ?>" id="stock-badge-<?= $v['variant_id'] ?>"><?= $sl ?></span></td>
            <td>
              <div style="display:flex;align-items:center;gap:6px;">
                <button class="btn btn-secondary btn-sm" onclick="adjustStock(<?= $v['variant_id'] ?>, -1)">−</button>
                <input type="number" min="0" value="<?= $stock ?>" id="stock-input-<?= $v['variant_id'] ?>"
                  style="width:60px;padding:5px 8px;border:1.5px solid var(--border);border-radius:4px;
                         background:var(--white);font-size:12px;font-weight:600;text-align:center;outline:none;"
                  onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border)'"
                  onchange="setStock(<?= $v['variant_id'] ?>, parseInt(this.value)||0)">
                <button class="btn btn-secondary btn-sm" onclick="adjustStock(<?= $v['variant_id'] ?>, 1)">＋</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <div class="icon">📦</div>
      <h3>No variants found</h3>
      <p><?= $search ? 'Try a different search.' : 'Add products with sizes and colors first.' ?></p>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
async function updateVariantStock(variantId, newStock) {
  newStock = Math.max(0, newStock);
  const fd = new FormData();
  fd.append('action',     'update_variant_stock');
  fd.append('variant_id', variantId);
  fd.append('stock',      newStock);

  const res  = await fetch('../api/products.php', { method:'POST', body:fd });
  const data = await res.json();
  if (!data.success) { showToast('Failed to update stock.', 'err'); return; }

  // Update UI
  document.getElementById('stock-val-' + variantId).textContent = newStock;
  document.getElementById('stock-input-' + variantId).value = newStock;

  const badge = document.getElementById('stock-badge-' + variantId);
  const valEl = document.getElementById('stock-val-' + variantId);
  if (newStock === 0) {
    badge.className = 'badge badge-out'; badge.textContent = 'Out of stock';
    valEl.style.color = '#B83232';
  } else if (newStock <= 3) {
    badge.className = 'badge badge-low'; badge.textContent = 'Low stock';
    valEl.style.color = '#B87A2A';
  } else {
    badge.className = 'badge badge-ok';  badge.textContent = 'In stock';
    valEl.style.color = 'var(--text)';
  }

  showToast('Stock updated ✓', 'ok');
}

function adjustStock(variantId, delta) {
  const input = document.getElementById('stock-input-' + variantId);
  const current = parseInt(input.value) || 0;
  updateVariantStock(variantId, current + delta);
}

function setStock(variantId, val) {
  updateVariantStock(variantId, val);
}
</script>

<?php include 'footer.php'; ?>