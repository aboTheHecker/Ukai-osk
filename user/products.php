<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireUser();
$page_title = 'Shop';
include '../includes/header.php';
?>

<div class="page-wrap">

  <!-- Search + Filter bar -->
  <div style="display:flex; align-items:center; gap:12px; margin-bottom:28px; flex-wrap:wrap;">
    <h1 class="page-title" style="margin:0; flex:none;">Browse</h1>

    <div style="flex:1; min-width:200px; position:relative;">
      <input type="text" id="search-input" placeholder="Search clothes..."
        style="width:100%; padding:10px 14px 10px 38px; border:1.5px solid var(--border);
               border-radius:var(--radius); background:var(--white); font-family:'Manrope',sans-serif;
               font-size:13px; color:var(--text); outline:none; transition:border-color .15s;"
        oninput="debounceLoad()"
        onfocus="this.style.borderColor='var(--accent)'"
        onblur="this.style.borderColor='var(--border)'">
      <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.5;font-size:15px;">🔍</span>
    </div>

    <select id="cat-filter" onchange="loadProducts()"
      style="padding:10px 14px; border:1.5px solid var(--border); border-radius:var(--radius);
             background:var(--white); font-family:'Manrope',sans-serif; font-size:13px;
             color:var(--text); outline:none; cursor:pointer;">
      <option value="">All Categories</option>
    </select>

    <div id="results-info" style="font-size:12px; color:var(--muted); white-space:nowrap;"></div>
  </div>

  <!-- Grid -->
  <div id="products-grid" style="
  display:grid;
  gap:20px;
">
    <div style="grid-column:1/-1; text-align:center; padding:60px; color:var(--muted);">
      <div class="spin" style="margin:0 auto 12px;"></div>
      Loading products...
    </div>
  </div>
</div>

<!-- ── PRODUCT MODAL ── -->
<div id="modal-overlay" onclick="closeModal()" style="
  display:none; position:fixed; inset:0; background:rgba(26,20,16,.45);
  z-index:200; align-items:center; justify-content:center; padding:20px;
">
  <div id="modal-box" onclick="event.stopPropagation()" style="
    background:var(--white); border-radius:8px; width:100%; max-width:600px;
    max-height:90vh; overflow-y:auto; position:relative;
    box-shadow:0 24px 64px rgba(0,0,0,.18);
  ">
    <button onclick="closeModal()" style="
      position:absolute; top:14px; right:14px; width:32px; height:32px;
      border:none; background:var(--surface); border-radius:50%; cursor:pointer;
      font-size:16px; display:flex; align-items:center; justify-content:center;
      color:var(--muted); z-index:1;
    ">✕</button>
    <div id="modal-content"></div>
  </div>
</div>

<script>
let selectedSize  = null;
let selectedColor = null;
// ── Load categories ──
async function loadCategories() {
  const res  = await fetch('../api/products.php?action=categories');
  const data = await res.json();
  const sel  = document.getElementById('cat-filter');
  (data.categories || []).forEach(c => {
    const opt = document.createElement('option');
    opt.value = c; opt.textContent = c;
    sel.appendChild(opt);
  });
}

