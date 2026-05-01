<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireUser();
$page_title = 'Checkout';
include '../includes/header.php';
?>

<div class="page-wrap" style="max-width:900px;">
  <h1 class="page-title">Checkout</h1>

  <div id="checkout-loading" style="text-align:center;padding:60px;color:var(--muted);">
    <div class="spin" style="margin:0 auto 12px;"></div>Loading...
  </div>

  <div id="checkout-empty" style="display:none;" class="empty-state">
    <div class="icon">🛍</div>
    <h3>Your cart is empty</h3>
    <p>Add items before checking out.</p>
    <a href="products.php" class="btn btn-primary">Browse Items</a>
  </div>

  <form id="checkout-form" style="display:none;" onsubmit="placeOrder(event)">
    <div style="display:grid;grid-template-columns:1fr 320px;gap:32px;align-items:start;">

      <!-- Left: delivery + payment -->
      <div style="display:flex;flex-direction:column;gap:24px;">

        <!-- Delivery Info -->
        <div style="background:var(--white);border:1.5px solid var(--border);border-radius:8px;padding:24px;">
          <h3 style="font-family:'Syne',sans-serif;font-weight:700;font-size:15px;margin-bottom:18px;">
            📦 Delivery Information
          </h3>

          <div style="display:flex;flex-direction:column;gap:14px;">
            <div>
              <label class="field-label">Full Name</label>
              <input class="field-input" type="text" name="recipient_name" id="f-name"
                placeholder="Juan Dela Cruz" required
                value="<?= htmlspecialchars($_SESSION['name'] ?? '') ?>">
            </div>
            <div>
              <label class="field-label">Email Address</label>
              <input class="field-input" type="email" name="recipient_email" id="f-email"
                placeholder="you@email.com" required
                value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
            </div>
            <div>
              <label class="field-label">Contact Number</label>
              <input class="field-input" type="text" name="recipient_contact"
                placeholder="09XXXXXXXXX" required pattern="^[0-9+\s\-]{7,20}$">
            </div>
            <div>
              <label class="field-label">Delivery Address</label>
              <textarea class="field-input" name="recipient_address" rows="3"
                placeholder="House No., Street, Barangay, City, Province"
                required style="resize:vertical;"></textarea>
            </div>
            <div>
              <label class="field-label">Order Notes <span style="font-weight:400;color:var(--muted);text-transform:none;">(optional)</span></label>
              <textarea class="field-input" name="notes" rows="2"
                placeholder="Any special instructions..."
                style="resize:vertical;"></textarea>
            </div>
          </div>
        </div>

        <!-- Payment Method -->
        <div style="background:var(--white);border:1.5px solid var(--border);border-radius:8px;padding:24px;">
          <h3 style="font-family:'Syne',sans-serif;font-weight:700;font-size:15px;margin-bottom:18px;">
            💳 Payment Method
          </h3>

          <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ([
              ['cod',    '💵', 'Cash on Delivery', 'Pay when your order arrives'],
              ['gcash',  '📱', 'GCash',             'Pay via GCash e-wallet'],
              ['maya',   '💙', 'Maya',              'Pay via Maya (PayMaya)'],
            ] as [$val, $icon, $label, $sub]): ?>
            <label style="
              display:flex;align-items:center;gap:14px;padding:14px;
              border:1.5px solid var(--border);border-radius:6px;cursor:pointer;
              transition:border-color .15s;" class="pay-option">
              <input type="radio" name="payment_method" value="<?= $val ?>"
                <?= $val==='cod'?'checked':'' ?>
                style="accent-color:var(--accent);width:16px;height:16px;"
                onchange="document.querySelectorAll('.pay-option').forEach(l=>l.style.borderColor='var(--border)');this.closest('.pay-option').style.borderColor='var(--accent)'">
              <span style="font-size:20px;"><?= $icon ?></span>
              <div>
                <div style="font-weight:600;font-size:13px;"><?= $label ?></div>
                <div style="font-size:11px;color:var(--muted);"><?= $sub ?></div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Right: order summary -->
      <div style="background:var(--white);border:1.5px solid var(--border);border-radius:8px;padding:24px;position:sticky;top:calc(var(--nav-h)+16px);">
        <h3 style="font-family:'Syne',sans-serif;font-weight:700;font-size:15px;margin-bottom:16px;">Your Items</h3>

        <div id="order-items" style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px;"></div>

        <div style="border-top:1.5px solid var(--border);padding-top:16px;display:flex;flex-direction:column;gap:8px;font-size:13px;">
          <div style="display:flex;justify-content:space-between;color:var(--muted);">
            <span>Subtotal</span><span id="co-subtotal">₱0.00</span>
          </div>
          <div style="display:flex;justify-content:space-between;color:var(--muted);">
            <span>Shipping</span><span style="color:var(--accent2);font-weight:600;">Free</span>
          </div>
          <div style="display:flex;justify-content:space-between;font-weight:700;font-size:16px;margin-top:4px;">
            <span>Total</span><span id="co-total" style="color:var(--accent);">₱0.00</span>
          </div>
        </div>

        <button type="submit" id="btn-place" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;margin-top:20px;font-size:14px;">
          Place Order
        </button>
        <a href="cart.php" style="display:block;text-align:center;margin-top:10px;font-size:12px;color:var(--muted);text-decoration:none;">← Back to Cart</a>
      </div>
    </div>
  </form>
