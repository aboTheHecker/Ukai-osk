<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireUser();

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$product_id) {
    header('Location: my_listings.php');
    exit;
}

$page_title = 'Edit Listing';
include '../includes/header.php';
?>

<div class="page-wrap" style="max-width:720px;">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px;">
    <a href="my_listings.php" style="color:var(--muted);text-decoration:none;font-size:13px;">← My Listings</a>
  </div>
  <h1 class="page-title">Edit Listing</h1>
  <p style="color:var(--muted);font-size:13px;margin-top:-16px;margin-bottom:28px;">
    Update your item details. Changes will be reflected in the shop immediately.
  </p>

  <!-- Loading state -->
  <div id="edit-loading" style="text-align:center;padding:60px;color:var(--muted);">
    <div class="spin" style="margin:0 auto 12px;"></div>Loading your listing...
  </div>

  <!-- Error state -->
  <div id="edit-error" style="display:none;" class="empty-state">
    <div class="icon">⚠️</div>
    <h3>Listing not found</h3>
    <p>This item may have been deleted or doesn't belong to you.</p>
    <a href="my_listings.php" class="btn btn-primary">Back to My Listings</a>
  </div>

  <form id="edit-form" onsubmit="submitEdit(event)" style="display:none;flex-direction:column;gap:20px;">

    <!-- Basic Info -->
    <div class="form-section">
      <div class="section-title">📝 Item Details</div>
      <div style="display:flex;flex-direction:column;gap:14px;">

        <div>
          <label class="field-label">Item Name <span class="req">*</span></label>
          <input class="field-input" type="text" name="name" id="f-name"
            placeholder="e.g. Vintage Polo Shirt, Floral Dress" required maxlength="200">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div>
            <label class="field-label">Category <span class="req">*</span></label>
            <select class="field-input" name="category" id="f-category" required>
              <option value="">Select category...</option>
              <option>Tops</option>
              <option>Bottoms</option>
              <option>Dresses</option>
              <option>Outerwear</option>
              <option>Footwear</option>
              <option>Bags</option>
              <option>Accessories</option>
              <option>Sets</option>
              <option>Others</option>
            </select>
          </div>
          <div>
            <label class="field-label">Condition <span class="req">*</span></label>
            <select class="field-input" name="condition_label" id="f-condition" required>
              <option value="Excellent">Excellent — Like new, no defects</option>
              <option value="Good">Good — Minor signs of use</option>
              <option value="Fair">Fair — Visible wear but usable</option>
              <option value="Poor">Poor — Heavily used, has flaws</option>
            </select>
          </div>
        </div>

        <div>
          <label class="field-label">Description</label>
          <textarea class="field-input" name="description" id="f-description" rows="3"
            placeholder="Describe the item — brand, material, measurements, any defects..."
            style="resize:vertical;"></textarea>
        </div>

        <div>
          <label class="field-label">Price (₱) <span class="req">*</span></label>
          <div style="position:relative;">
            <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-weight:600;color:var(--muted);font-size:14px;">₱</span>
            <input class="field-input" type="number" name="base_price" id="f-price"
              placeholder="0.00" min="1" step="0.01" required
              style="padding-left:28px;"
              oninput="updatePricePreview(this.value)">
          </div>
          <div id="price-preview" style="font-size:12px;color:var(--muted);margin-top:4px;"></div>
        </div>

        <div>
          <label class="field-label">Image URL</label>
          <input class="field-input" type="url" name="image_url" id="f-image"
            placeholder="https://... (paste a direct image link)"
            oninput="previewImage(this.value)">
          <div id="img-preview" style="margin-top:10px;display:none;">
            <img id="img-preview-tag" src="" alt="preview"
              style="width:100px;height:120px;object-fit:cover;border-radius:6px;border:1.5px solid var(--border);">
            <button type="button" onclick="clearImage()" style="
              display:block;margin-top:6px;background:none;border:none;
              font-size:12px;color:var(--muted);cursor:pointer;text-decoration:underline;">Remove image</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Sizes -->
    <div class="form-section">
      <div class="section-title">📐 Sizes Available <span class="req">*</span></div>
      <p style="font-size:12px;color:var(--muted);margin-bottom:12px;">
        Add at least one size. Removing a size will zero out its stock.
      </p>
      <div id="sizes-list" style="display:flex;flex-direction:column;gap:8px;"></div>
      <button type="button" onclick="addSize()" class="btn btn-secondary" style="margin-top:10px;font-size:12px;padding:7px 14px;">
        + Add Size
      </button>
    </div>

    <!-- Colors -->
    <div class="form-section">
      <div class="section-title">🎨 Colors Available <span class="req">*</span></div>
      <p style="font-size:12px;color:var(--muted);margin-bottom:12px;">
        Add at least one color. Removing a color will zero out its stock.
      </p>
      <div id="colors-list" style="display:flex;flex-direction:column;gap:8px;"></div>
      <button type="button" onclick="addColor()" class="btn btn-secondary" style="margin-top:10px;font-size:12px;padding:7px 14px;">
        + Add Color
      </button>
    </div>

    <!-- Variants / Stock -->
    <div class="form-section" id="variants-section" style="display:none;">
      <div class="section-title">📦 Stock per Variant <span class="req">*</span></div>
      <p style="font-size:12px;color:var(--muted);margin-bottom:12px;">
        Update how many pieces you have for each size + color combination.
      </p>
      <div id="variants-grid" style="display:flex;flex-direction:column;gap:8px;"></div>
    </div>

    <!-- Actions -->
    <div style="display:flex;gap:12px;padding-top:8px;">
      <button type="submit" id="btn-submit" class="btn btn-primary" style="flex:1;justify-content:center;padding:13px;font-size:14px;">
        Save Changes
      </button>
      <a href="my_listings.php" class="btn btn-secondary" style="padding:13px 20px;">Cancel</a>
    </div>
  </form>