// ── Load products ──
async function loadProducts() {
  const search = document.getElementById('search-input').value.trim();
  const cat    = document.getElementById('cat-filter').value;
  const grid   = document.getElementById('products-grid');

  const params = new URLSearchParams({ action:'list' });
  if (search) params.append('search', search);
  if (cat)    params.append('category', cat);

  const res  = await fetch('../api/products.php?' + params);
  const data = await res.json();

  const products = data.products || [];
  document.getElementById('results-info').textContent =
    products.length ? products.length + ' item' + (products.length>1?'s':'') + ' found' : '';

  if (!products.length) {
    grid.innerHTML = `
      <div class="empty-state" style="grid-column:1/-1">
        <div class="icon">👕</div>
        <h3>No items found</h3>
        <p>Try a different search or category.</p>
      </div>`;
    return;
  }

  grid.innerHTML = products.map(p => {
    const inStock = parseInt(p.total_stock) > 0;
    const condColors = { 'Excellent':'#3A5A40', 'Good':'#7A6E63', 'Fair':'#B87A2A', 'Poor':'#B83232' };
    const condColor  = condColors[p.condition_label] || '#7A6E63';
    return `
    <div class="product-card" onclick="openProduct(${p.id})" style="
      background:var(--white); border:1.5px solid var(--border); border-radius:8px;
      overflow:hidden; cursor:pointer; transition:transform .15s, box-shadow .15s;
    " onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.1)'"
       onmouseout ="this.style.transform='';this.style.boxShadow=''">

      <!-- Image -->
      <div style="aspect-ratio:3/4; overflow:hidden; background:var(--surface); position:relative;">
        ${p.image_url
          ? `<img src="${escHtml(p.image_url)}" alt="${escHtml(p.name)}"
               style="width:100%;height:100%;object-fit:cover;"
               onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:40px;opacity:.3\'>👗</div>'">`
          : `<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:40px;opacity:.3">👗</div>`
        }
        ${!inStock ? `<div style="position:absolute;inset:0;background:rgba(26,20,16,.45);display:flex;align-items:center;justify-content:center;">
          <span style="background:#1A1410;color:#FAF7F2;padding:6px 14px;border-radius:4px;font-size:12px;font-weight:600;letter-spacing:.5px;">SOLD OUT</span>
        </div>` : ''}
        <span style="position:absolute;top:8px;left:8px;background:var(--white);border-radius:4px;
          padding:3px 8px;font-size:10px;font-weight:700;color:${condColor};letter-spacing:.5px;
          text-transform:uppercase;">${escHtml(p.condition_label)}</span>
      </div>

      <!-- Info -->
      <div style="padding:14px;">
        <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">
          ${escHtml(p.category)}
        </div>
        <div style="font-family:'Syne',sans-serif;font-weight:600;font-size:14px;margin-bottom:8px;
          white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
          ${escHtml(p.name)}
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;">
          <span style="font-size:16px;font-weight:700;color:var(--accent);">
            ₱${parseFloat(p.base_price).toLocaleString('en-PH',{minimumFractionDigits:2})}
          </span>
          ${inStock
            ? `<span style="font-size:11px;color:var(--accent2);font-weight:600;">${p.total_stock} left</span>`
            : `<span style="font-size:11px;color:var(--muted);">Sold out</span>`
          }
        </div>
      </div>
    </div>`;
  }).join('');
}

// ── Open product modal ──
async function openProduct(id) {
  document.getElementById('modal-overlay').style.display = 'flex';
  document.getElementById('modal-content').innerHTML = `
    <div style="padding:40px;text-align:center;color:var(--muted);">
      <div class="spin" style="margin:0 auto 12px;"></div>Loading...
    </div>`;

  const res  = await fetch(`../api/products.php?action=detail&id=${id}`);
  const data = await res.json();
  if (!data.success) return;
  renderModal(data.product);
}

function renderModal(p) {

  function getVariantKey() { return selectedSize + '-' + selectedColor; }
  function getVariant()    { return p.variant_map?.[getVariantKey()] || null; }

  function buildHTML() {
    const v = getVariant();
    const stock = v ? v.stock : 0;

    const sizeBtns = p.sizes.map(s => `
      <button onclick="selectSize(${s.id})" id="sz-${s.id}" style="
        padding:7px 14px; border-radius:4px; font-family:'Manrope',sans-serif;
        font-size:13px; font-weight:600; cursor:pointer; transition:.15s;
        border:1.5px solid ${selectedSize===s.id?'var(--accent)':'var(--border)'};
        background:${selectedSize===s.id?'var(--accent)':'transparent'};
        color:${selectedSize===s.id?'var(--white)':'var(--text)'};
      ">${escHtml(s.size_label)}</button>`).join('');

    const colorBtns = p.colors.map(c => `
      <button onclick="selectColor(${c.id})" id="cl-${c.id}" title="${escHtml(c.color_name)}" style="
        width:28px; height:28px; border-radius:50%; cursor:pointer;
        background:${escHtml(c.color_hex)};
        border:${selectedColor===c.id?'3px solid var(--accent)':'2px solid var(--border)'};
        outline:${selectedColor===c.id?'2px solid var(--white)':'none'};
        outline-offset:1px; transition:.15s;
      "></button>`).join('');

    return `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;" id="modal-inner">

      <!-- Left: image -->
      <div style="aspect-ratio:3/4;background:var(--surface);border-radius:8px 0 0 8px;overflow:hidden;">
        ${p.image_url
          ? `<img src="${escHtml(p.image_url)}" style="width:100%;height:100%;object-fit:cover;">`
          : `<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:56px;opacity:.3">👗</div>`
        }
      </div>

      <!-- Right: details -->
      <div style="padding:28px;display:flex;flex-direction:column;gap:14px;">
        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">
            ${escHtml(p.category)} · ${escHtml(p.condition_label)}
          </div>
          <h2 style="font-family:'Syne',sans-serif;font-weight:700;font-size:20px;line-height:1.2;">
            ${escHtml(p.name)}
          </h2>
          <div style="font-size:22px;font-weight:700;color:var(--accent);margin-top:8px;">
            ₱${parseFloat(p.base_price).toLocaleString('en-PH',{minimumFractionDigits:2})}
          </div>
        </div>

        ${p.description ? `<p style="font-size:13px;color:var(--muted);line-height:1.6;">${escHtml(p.description)}</p>` : ''}

        <!-- Size -->
        <div>
          <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Size</div>
          <div style="display:flex;flex-wrap:wrap;gap:6px;">${sizeBtns}</div>
        </div>

        <!-- Color -->
        <div>
          <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Color</div>
          <div style="display:flex;flex-wrap:wrap;gap:8px;">${colorBtns}</div>
        </div>

        <!-- Stock status -->
        <div id="stock-status" style="font-size:12px;color:var(--muted);">
          ${selectedSize && selectedColor
            ? (stock > 0 ? `<span style="color:var(--accent2);font-weight:600;">${stock} in stock</span>`
                         : `<span style="color:#B83232;font-weight:600;">Out of stock</span>`)
            : 'Select size and color'}
        </div>

        <!-- Add to cart -->
        <button id="btn-add-cart" onclick="addToCart(${p.id})"
          ${(!selectedSize || !selectedColor || stock < 1) ? 'disabled' : ''}
          class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;">
          Add to Cart
        </button>
      </div>
    </div>`;
  }

  document.getElementById('modal-content').innerHTML = buildHTML();

  window.selectSize = function(id) {
    selectedSize = id;
    document.getElementById('modal-content').innerHTML = buildHTML();
    // re-attach after re-render
    attachModalHandlers();
  };
  window.selectColor = function(id) {
    selectedColor = id;
    document.getElementById('modal-content').innerHTML = buildHTML();
    attachModalHandlers();
  };
  window.addToCart = async function(productId) {
    const v = getVariant();
    if (!v) return;
    const btn = document.getElementById('btn-add-cart');
    btn.disabled = true;
    btn.textContent = 'Adding...';
    const fd = new FormData();
    fd.append('action',     'add');
    fd.append('product_id', productId);
    fd.append('variant_id', v.id);
    fd.append('quantity',   1);
    const res  = await fetch('../api/cart.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) {
      updateCartBadge(data.count);
      showToast('Added to cart! 🛍', 'ok');
      closeModal();
      selectedSize = null;
      selectedColor = null;
    } else {
      showToast(data.message || 'Failed to add.', 'err');
      btn.disabled = false;
      btn.textContent = 'Add to Cart';
    }
  };

  function attachModalHandlers() {
    // handlers are re-set via window.selectSize/selectColor above
  }
}

function closeModal() {
  document.getElementById('modal-overlay').style.display = 'none';
    selectedSize  = null;
    selectedColor = null;
}

// ── Debounce search ──
let _searchTimer;
function debounceLoad() {
  clearTimeout(_searchTimer);
  _searchTimer = setTimeout(loadProducts, 320);
}

function escHtml(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadCategories();
loadProducts();
</script>

<?php include '../includes/footer.php'; 