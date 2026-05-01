<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireUser();
$page_title = 'My Cart';
include '../includes/header.php';
?>

<div class="page-wrap" style="max-width:900px;">
  <h1 class="page-title">My Cart <small id="cart-item-count"></small></h1>

  <div id="cart-loading" style="text-align:center;padding:60px;color:var(--muted);">
    <div class="spin" style="margin:0 auto 12px;"></div>Loading your cart...
  </div>

  <div id="cart-wrap" style="display:none;">
    <div style="display:grid;grid-template-columns:1fr 300px;gap:32px;align-items:start;">

      <!-- Items list -->
      <div id="cart-items"></div>

      <!-- Order summary -->
      <div style="background:var(--white);border:1.5px solid var(--border);border-radius:8px;padding:24px;position:sticky;top:calc(var(--nav-h) + 16px);">
        <h3 style="font-family:'Syne',sans-serif;font-weight:700;font-size:16px;margin-bottom:20px;">Order Summary</h3>

        <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:20px;font-size:13px;">
          <div style="display:flex;justify-content:space-between;color:var(--muted);">
            <span>Subtotal</span><span id="summary-subtotal">₱0.00</span>
          </div>
          <div style="display:flex;justify-content:space-between;color:var(--muted);">
            <span>Shipping</span><span style="color:var(--accent2);font-weight:600;">Free 🎉</span>
          </div>
          <div style="height:1px;background:var(--border);margin:4px 0;"></div>
          <div style="display:flex;justify-content:space-between;font-weight:700;font-size:16px;">
            <span>Total</span><span id="summary-total" style="color:var(--accent);">₱0.00</span>
          </div>
        </div>

        <a href="checkout.php" id="btn-checkout" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;font-size:14px;">
          Proceed to Checkout
        </a>

        <a href="products.php" style="display:block;text-align:center;margin-top:12px;font-size:12px;color:var(--muted);text-decoration:none;">
          ← Continue Shopping
        </a>
      </div>
    </div>
  </div>

  <div id="cart-empty" style="display:none;">
    <div class="empty-state">
      <div class="icon">🛍</div>
      <h3>Your cart is empty</h3>
      <p>Add some pre-loved finds to your cart!</p>
      <a href="products.php" class="btn btn-primary">Browse Items</a>
    </div>
  </div>
</div>

<script>
async function loadCart() {
  const res  = await fetch('../api/cart.php?action=list');
  const data = await res.json();

  document.getElementById('cart-loading').style.display = 'none';

  if (!data.success || !data.items.length) {
    document.getElementById('cart-empty').style.display = 'block';
    updateCartBadge(0);
    return;
  }

  document.getElementById('cart-wrap').style.display = 'block';
  document.getElementById('cart-item-count').textContent = `(${data.count})`;
  updateCartBadge(data.count);

  renderItems(data.items);
  renderSummary(data.total);
}

function renderItems(items) {
  document.getElementById('cart-items').innerHTML = items.map(item => `
    <div class="cart-row" id="row-${item.id}" style="
      display:flex; gap:14px; padding:16px 0;
      border-bottom:1.5px solid var(--border); align-items:flex-start;
    ">
      <!-- Image -->
      <div style="width:80px;height:100px;flex-shrink:0;border-radius:6px;overflow:hidden;background:var(--surface);">
        ${item.image_url
          ? `<img src="${esc(item.image_url)}" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentElement.innerHTML='<div style=\'font-size:28px;display:flex;align-items:center;justify-content:center;height:100%\'>👗</div>'">`
          : `<div style="font-size:28px;display:flex;align-items:center;justify-content:center;height:100%">👗</div>`
        }
      </div>

      <!-- Info -->
      <div style="flex:1;">
        <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px;">
          ${esc(item.category)}
        </div>
        <div style="font-family:'Syne',sans-serif;font-weight:600;font-size:15px;margin-bottom:6px;">
          ${esc(item.name)}
        </div>
        <div style="display:flex;align-items:center;gap:10px;font-size:12px;color:var(--muted);margin-bottom:10px;">
          <span style="background:var(--tag-bg);padding:2px 8px;border-radius:3px;">Size: ${esc(item.size_label)}</span>
          <span style="display:flex;align-items:center;gap:4px;">
            <span style="width:12px;height:12px;border-radius:50%;background:${esc(item.color_hex)};border:1px solid var(--border);display:inline-block;"></span>
            ${esc(item.color_name)}
          </span>
        </div>
        <!-- Qty controls -->
        <div style="display:flex;align-items:center;gap:8px;">
          <button onclick="updateQty(${item.id}, ${item.quantity - 1}, ${item.stock})"
            style="width:28px;height:28px;border:1.5px solid var(--border);border-radius:4px;background:var(--surface);cursor:pointer;font-size:14px;font-weight:700;">−</button>
          <span style="font-size:14px;font-weight:600;min-width:24px;text-align:center;" id="qty-${item.id}">${item.quantity}</span>
          <button onclick="updateQty(${item.id}, ${item.quantity + 1}, ${item.stock})"
            style="width:28px;height:28px;border:1.5px solid var(--border);border-radius:4px;background:var(--surface);cursor:pointer;font-size:14px;font-weight:700;">+</button>
          <button onclick="removeItem(${item.id})"
            style="margin-left:8px;background:none;border:none;cursor:pointer;color:var(--muted);font-size:12px;text-decoration:underline;">Remove</button>
        </div>
      </div>

      <!-- Price -->
      <div style="font-size:15px;font-weight:700;color:var(--accent);white-space:nowrap;">
        ₱${(parseFloat(item.base_price) * item.quantity).toLocaleString('en-PH',{minimumFractionDigits:2})}
      </div>
    </div>
  `).join('');
}

function renderSummary(total) {
  const fmt = parseFloat(total).toLocaleString('en-PH',{minimumFractionDigits:2});
  document.getElementById('summary-subtotal').textContent = '₱' + fmt;
  document.getElementById('summary-total').textContent    = '₱' + fmt;
}

async function updateQty(cartId, newQty, maxStock) {
  if (newQty < 1) return removeItem(cartId);
  if (newQty > maxStock) { showToast('Max stock reached.', 'err'); return; }

  const fd = new FormData();
  fd.append('action',   'update');
  fd.append('cart_id',  cartId);
  fd.append('quantity', newQty);

  const res  = await fetch('../api/cart.php', { method:'POST', body:fd });
  const data = await res.json();
  if (data.success) loadCart();
}

async function removeItem(cartId) {
  const fd = new FormData();
  fd.append('action',  'remove');
  fd.append('cart_id', cartId);

  const res  = await fetch('../api/cart.php', { method:'POST', body:fd });
  const data = await res.json();
  if (data.success) {
    updateCartBadge(data.count);
    loadCart();
    showToast('Item removed.', '');
  }
}

function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadCart();
</script>

<?php include '../includes/footer.php'; ?>