</div>

<!-- Success Modal -->
<div id="success-modal" style="
  display:none;position:fixed;inset:0;background:rgba(26,20,16,.5);
  z-index:300;align-items:center;justify-content:center;">
  <div style="background:var(--white);border-radius:12px;padding:48px 40px;text-align:center;max-width:380px;width:90%;box-shadow:0 24px 64px rgba(0,0,0,.2);">
    <div style="font-size:52px;margin-bottom:16px;">✅</div>
    <h2 style="font-family:'Syne',sans-serif;font-weight:700;font-size:22px;margin-bottom:8px;">Listing Updated!</h2>
    <p style="color:var(--muted);font-size:14px;margin-bottom:24px;">
      Your changes are now live in the shop.
    </p>
    <a href="my_listings.php" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:10px;">View My Listings</a>
    <a href="products.php" class="btn btn-secondary" style="width:100%;justify-content:center;">Browse Shop</a>
  </div>
</div>

<style>
.form-section {
  background:var(--white);
  border:1.5px solid var(--border);
  border-radius:8px;
  padding:24px;
}
.section-title {
  font-family:'Syne',sans-serif;
  font-weight:700;
  font-size:15px;
  margin-bottom:16px;
}
.req { color:var(--accent); }
.field-label {
  display:block;
  font-size:11px;font-weight:600;
  text-transform:uppercase;letter-spacing:1px;
  color:#5C3D2E;margin-bottom:5px;
}
.field-input {
  width:100%;padding:10px 13px;
  border:1.5px solid var(--border);border-radius:var(--radius);
  background:var(--bg);font-family:'Manrope',sans-serif;
  font-size:13px;color:var(--text);outline:none;transition:border-color .15s;
}
.field-input:focus { border-color:var(--accent); background:var(--white); }
.field-input::placeholder { color:var(--muted); }

