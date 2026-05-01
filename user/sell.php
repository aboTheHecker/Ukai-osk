<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireUser();
$page_title = 'Sell an Item';
include '../includes/header.php';
?>

<div class="page-wrap" style="max-width:720px;">
  <h1 class="page-title">Sell an Item</h1>
  <p style="color:var(--muted);font-size:13px;margin-top:-16px;margin-bottom:28px;">
    List your pre-loved clothing. Once submitted, it'll appear in the shop for other buyers to purchase.
  </p>

  <form id="sell-form" onsubmit="submitListing(event)" style="display:flex;flex-direction:column;gap:20px;">

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
            <select class="field-input" name="condition_label" required>
              <option value="Excellent">Excellent — Like new, no defects</option>
              <option value="Good" selected>Good — Minor signs of use</option>
              <option value="Fair">Fair — Visible wear but usable</option>
              <option value="Poor">Poor — Heavily used, has flaws</option>
            </select>
          </div>
        </div>

        <div>
          <label class="field-label">Description</label>
          <textarea class="field-input" name="description" rows="3"
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
      <p style="font-size:12px;color:var(--muted);margin-bottom:12px;">Add at least one size. You can add multiple.</p>

      <div id="sizes-list" style="display:flex;flex-direction:column;gap:8px;"></div>
      <button type="button" onclick="addSize()" class="btn btn-secondary" style="margin-top:10px;font-size:12px;padding:7px 14px;">
        + Add Size
      </button>
    </div>

    <!-- Colors -->
    <div class="form-section">
      <div class="section-title">🎨 Colors Available <span class="req">*</span></div>
      <p style="font-size:12px;color:var(--muted);margin-bottom:12px;">Add at least one color.</p>

      <div id="colors-list" style="display:flex;flex-direction:column;gap:8px;"></div>
      <button type="button" onclick="addColor()" class="btn btn-secondary" style="margin-top:10px;font-size:12px;padding:7px 14px;">
        + Add Color
      </button>
    </div>

    <!-- Variants / Stock -->
    <div class="form-section" id="variants-section" style="display:none;">
      <div class="section-title">📦 Stock per Variant <span class="req">*</span></div>
      <p style="font-size:12px;color:var(--muted);margin-bottom:12px;">Set how many pieces you have for each size + color combo.</p>
      <div id="variants-grid" style="display:flex;flex-direction:column;gap:8px;"></div>
    </div>

    <!-- Submit -->
    <div style="display:flex;gap:12px;padding-top:8px;">
      <button type="submit" id="btn-submit" class="btn btn-primary" style="flex:1;justify-content:center;padding:13px;font-size:14px;">
        List Item for Sale
      </button>
      <a href="products.php" class="btn btn-secondary" style="padding:13px 20px;">Cancel</a>
    </div>
  </form>
</div>

<!-- Success Modal -->
<div id="success-modal" style="
  display:none;position:fixed;inset:0;background:rgba(26,20,16,.5);
  z-index:300;align-items:center;justify-content:center;">
  <div style="background:var(--white);border-radius:12px;padding:48px 40px;text-align:center;max-width:380px;width:90%;box-shadow:0 24px 64px rgba(0,0,0,.2);">
    <div style="font-size:52px;margin-bottom:16px;">🏷️</div>
    <h2 style="font-family:'Syne',sans-serif;font-weight:700;font-size:22px;margin-bottom:8px;">Item Listed!</h2>
    <p style="color:var(--muted);font-size:14px;margin-bottom:24px;">
      Your item is now live in the shop. Good luck with your sale!
    </p>
    <a href="my_listings.php" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:10px;">View My Listings</a>
    <a href="sell.php" class="btn btn-secondary" style="width:100%;justify-content:center;">List Another Item</a>
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
</style>

<script>
let sizes  = [];
let colors = [];
let sizeId = 0, colorId = 0;

// ── Price preview ──
function updatePricePreview(val) {
  const el = document.getElementById('price-preview');
  const n  = parseFloat(val);
  el.textContent = (!isNaN(n) && n > 0)
    ? '₱' + n.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2})
    : '';
}

// ── Image preview ──
function previewImage(url) {
  const wrap = document.getElementById('img-preview');
  const tag  = document.getElementById('img-preview-tag');
  if (!url) { wrap.style.display='none'; return; }
  tag.src = url;
  tag.onload  = () => wrap.style.display = 'block';
  tag.onerror = () => wrap.style.display = 'none';
}
function clearImage() {
  document.getElementById('f-image').value = '';
  document.getElementById('img-preview').style.display = 'none';
}

