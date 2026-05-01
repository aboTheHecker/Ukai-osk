<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireUser();
$page_title = 'My Listings';
include '../includes/header.php';
?>

<div class="page-wrap" style="max-width:960px;">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
    <h1 class="page-title" style="margin:0;">My Listings</h1>
    <a href="sell.php" class="btn btn-green" style="margin-left:auto;">＋ List New Item</a>
  </div>

  <div id="listings-loading" style="text-align:center;padding:60px;color:var(--muted);">
    <div class="spin" style="margin:0 auto 12px;"></div>Loading your listings...
  </div>

  <div id="listings-empty" style="display:none;" class="empty-state">
    <div class="icon">🏷️</div>
    <h3>No listings yet</h3>
    <p>Start selling your pre-loved items!</p>
    <a href="sell.php" class="btn btn-primary">Sell an Item</a>
  </div>

  <div id="listings-grid" style="display:none;
  display:grid;gap:20px;">
</div>

<!-- Confirm delete modal -->
<div id="del-overlay" style="
  display:none;position:fixed;inset:0;background:rgba(26,20,16,.5);
  z-index:300;align-items:center;justify-content:center;">
  <div style="background:var(--white);border-radius:10px;padding:36px 32px;text-align:center;max-width:360px;width:90%;">
    <div style="font-size:40px;margin-bottom:14px;">🗑️</div>
    <h3 style="font-family:'Syne',sans-serif;font-weight:700;margin-bottom:8px;">Delete this listing?</h3>
    <p style="font-size:13px;color:var(--muted);margin-bottom:24px;">
      This will permanently remove the item from the shop. This cannot be undone.
    </p>
    <div style="display:flex;gap:10px;">
      <button onclick="closeDelModal()" class="btn btn-secondary" style="flex:1;justify-content:center;">Cancel</button>
      <button onclick="confirmDelete()" class="btn btn-primary" id="btn-confirm-del"
        style="flex:1;justify-content:center;background:#B83232;">Delete</button>
    </div>
  </div>
</div>

<script>
let pendingDeleteId = null;

async function loadListings() {
  const res  = await fetch('../api/products.php?action=my_listings');
  const data = await res.json();

  document.getElementById('listings-loading').style.display = 'none';

  if (!data.success || !data.products.length) {
    document.getElementById('listings-empty').style.display = 'block';
    return;
  }

  const grid = document.getElementById('listings-grid');
  grid.style.display = 'grid';

  grid.innerHTML = data.products.map(p => {
    const inStock = parseInt(p.total_stock) > 0;
    const condColors = { 'Excellent':'#3A5A40','Good':'#7A6E63','Fair':'#B87A2A','Poor':'#B83232' };
    return `
    <div style="background:var(--white);border:1.5px solid var(--border);border-radius:8px;overflow:hidden;">

      <!-- Image -->
      <div style="aspect-ratio:3/4;overflow:hidden;background:var(--surface);position:relative;">
        ${p.image_url
          ? `<img src="${esc(p.image_url)}" style="width:100%;height:100%;object-fit:cover;"
               onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:40px;opacity:.3\'>👗</div>'">`
          : `<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:40px;opacity:.3">👗</div>`
        }
        <span style="position:absolute;top:8px;left:8px;background:var(--white);border-radius:4px;
          padding:3px 8px;font-size:10px;font-weight:700;color:${condColors[p.condition_label]||'#7A6E63'};
          letter-spacing:.5px;text-transform:uppercase;">${esc(p.condition_label)}</span>
        ${!inStock ? `<div style="position:absolute;inset:0;background:rgba(26,20,16,.4);display:flex;align-items:center;justify-content:center;">
          <span style="background:#1A1410;color:#FAF7F2;padding:5px 14px;border-radius:4px;font-size:11px;font-weight:600;">SOLD OUT</span>
        </div>` : ''}
      </div>

      <!-- Info -->
      <div style="padding:14px;">
        <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;">${esc(p.category)}</div>
        <div style="font-family:'Syne',sans-serif;font-weight:600;font-size:14px;margin-bottom:6px;
          white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(p.name)}</div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
          <span style="font-size:16px;font-weight:700;color:var(--accent);">
            ₱${parseFloat(p.base_price).toLocaleString('en-PH',{minimumFractionDigits:2})}
          </span>
          <span style="font-size:11px;color:${inStock?'var(--accent2)':'var(--muted)'};font-weight:600;">
            ${inStock ? p.total_stock + ' in stock' : 'Sold out'}
          </span>
        </div>
        <div style="display:flex;gap:8px;">
          <a href="sell_edit.php?id=${p.id}" class="btn btn-secondary"
            style="flex:1;justify-content:center;font-size:12px;padding:7px;">✏️ Edit</a>
          <button onclick="askDelete(${p.id})" class="btn"
            style="flex:1;justify-content:center;font-size:12px;padding:7px;
              background:#FEE2E2;color:#991B1B;border:1.5px solid #FECACA;">🗑 Delete</button>
        </div>
      </div>

      <!-- Listed date -->
      <div style="padding:8px 14px;border-top:1px solid var(--border);font-size:11px;color:var(--muted);">
        Listed ${new Date(p.created_at).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})}
      </div>
    </div>`;
  }).join('');
}

function askDelete(id) {
  pendingDeleteId = id;
  document.getElementById('del-overlay').style.display = 'flex';
}
function closeDelModal() {
  pendingDeleteId = null;
  document.getElementById('del-overlay').style.display = 'none';
}
async function confirmDelete() {
  if (!pendingDeleteId) return;
  const btn = document.getElementById('btn-confirm-del');
  btn.disabled = true; btn.textContent = 'Deleting...';

  const fd = new FormData();
  fd.append('action', 'delete');
  fd.append('id', pendingDeleteId);

  const res  = await fetch('../api/products.php', { method:'POST', body:fd });
  const data = await res.json();

  closeDelModal();
  btn.disabled = false; btn.textContent = 'Delete';

  if (data.success) {
    showToast('Listing deleted.', '');
    loadListings();
  } else {
    showToast(data.message || 'Failed to delete.', 'err');
  }
}

function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadListings();
</script>

<?php include '../includes/footer.php'; ?>