.size-row, .color-row {
  display:flex;align-items:center;gap:8px;
}
.size-row input, .color-row input {
  padding:9px 12px;border:1.5px solid var(--border);border-radius:var(--radius);
  background:var(--bg);font-family:'Manrope',sans-serif;font-size:13px;
  color:var(--text);outline:none;transition:border-color .15s;
}
.size-row input:focus, .color-row input:focus { border-color:var(--accent); }
.remove-btn {
  width:30px;height:30px;flex-shrink:0;border:1.5px solid var(--border);
  border-radius:4px;background:transparent;cursor:pointer;color:var(--muted);
  font-size:14px;transition:.15s;display:flex;align-items:center;justify-content:center;
}
.remove-btn:hover { border-color:#B83232;color:#B83232; }

/* Highlight existing vs new rows */
.row-existing { border-left:3px solid var(--accent2) !important; padding-left:9px; }
.row-new      { border-left:3px solid var(--accent)  !important; padding-left:9px; }
</style>

<script>
const PRODUCT_ID = <?= $product_id ?>;

let sizes      = [];   // { tempId, dbId|null, label }
let colors     = [];   // { tempId, dbId|null, name, hex }
let sizeId     = 0;
let colorId    = 0;
let variantStocks = {}; // key: tempSzId-tempClId → stock
let dbVariants    = {}; // key: dbSzId-dbClId    → variant db id (for update)

// ─── Bootstrap: load existing product ───────────────────────────────────────
async function loadProduct() {
  const res  = await fetch(`../api/products.php?action=detail&id=${PRODUCT_ID}`);
  const data = await res.json();

  document.getElementById('edit-loading').style.display = 'none';

  if (!data.success || !data.product) {
    document.getElementById('edit-error').style.display = 'block';
    return;
  }

  const p = data.product;

  // Verify ownership client-side hint (server must also enforce)
  // Populate basic fields
  document.getElementById('f-name').value        = p.name        || '';
  document.getElementById('f-description').value = p.description || '';
  document.getElementById('f-price').value       = p.base_price  || '';
  updatePricePreview(p.base_price);

  // Category
  const catSel = document.getElementById('f-category');
  for (let opt of catSel.options) {
    if (opt.value === p.category) { opt.selected = true; break; }
  }

  // Condition
  const conSel = document.getElementById('f-condition');
  for (let opt of conSel.options) {
    if (opt.value === p.condition_label) { opt.selected = true; break; }
  }

  // Image
  if (p.image_url) {
    document.getElementById('f-image').value = p.image_url;
    previewImage(p.image_url);
  }

  // Sizes — p.sizes: [{id, size_label}, ...]
  (p.sizes || []).forEach(s => {
    const tid = ++sizeId;
    sizes.push({ tempId: tid, dbId: s.id, label: s.size_label });
  });

  // Colors — p.colors: [{id, color_name, color_hex}, ...]
  (p.colors || []).forEach(c => {
    const tid = ++colorId;
    colors.push({ tempId: tid, dbId: c.id, name: c.color_name, hex: c.color_hex });
  });

  // Existing variant stocks — p.variant_map: { "szId-clId": {id, stock}, ... }
  // Map db ids back to temp ids
  const szDbToTemp = {};
  sizes.forEach(s  => { if (s.dbId) szDbToTemp[s.dbId] = s.tempId; });
  const clDbToTemp = {};
  colors.forEach(c => { if (c.dbId) clDbToTemp[c.dbId] = c.tempId; });

  if (p.variant_map) {
    Object.entries(p.variant_map).forEach(([key, v]) => {
      const [dbSzId, dbClId] = key.split('-').map(Number);
      const tSz = szDbToTemp[dbSzId];
      const tCl = clDbToTemp[dbClId];
      if (tSz && tCl) {
        variantStocks[tSz + '-' + tCl] = v.stock;
        dbVariants[tSz + '-' + tCl]    = v.id;
      }
    });
  }

  renderSizes();
  renderColors();

  const form = document.getElementById('edit-form');
  form.style.display = 'flex';
}

// ─── Price preview ────────────────────────────────────────────────────────────
function updatePricePreview(val) {
  const el = document.getElementById('price-preview');
  const n  = parseFloat(val);
  el.textContent = (!isNaN(n) && n > 0)
    ? '₱' + n.toLocaleString('en-PH', { minimumFractionDigits:2, maximumFractionDigits:2 })
    : '';
}

// ─── Image preview ────────────────────────────────────────────────────────────
function previewImage(url) {
  const wrap = document.getElementById('img-preview');
  const tag  = document.getElementById('img-preview-tag');
  if (!url) { wrap.style.display = 'none'; return; }
  tag.src     = url;
  tag.onload  = () => wrap.style.display = 'block';
  tag.onerror = () => wrap.style.display = 'none';
}
function clearImage() {
  document.getElementById('f-image').value = '';
  document.getElementById('img-preview').style.display = 'none';
}

// ─── Sizes ────────────────────────────────────────────────────────────────────
function addSize(label = '') {
  const id = ++sizeId;
  sizes.push({ tempId: id, dbId: null, label });
  renderSizes();
}
function removeSize(id) {
  sizes = sizes.filter(s => s.tempId !== id);
  renderSizes();
  rebuildVariants();
}
function renderSizes() {
  document.getElementById('sizes-list').innerHTML = sizes.map(s => `
    <div class="size-row ${s.dbId ? 'row-existing' : 'row-new'}">
      <input type="text" placeholder="e.g. XS, S, M, L, XL, Free Size" value="${esc(s.label)}"
        maxlength="20" oninput="updateSizeLabel(${s.tempId}, this.value)"
        style="flex:1;" title="${s.dbId ? 'Existing size' : 'New size'}">
      ${s.dbId
        ? `<span style="font-size:10px;color:var(--accent2);white-space:nowrap;font-weight:600;">existing</span>`
        : `<span style="font-size:10px;color:var(--accent);white-space:nowrap;font-weight:600;">new</span>`
      }
      <button type="button" class="remove-btn" onclick="removeSize(${s.tempId})" title="Remove size">✕</button>
    </div>`).join('');
  rebuildVariants();
}
function updateSizeLabel(id, val) {
  const s = sizes.find(s => s.tempId === id);
  if (s) { s.label = val; rebuildVariants(); }
}

// ─── Colors ───────────────────────────────────────────────────────────────────
function addColor(name = '', hex = '#888888') {
  const id = ++colorId;
  colors.push({ tempId: id, dbId: null, name, hex });
  renderColors();
}
function removeColor(id) {
  colors = colors.filter(c => c.tempId !== id);
  renderColors();
  rebuildVariants();
}
function renderColors() {
  document.getElementById('colors-list').innerHTML = colors.map(c => `
    <div class="color-row ${c.dbId ? 'row-existing' : 'row-new'}">
      <input type="color" value="${esc(c.hex)}"
        oninput="updateColorHex(${c.tempId}, this.value)"
        style="width:40px;height:36px;padding:2px;border-radius:4px;border:1.5px solid var(--border);cursor:pointer;background:var(--bg);"
        title="${c.dbId ? 'Existing color' : 'New color'}">
      <input type="text" placeholder="Color name (e.g. Black, Baby Blue)" value="${esc(c.name)}"
        maxlength="50" oninput="updateColorName(${c.tempId}, this.value)"
        style="flex:1;">
      ${c.dbId
        ? `<span style="font-size:10px;color:var(--accent2);white-space:nowrap;font-weight:600;">existing</span>`
        : `<span style="font-size:10px;color:var(--accent);white-space:nowrap;font-weight:600;">new</span>`
      }
      <button type="button" class="remove-btn" onclick="removeColor(${c.tempId})" title="Remove color">✕</button>
    </div>`).join('');
  rebuildVariants();
}
function updateColorHex(id, val) {
  const c = colors.find(c => c.tempId === id);
  if (c) c.hex = val;
}
function updateColorName(id, val) {
  const c = colors.find(c => c.tempId === id);
  if (c) { c.name = val; rebuildVariants(); }
}

// ─── Variants grid ────────────────────────────────────────────────────────────
function rebuildVariants() {
  const section = document.getElementById('variants-section');
  const grid    = document.getElementById('variants-grid');

  const validSizes  = sizes.filter(s => s.label.trim());
  const validColors = colors.filter(c => c.name.trim());

  if (!validSizes.length || !validColors.length) {
    section.style.display = 'none';
    return;
  }
  section.style.display = 'block';

  grid.innerHTML = validSizes.map(s =>
    validColors.map(c => {
      const key   = s.tempId + '-' + c.tempId;
      const stock = variantStocks[key] ?? 0;
      const isExisting = s.dbId && c.dbId && dbVariants[key] !== undefined;
      return `
      <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;
        background:var(--bg);border-radius:6px;border:1px solid var(--border);
        border-left:3px solid ${isExisting ? 'var(--accent2)' : 'var(--accent)'};">
        <div style="display:flex;align-items:center;gap:8px;flex:1;">
          <span style="background:var(--tag-bg);padding:3px 10px;border-radius:4px;font-size:12px;font-weight:600;">${esc(s.label)}</span>
          <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--muted);">
            <span style="width:12px;height:12px;border-radius:50%;background:${esc(c.hex)};display:inline-block;border:1px solid var(--border);"></span>
            ${esc(c.name)}
          </span>
          ${isExisting
            ? `<span style="font-size:10px;color:var(--accent2);font-weight:600;">existing</span>`
            : `<span style="font-size:10px;color:var(--accent);font-weight:600;">new</span>`
          }
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          <label style="font-size:11px;color:var(--muted);white-space:nowrap;">Stock:</label>
          <input type="number" min="0" value="${stock}"
            oninput="variantStocks['${key}']=parseInt(this.value)||0"
            style="width:70px;padding:6px 10px;border:1.5px solid var(--border);
              border-radius:4px;background:var(--white);font-family:'Manrope',sans-serif;
              font-size:13px;font-weight:600;text-align:center;outline:none;"
            onfocus="this.style.borderColor='var(--accent)'"
            onblur="this.style.borderColor='var(--border)'">
          <span style="font-size:12px;color:var(--muted);">pcs</span>
        </div>
      </div>`;
    }).join('')
  ).join('');
}

// ─── Submit ───────────────────────────────────────────────────────────────────
async function submitEdit(e) {
  e.preventDefault();

  const validSizes  = sizes.filter(s => s.label.trim());
  const validColors = colors.filter(c => c.name.trim());

  if (!validSizes.length)  return showToast('Add at least one size.', 'err');
  if (!validColors.length) return showToast('Add at least one color.', 'err');

  // Build sizes payload — include dbId so the API can upsert
  const sizesPayload = validSizes.map(s => ({
    temp_id: s.tempId,
    db_id:   s.dbId || null,
    label:   s.label.trim()
  }));

  // Build colors payload
  const colorsPayload = validColors.map(c => ({
    temp_id: c.tempId,
    db_id:   c.dbId || null,
    name:    c.name.trim(),
    hex:     c.hex
  }));

  // Build variants payload
  const variants = [];
  validSizes.forEach(s => {
    validColors.forEach(c => {
      const key = s.tempId + '-' + c.tempId;
      variants.push({
        size_temp_id:  s.tempId,
        color_temp_id: c.tempId,
        db_variant_id: dbVariants[key] || null,
        stock:         variantStocks[key] ?? 0
      });
    });
  });

  const btn = document.getElementById('btn-submit');
  btn.disabled    = true;
  btn.textContent = 'Saving...';

  const fd = new FormData(document.getElementById('edit-form'));
  fd.append('action',     'edit');
  fd.append('id',         PRODUCT_ID);
  fd.append('sizes',      JSON.stringify(sizesPayload));
  fd.append('colors',     JSON.stringify(colorsPayload));
  fd.append('variants',   JSON.stringify(variants));

  const res  = await fetch('../api/products.php', { method:'POST', body:fd });
  const data = await res.json();

  if (data.success) {
    document.getElementById('success-modal').style.display = 'flex';
  } else {
    showToast(data.message || 'Failed to save changes.', 'err');
    btn.disabled    = false;
    btn.textContent = 'Save Changes';
  }
}

function esc(s) {
  if (!s && s !== 0) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Boot
loadProduct();
</script>

<?php include '../includes/footer.php'; ?>