// ── Sizes ──
function addSize(label='') {
  const id = ++sizeId;
  sizes.push({ tempId: id, label });
  renderSizes();
}
function removeSize(id) {
  sizes = sizes.filter(s => s.tempId !== id);
  renderSizes();
  rebuildVariants();
}
function renderSizes() {
  document.getElementById('sizes-list').innerHTML = sizes.map(s => `
    <div class="size-row">
      <input type="text" placeholder="e.g. XS, S, M, L, XL, Free Size" value="${esc(s.label)}"
        maxlength="20" oninput="updateSizeLabel(${s.tempId}, this.value)"
        style="flex:1;">
      <button type="button" class="remove-btn" onclick="removeSize(${s.tempId})">✕</button>
    </div>`).join('');
  rebuildVariants();
}
function updateSizeLabel(id, val) {
  const s = sizes.find(s => s.tempId === id);
  if (s) { s.label = val; rebuildVariants(); }
}

// ── Colors ──
function addColor(name='', hex='#888888') {
  const id = ++colorId;
  colors.push({ tempId: id, name, hex });
  renderColors();
}
function removeColor(id) {
  colors = colors.filter(c => c.tempId !== id);
  renderColors();
  rebuildVariants();
}
function renderColors() {
  document.getElementById('colors-list').innerHTML = colors.map(c => `
    <div class="color-row">
      <input type="color" value="${esc(c.hex)}"
        oninput="updateColorHex(${c.tempId}, this.value)"
        style="width:40px;height:36px;padding:2px;border-radius:4px;border:1.5px solid var(--border);cursor:pointer;background:var(--bg);">
      <input type="text" placeholder="Color name (e.g. Black, Baby Blue)" value="${esc(c.name)}"
        maxlength="50" oninput="updateColorName(${c.tempId}, this.value)"
        style="flex:1;">
      <button type="button" class="remove-btn" onclick="removeColor(${c.tempId})">✕</button>
    </div>`).join('');
  rebuildVariants();
}
function updateColorHex(id, val) {
  const c = colors.find(c => c.tempId === id);
  if (c) { c.hex = val; }
}
function updateColorName(id, val) {
  const c = colors.find(c => c.tempId === id);
  if (c) { c.name = val; rebuildVariants(); }
}

// ── Variants grid ──
let variantStocks = {};
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
      const key = s.tempId + '-' + c.tempId;
      const stock = variantStocks[key] ?? 1;
      return `
      <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;
        background:var(--bg);border-radius:6px;border:1px solid var(--border);">
        <div style="display:flex;align-items:center;gap:8px;flex:1;">
          <span style="background:var(--tag-bg);padding:3px 10px;border-radius:4px;font-size:12px;font-weight:600;">${esc(s.label)}</span>
          <span style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--muted);">
            <span style="width:12px;height:12px;border-radius:50%;background:${esc(c.hex)};display:inline-block;border:1px solid var(--border);"></span>
            ${esc(c.name)}
          </span>
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

// ── Submit ──
async function submitListing(e) {
  e.preventDefault();

  const validSizes  = sizes.filter(s => s.label.trim());
  const validColors = colors.filter(c => c.name.trim());

  if (!validSizes.length)  return showToast('Add at least one size.', 'err');
  if (!validColors.length) return showToast('Add at least one color.', 'err');

  // Build variants array
  const variants = [];
  validSizes.forEach(s => {
    validColors.forEach(c => {
      variants.push({
        size_temp_id:  s.tempId,
        color_temp_id: c.tempId,
        stock: variantStocks[s.tempId + '-' + c.tempId] ?? 1
      });
    });
  });

  const sizesPayload  = validSizes.map(s => ({ temp_id: s.tempId, label: s.label.trim() }));
  const colorsPayload = validColors.map(c => ({ temp_id: c.tempId, name: c.name.trim(), hex: c.hex }));

  const btn = document.getElementById('btn-submit');
  btn.disabled = true;
  btn.textContent = 'Listing...';

  const fd = new FormData(document.getElementById('sell-form'));
  fd.append('action',   'add');
  fd.append('sizes',    JSON.stringify(sizesPayload));
  fd.append('colors',   JSON.stringify(colorsPayload));
  fd.append('variants', JSON.stringify(variants));
  // seller_id so admin/listings can track who listed it
  fd.append('seller_id', '<?= $_SESSION['user_id'] ?>');

  const res  = await fetch('../api/products.php', { method:'POST', body:fd });
  const data = await res.json();

  if (data.success) {
    document.getElementById('success-modal').style.display = 'flex';
  } else {
    showToast(data.message || 'Failed to list item.', 'err');
    btn.disabled = false;
    btn.textContent = 'List Item for Sale';
  }
}

function esc(s) {
  if (!s && s !== 0) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Add one size and color row by default
addSize('Free Size');
addColor('Assorted', '#888888');
</script>

<?php include '../includes/footer.php'; ?>