</div>

<!-- Success modal -->
<div id="success-modal" style="
  display:none;position:fixed;inset:0;background:rgba(26,20,16,.5);
  z-index:300;align-items:center;justify-content:center;">
  <div style="background:var(--white);border-radius:12px;padding:48px 40px;text-align:center;max-width:400px;width:90%;box-shadow:0 24px 64px rgba(0,0,0,.2);">
    <div style="font-size:52px;margin-bottom:16px;">🎉</div>
    <h2 style="font-family:'Syne',sans-serif;font-weight:700;font-size:22px;margin-bottom:8px;">Order Placed!</h2>
    <p style="color:var(--muted);font-size:14px;margin-bottom:24px;">
      Your order has been received. We'll get it ready for you soon!
    </p>
    <a href="orders.php" class="btn btn-primary" style="width:100%;justify-content:center;">View My Orders</a>
    <a href="products.php" style="display:block;margin-top:10px;font-size:13px;color:var(--muted);text-decoration:none;">Continue Shopping</a>
  </div>
</div>

<style>
.field-label {
  display:block;
  font-size:11px;font-weight:600;text-transform:uppercase;
  letter-spacing:1px;color:var(--brown,#5C3D2E);margin-bottom:5px;
}
.field-input {
  width:100%;padding:10px 13px;
  border:1.5px solid var(--border);border-radius:var(--radius);
  background:var(--bg);font-family:'Manrope',sans-serif;
  font-size:13px;color:var(--text);outline:none;transition:border-color .15s;
}
.field-input:focus { border-color:var(--accent); background:var(--white); }
.field-input::placeholder { color:var(--muted); }
</style>

<script>
let cartData = null;

async function loadCheckout() {
  const res  = await fetch('../api/cart.php?action=list');
  const data = await res.json();
  document.getElementById('checkout-loading').style.display = 'none';

  if (!data.success || !data.items.length) {
    document.getElementById('checkout-empty').style.display = 'block';
    return;
  }

  cartData = data;
  document.getElementById('checkout-form').style.display = 'block';
  renderOrderItems(data.items, data.total);
}

function renderOrderItems(items, total) {
  document.getElementById('order-items').innerHTML = items.map(item => `
    <div style="display:flex;gap:10px;align-items:center;">
      <div style="width:44px;height:52px;border-radius:4px;overflow:hidden;background:var(--surface);flex-shrink:0;">
        ${item.image_url
          ? `<img src="${esc(item.image_url)}" style="width:100%;height:100%;object-fit:cover;">`
          : `<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:18px;">👗</div>`
        }
      </div>
      <div style="flex:1;min-width:0;">
        <div style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(item.name)}</div>
        <div style="font-size:11px;color:var(--muted);">
          ${esc(item.size_label)} · ${esc(item.color_name)} · x${item.quantity}
        </div>
      </div>
      <div style="font-size:13px;font-weight:700;color:var(--accent);white-space:nowrap;">
        ₱${(item.base_price * item.quantity).toLocaleString('en-PH',{minimumFractionDigits:2})}
      </div>
    </div>
  `).join('');

  const fmt = parseFloat(total).toLocaleString('en-PH',{minimumFractionDigits:2});
  document.getElementById('co-subtotal').textContent = '₱' + fmt;
  document.getElementById('co-total').textContent    = '₱' + fmt;
}

async function placeOrder(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-place');
  btn.disabled = true;
  btn.textContent = 'Placing order...';

  const fd = new FormData(document.getElementById('checkout-form'));
  fd.append('action', 'place');

  const res  = await fetch('../api/orders.php', { method:'POST', body:fd });
  const data = await res.json();

  if (data.success) {
    updateCartBadge(0);
    document.getElementById('success-modal').style.display = 'flex';
  } else {
    showToast(data.message || 'Failed to place order.', 'err');
    btn.disabled = false;
    btn.textContent = 'Place Order';
  }
}

function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadCheckout();
</script>

<?php include '../includes/footer.php'; ?>