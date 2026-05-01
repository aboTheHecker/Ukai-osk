<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireUser();
$page_title = 'My Orders';
include '../includes/header.php';
?>

<div class="page-wrap" style="max-width:860px;">
  <h1 class="page-title">My Orders</h1>

  <div id="orders-loading" style="text-align:center;padding:60px;color:var(--muted);">
    <div class="spin" style="margin:0 auto 12px;"></div>Loading your orders...
  </div>

  <div id="orders-empty" style="display:none;" class="empty-state">
    <div class="icon">📦</div>
    <h3>No orders yet</h3>
    <p>Your orders will appear here once you place one.</p>
    <a href="products.php" class="btn btn-primary">Start Shopping</a>
  </div>

  <div id="orders-list" style="display:none;display:flex;flex-direction:column;gap:16px;"></div>
</div>

<!-- Order detail modal -->
<div id="detail-overlay" onclick="closeDetail()" style="
  display:none;position:fixed;inset:0;background:rgba(26,20,16,.45);
  z-index:200;align-items:center;justify-content:center;padding:20px;">
  <div id="detail-box" onclick="event.stopPropagation()" style="
    background:var(--white);border-radius:8px;width:100%;max-width:560px;
    max-height:90vh;overflow-y:auto;
    box-shadow:0 24px 64px rgba(0,0,0,.18);">
    <div id="detail-content"></div>
  </div>
</div>

<script>
const STATUS_COLORS = {
  pending:    { bg:'#FEF3C7', text:'#92400E' },
  processing: { bg:'#DBEAFE', text:'#1E40AF' },
  shipped:    { bg:'#EDE9FE', text:'#5B21B6' },
  delivered:  { bg:'#D1FAE5', text:'#065F46' },
  cancelled:  { bg:'#FEE2E2', text:'#991B1B' },
};
const STATUS_LABELS = {
  pending:'⏳ Pending', processing:'🔄 Processing',
  shipped:'🚚 Shipped', delivered:'✅ Delivered', cancelled:'❌ Cancelled'
};

async function loadOrders() {
  const res  = await fetch('../api/orders.php?action=my_orders');
  const data = await res.json();

  document.getElementById('orders-loading').style.display = 'none';

  if (!data.success || !data.orders.length) {
    document.getElementById('orders-empty').style.display = 'block';
    return;
  }

  const container = document.getElementById('orders-list');
  container.style.display = 'flex';

  container.innerHTML = data.orders.map(o => {
    const sc = STATUS_COLORS[o.status] || STATUS_COLORS.pending;
    const sl = STATUS_LABELS[o.status] || o.status;
    const date = new Date(o.created_at).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' });
    return `
    <div style="background:var(--white);border:1.5px solid var(--border);border-radius:8px;overflow:hidden;">

      <!-- Header row -->
      <div style="padding:14px 20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;border-bottom:1px solid var(--border);">
        <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:15px;">
          Order #${o.id}
        </div>
        <div style="font-size:12px;color:var(--muted);">${date}</div>
        <span style="margin-left:auto;background:${sc.bg};color:${sc.text};padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.3px;">
          ${sl}
        </span>
      </div>

      <!-- Items preview -->
      <div style="padding:14px 20px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        ${(o.items||[]).slice(0,4).map(item => `
          <div style="width:48px;height:56px;border-radius:4px;overflow:hidden;background:var(--surface);">
            ${item.image_url
              ? `<img src="${esc(item.image_url)}" style="width:100%;height:100%;object-fit:cover;">`
              : `<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:20px;">👗</div>`
            }
          </div>`).join('')}
        ${o.items.length > 4 ? `<div style="font-size:12px;color:var(--muted);">+${o.items.length-4} more</div>` : ''}

        <div style="margin-left:auto;text-align:right;">
          <div style="font-size:12px;color:var(--muted);">${o.items.length} item${o.items.length>1?'s':''}</div>
          <div style="font-size:17px;font-weight:700;color:var(--accent);">
            ₱${parseFloat(o.total_amount).toLocaleString('en-PH',{minimumFractionDigits:2})}
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div style="padding:10px 20px;border-top:1px solid var(--border);display:flex;align-items:center;gap:10px;">
        <span style="font-size:12px;color:var(--muted);">via ${esc(o.payment_method).toUpperCase()}</span>
        <button onclick="showDetail(${o.id})" style="
          margin-left:auto;padding:6px 16px;border-radius:4px;
          border:1.5px solid var(--border);background:transparent;
          font-family:'Manrope',sans-serif;font-size:12px;font-weight:600;
          cursor:pointer;color:var(--text);transition:.15s;"
          onmouseover="this.style.borderColor='var(--accent)'"
          onmouseout="this.style.borderColor='var(--border)'">
          View Details
        </button>
      </div>
    </div>`;
  }).join('');
}

