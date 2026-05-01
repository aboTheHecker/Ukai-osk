<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireAdmin();
$page_title = 'Orders';

// Filters
$status_filter = trim($_GET['status'] ?? '');
$search        = trim($_GET['search'] ?? '');
$page          = max(1, (int)($_GET['page'] ?? 1));
$per_page      = 20;
$offset        = ($page - 1) * $per_page;

// Build query
$where = []; $params = []; $types = '';

if ($status_filter && $status_filter !== 'all') {
  $where[] = "o.status = ?"; $params[] = $status_filter; $types .= 's';
}
if ($search) {
  $where[] = "(u.name LIKE ? OR u.email LIKE ? OR o.id = ?)";
  $like = "%$search%"; $sid = is_numeric($search) ? (int)$search : 0;
  $params[] = $like; $params[] = $like; $params[] = $sid; $types .= 'ssi';
}

$base_sql = "FROM orders o JOIN users u ON u.id = o.user_id";
if ($where) $base_sql .= " WHERE " . implode(' AND ', $where);

$count_sql = "SELECT COUNT(*) AS n " . $base_sql;
$stmt = $conn->prepare($count_sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total_rows = (int)$stmt->get_result()->fetch_assoc()['n'];
$total_pages = max(1, ceil($total_rows / $per_page));

$data_sql = "SELECT o.id, o.total_amount, o.status, o.payment_method,
                    o.recipient_name, o.recipient_address, o.recipient_contact,
                    o.recipient_email, o.notes, o.created_at,
                    u.name AS customer_name, u.email AS customer_email,
                    (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) AS item_count
             " . $base_sql . " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";

$limit_params = array_merge($params, [$per_page, $offset]);
$limit_types  = $types . 'ii';
$stmt2 = $conn->prepare($data_sql);
$stmt2->bind_param($limit_types, ...$limit_params);
$stmt2->execute();
$orders = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

// Status counts
$status_counts = [];
$sc = $conn->query("SELECT status, COUNT(*) AS n FROM orders GROUP BY status");
while ($row = $sc->fetch_assoc()) $status_counts[$row['status']] = $row['n'];
$status_counts['all'] = array_sum($status_counts);

include 'header.php';
?>

<div class="topbar">
  <div>
    <div class="topbar-title">Orders</div>
    <div class="topbar-breadcrumb"><?= $total_rows ?> total orders</div>
  </div>
</div>

<div class="content">

  <!-- Status Tabs -->
  <div style="display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap;">
    <?php foreach (['all'=>'All','pending'=>'⏳ Pending','processing'=>'🔄 Processing','shipped'=>'🚚 Shipped','delivered'=>'✅ Delivered','cancelled'=>'❌ Cancelled'] as $val => $label): ?>
    <a href="?status=<?= $val ?><?= $search ? '&search='.urlencode($search) : '' ?>"
       style="padding:6px 14px;border-radius:99px;font-size:12px;font-weight:600;text-decoration:none;
              background:<?= ($status_filter === $val || ($val === 'all' && !$status_filter)) ? 'var(--accent)' : 'var(--surface)' ?>;
              color:<?= ($status_filter === $val || ($val === 'all' && !$status_filter)) ? 'var(--white)' : 'var(--muted)' ?>;
              border:1.5px solid <?= ($status_filter === $val || ($val === 'all' && !$status_filter)) ? 'var(--accent)' : 'var(--border)' ?>;">
      <?= $label ?> <span style="opacity:.7;">(<?= $status_counts[$val] ?? 0 ?>)</span>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <!-- Search bar -->
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
      <form method="get" style="display:flex;gap:10px;flex:1;flex-wrap:wrap;">
        <?php if ($status_filter): ?><input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>"><?php endif; ?>
        <div class="search-bar" style="max-width:260px;">
          <span class="icon">🔍</span>
          <input type="text" name="search" placeholder="Search customer, email, order #..."
            value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" class="btn btn-secondary btn-sm">Search</button>
        <?php if ($search): ?><a href="?status=<?= urlencode($status_filter) ?>" class="btn btn-secondary btn-sm">✕ Clear</a><?php endif; ?>
      </form>
    </div>

    <?php if ($orders): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td style="font-weight:700;">#<?= $o['id'] ?></td>
            <td>
              <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($o['customer_name']) ?></div>
              <div style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($o['customer_email']) ?></div>
            </td>
            <td style="color:var(--muted);"><?= $o['item_count'] ?> item<?= $o['item_count'] != 1 ? 's' : '' ?></td>
            <td style="font-weight:700;color:var(--accent);">₱<?= number_format($o['total_amount'], 2) ?></td>
            <td style="text-transform:uppercase;font-size:11px;font-weight:600;color:var(--muted);"><?= htmlspecialchars($o['payment_method']) ?></td>
            <td>
              <select class="status-select" data-order-id="<?= $o['id'] ?>"
                style="border:1.5px solid var(--border);border-radius:4px;padding:4px 8px;
                       font-family:'Manrope',sans-serif;font-size:11px;font-weight:600;
                       background:var(--bg);cursor:pointer;outline:none;color:var(--text);">
                <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $st): ?>
                <option value="<?= $st ?>" <?= $o['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td style="font-size:11px;color:var(--muted);">
              <?= date('M j, Y', strtotime($o['created_at'])) ?><br>
              <span><?= date('g:i A', strtotime($o['created_at'])) ?></span>
            </td>
            <td>
              <button class="btn btn-secondary btn-sm" onclick="showOrderDetail(<?= $o['id'] ?>)">View</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="display:flex;justify-content:center;gap:6px;margin-top:20px;flex-wrap:wrap;">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <a href="?page=<?= $i ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>"
         style="padding:6px 12px;border-radius:4px;font-size:12px;font-weight:600;text-decoration:none;
                background:<?= $i===$page?'var(--accent)':'var(--surface)' ?>;
                color:<?= $i===$page?'var(--white)':'var(--muted)' ?>;
                border:1.5px solid <?= $i===$page?'var(--accent)':'var(--border)' ?>;">
        <?= $i ?>
      </a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="empty-state">
      <div class="icon">📭</div>
      <h3>No orders found</h3>
      <p><?= $search ? 'Try adjusting your search.' : 'Orders will appear here once customers place them.' ?></p>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ORDER DETAIL MODAL -->
<div class="modal-overlay" id="detail-modal">
  <div class="modal-box" style="max-width:580px;" onclick="event.stopPropagation()">
    <div class="modal-header">
      <div class="modal-title" id="detail-title">Order Details</div>
      <button class="modal-close" onclick="closeDetail()">✕</button>
    </div>
    <div class="modal-body" id="detail-body">
      <div style="text-align:center;padding:40px;color:var(--muted);">
        <div class="spin" style="margin:0 auto 12px;"></div>Loading...
      </div>
    </div>
  </div>
</div>

<script>
// Live status update
document.querySelectorAll('.status-select').forEach(sel => {
  sel.addEventListener('change', async function() {
    const orderId = this.dataset.orderId;
    const status  = this.value;
    const fd = new FormData();
    fd.append('action',   'update_status');
    fd.append('order_id', orderId);
    fd.append('status',   status);
    const res  = await fetch('../api/orders.php', { method:'POST', body:fd });
    const data = await res.json();
    showToast(data.success ? 'Status updated ✓' : (data.message || 'Failed'), data.success ? 'ok' : 'err');
  });
});

async function showOrderDetail(id) {
  document.getElementById('detail-modal').classList.add('open');
  document.getElementById('detail-title').textContent = `Order #${id}`;
  document.getElementById('detail-body').innerHTML = `<div style="text-align:center;padding:40px;color:var(--muted);"><div class="spin" style="margin:0 auto 12px;"></div>Loading...</div>`;

  const res  = await fetch(`../api/orders.php?action=detail&id=${id}`);
  const data = await res.json();
  if (!data.success) { closeDetail(); showToast('Failed to load order.','err'); return; }

  const o = data.order;
  const statusColors = {
    pending:'#FEF3C7|#92400E', processing:'#DBEAFE|#1E40AF',
    shipped:'#EDE9FE|#5B21B6', delivered:'#D1FAE5|#065F46', cancelled:'#FEE2E2|#991B1B'
  };
  const [sbg, stxt] = (statusColors[o.status] || '#FEF3C7|#92400E').split('|');

  document.getElementById('detail-body').innerHTML = `
    <div style="margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      <span style="background:${sbg};color:${stxt};padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;">${o.status.charAt(0).toUpperCase()+o.status.slice(1)}</span>
      <span style="font-size:12px;color:var(--muted);">${new Date(o.created_at).toLocaleDateString('en-PH',{month:'long',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit'})}</span>
    </div>

    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:10px;">Items</div>
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px;">
      ${(o.items||[]).map(item => `
        <div style="display:flex;gap:12px;align-items:center;padding:10px;background:var(--bg);border-radius:6px;">
          <div style="width:44px;height:52px;border-radius:4px;overflow:hidden;background:var(--surface);flex-shrink:0;">
            ${item.image_url
              ? `<img src="${esc(item.image_url)}" style="width:100%;height:100%;object-fit:cover;">`
              : `<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:18px;">👗</div>`
            }
          </div>
          <div style="flex:1;">
            <div style="font-weight:600;font-size:13px;">${esc(item.product_name)}</div>
            <div style="font-size:11px;color:var(--muted);">
              ${esc(item.size_label)} ·
              <span style="display:inline-flex;align-items:center;gap:3px;">
                <span style="width:10px;height:10px;border-radius:50%;background:${esc(item.color_hex)};display:inline-block;border:1px solid var(--border);"></span>
                ${esc(item.color_name)}
              </span>
              · x${item.quantity}
            </div>
          </div>
          <div style="font-weight:700;font-size:13px;color:var(--accent);">₱${(item.price * item.quantity).toLocaleString('en-PH',{minimumFractionDigits:2})}</div>
        </div>
      `).join('')}
    </div>

    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:10px;">Delivery</div>
    <div style="background:var(--bg);border-radius:6px;padding:14px;font-size:13px;display:flex;flex-direction:column;gap:6px;margin-bottom:20px;">
      <div><span style="color:var(--muted);">Name: </span><strong>${esc(o.recipient_name)}</strong></div>
      <div><span style="color:var(--muted);">Email: </span>${esc(o.recipient_email)}</div>
      <div><span style="color:var(--muted);">Contact: </span>${esc(o.recipient_contact)}</div>
      <div><span style="color:var(--muted);">Address: </span>${esc(o.recipient_address)}</div>
      ${o.notes ? `<div><span style="color:var(--muted);">Notes: </span>${esc(o.notes)}</div>` : ''}
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;border-top:1.5px solid var(--border);padding-top:16px;">
      <div style="font-size:13px;color:var(--muted);">Payment: <strong style="color:var(--text);text-transform:uppercase;">${esc(o.payment_method)}</strong></div>
      <div style="font-size:22px;font-weight:700;color:var(--accent);">₱${parseFloat(o.total_amount).toLocaleString('en-PH',{minimumFractionDigits:2})}</div>
    </div>

    <div style="margin-top:16px;">
      <label class="field-label">Update Status</label>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px;">
        ${['pending','processing','shipped','delivered','cancelled'].map(st => `
          <button onclick="updateStatus(${o.id},'${st}')" class="btn btn-sm" style="
            background:${o.status===st?'var(--accent)':'var(--surface)'};
            color:${o.status===st?'var(--white)':'var(--text)'};
            border:1.5px solid ${o.status===st?'var(--accent)':'var(--border)'};"
          >${st.charAt(0).toUpperCase()+st.slice(1)}</button>
        `).join('')}
      </div>
    </div>
  `;
}

async function updateStatus(orderId, status) {
  const fd = new FormData();
  fd.append('action', 'update_status');
  fd.append('order_id', orderId);
  fd.append('status', status);
  const res  = await fetch('../api/orders.php', { method:'POST', body:fd });
  const data = await res.json();
  if (data.success) {
    showToast('Status updated ✓', 'ok');
    setTimeout(() => location.reload(), 700);
} else {
    showToast(data.message || 'Failed', 'err');
}
}

function closeDetail() {
  document.getElementById('detail-modal').classList.remove('open');
}
document.getElementById('detail-modal').addEventListener('click', closeDetail);
</script>

<?php include 'footer.php'; ?>