async function showDetail(orderId) {
  document.getElementById('detail-overlay').style.display = 'flex';
  document.getElementById('detail-content').innerHTML = `
    <div style="padding:40px;text-align:center;color:var(--muted);">
      <div class="spin" style="margin:0 auto 12px;"></div>Loading...
    </div>`;

  const res  = await fetch(`../api/orders.php?action=detail&id=${orderId}`);
  const data = await res.json();
  if (!data.success) { closeDetail(); showToast('Failed to load order.','err'); return; }

  const o  = data.order;
  const sc = STATUS_COLORS[o.status] || STATUS_COLORS.pending;
  const sl = STATUS_LABELS[o.status] || o.status;
  const date = new Date(o.created_at).toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit' });

  document.getElementById('detail-content').innerHTML = `
    <div style="padding:28px;">
      <!-- Header -->
      <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:24px;">
        <div>
          <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:18px;">Order #${o.id}</div>
          <div style="font-size:12px;color:var(--muted);margin-top:3px;">${date}</div>
        </div>
        <button onclick="closeDetail()" style="margin-left:auto;background:var(--surface);border:none;width:30px;height:30px;border-radius:50%;cursor:pointer;font-size:14px;color:var(--muted);">✕</button>
      </div>

      <span style="background:${sc.bg};color:${sc.text};padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;">${sl}</span>

      <!-- Items -->
      <div style="margin-top:20px;border-top:1.5px solid var(--border);padding-top:16px;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:12px;">Items</div>
        <div style="display:flex;flex-direction:column;gap:12px;">
          ${o.items.map(item => `
            <div style="display:flex;gap:12px;align-items:center;">
              <div style="width:50px;height:60px;border-radius:4px;overflow:hidden;background:var(--surface);flex-shrink:0;">
                ${item.image_url
                  ? `<img src="${esc(item.image_url)}" style="width:100%;height:100%;object-fit:cover;">`
                  : `<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:20px;">👗</div>`
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
                  · Qty: ${item.quantity}
                </div>
              </div>
              <div style="font-weight:700;font-size:13px;color:var(--accent);">
                ₱${(item.price * item.quantity).toLocaleString('en-PH',{minimumFractionDigits:2})}
              </div>
            </div>
          `).join('')}
        </div>
      </div>

      <!-- Delivery info -->
      <div style="margin-top:20px;border-top:1.5px solid var(--border);padding-top:16px;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:12px;">Delivery Info</div>
        <div style="font-size:13px;display:flex;flex-direction:column;gap:6px;">
          <div><span style="color:var(--muted);">Name: </span><strong>${esc(o.recipient_name)}</strong></div>
          <div><span style="color:var(--muted);">Contact: </span>${esc(o.recipient_contact)}</div>
          <div><span style="color:var(--muted);">Address: </span>${esc(o.recipient_address)}</div>
          ${o.notes ? `<div><span style="color:var(--muted);">Notes: </span>${esc(o.notes)}</div>` : ''}
        </div>
      </div>

      <!-- Total -->
      <div style="margin-top:20px;border-top:1.5px solid var(--border);padding-top:16px;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:13px;color:var(--muted);">Payment: <strong style="color:var(--text);">${esc(o.payment_method).toUpperCase()}</strong></div>
        <div style="font-size:20px;font-weight:700;color:var(--accent);">
          ₱${parseFloat(o.total_amount).toLocaleString('en-PH',{minimumFractionDigits:2})}
        </div>
      </div>
    </div>`;
}

function closeDetail() {
  document.getElementById('detail-overlay').style.display = 'none';
}

function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadOrders();
</script>

<?php include '../includes/footer.php'; ?>