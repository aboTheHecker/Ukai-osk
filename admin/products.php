<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireAdmin();
$page_title = 'Products';

$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset   = ($page - 1) * $per_page;

// Categories
$cats = $conn->query("SELECT DISTINCT category FROM products ORDER BY category")->fetch_all(MYSQLI_ASSOC);
$cat_list = array_column($cats, 'category');

// Build query
$where = []; $params = []; $types = '';
if ($search) {
  $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
  $like = "%$search%"; $params[] = $like; $params[] = $like; $types .= 'ss';
}
if ($category) {
  $where[] = "p.category = ?"; $params[] = $category; $types .= 's';
}

$base_sql = "FROM products p LEFT JOIN product_variants pv ON pv.product_id = p.id LEFT JOIN users u ON u.id = p.seller_id";
if ($where) $base_sql .= " WHERE " . implode(' AND ', $where);

$count_stmt = $conn->prepare("SELECT COUNT(DISTINCT p.id) AS n " . $base_sql);
if ($params) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_rows  = (int)$count_stmt->get_result()->fetch_assoc()['n'];
$total_pages = max(1, ceil($total_rows / $per_page));

$data_sql = "SELECT p.id, p.name, p.base_price, p.image_url, p.category, p.condition_label,
                    p.created_at, u.name AS seller_name,
                    COALESCE(SUM(pv.stock),0) AS total_stock
             " . $base_sql . " GROUP BY p.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$limit_params = array_merge($params, [$per_page, $offset]);
$limit_types  = $types . 'ii';
$stmt = $conn->prepare($data_sql);
$stmt->bind_param($limit_types, ...$limit_params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>

<div class="topbar">
  <div>
    <div class="topbar-title">Products</div>
    <div class="topbar-breadcrumb"><?= $total_rows ?> products listed</div>
  </div>
  <div class="topbar-actions">
    <button class="btn btn-primary" onclick="openAddModal()">＋ Add Product</button>
  </div>
</div>

<div class="content">
  <div class="card">

    <!-- Filters -->
    <form method="get" style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
      <div class="search-bar">
        <span class="icon">🔍</span>
        <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <select name="category" class="field-input" style="width:auto;padding:8px 12px;"
              onchange="this.form.submit()">
        <option value="">All Categories</option>
        <?php foreach ($cat_list as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>" <?= $c===$category?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
      <?php if ($search || $category): ?>
      <a href="products.php" class="btn btn-secondary btn-sm">✕ Clear</a>
      <?php endif; ?>
    </form>

    <?php if ($products): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Condition</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Seller</th>
            <th>Listed</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
          <?php
            $stock = (int)$p['total_stock'];
            $stock_class = $stock === 0 ? 'badge-out' : ($stock <= 3 ? 'badge-low' : 'badge-ok');
            $stock_label = $stock === 0 ? 'Out of stock' : $stock . ' in stock';
            $cond_colors = ['Excellent'=>'#3A5A40','Good'=>'#7A6E63','Fair'=>'#B87A2A','Poor'=>'#B83232'];
            $cond_color  = $cond_colors[$p['condition_label']] ?? '#7A6E63';
          ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:44px;border-radius:4px;overflow:hidden;background:var(--surface);flex-shrink:0;">
                  <?php if ($p['image_url']): ?>
                  <img src="<?= htmlspecialchars($p['image_url']) ?>" style="width:100%;height:100%;object-fit:cover;"
                       onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;font-size:16px;\'>👗</div>'">
                  <?php else: ?>
                  <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:16px;">👗</div>
                  <?php endif; ?>
                </div>
                <div>
                  <div style="font-weight:600;font-size:13px;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($p['name']) ?></div>
                  <div style="font-size:11px;color:var(--muted);">#<?= $p['id'] ?></div>
                </div>
              </div>
            </td>
            <td style="color:var(--muted);font-size:12px;"><?= htmlspecialchars($p['category']) ?></td>
            <td><span style="font-size:11px;font-weight:700;color:<?= $cond_color ?>;"><?= htmlspecialchars($p['condition_label']) ?></span></td>
            <td style="font-weight:700;color:var(--accent);">₱<?= number_format($p['base_price'], 2) ?></td>
            <td><span class="badge <?= $stock_class ?>"><?= $stock_label ?></span></td>
            <td style="font-size:12px;color:var(--muted);"><?= htmlspecialchars($p['seller_name'] ?? '—') ?></td>
            <td style="font-size:11px;color:var(--muted);"><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
            <td>
              <div style="display:flex;gap:6px;">
                <button class="btn btn-secondary btn-sm" onclick="openEditModal(<?= $p['id'] ?>)">✏️ Edit</button>
                <button class="btn btn-sm" style="background:#FEE2E2;color:#991B1B;border:1.5px solid #FECACA;"
                        onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>')">🗑</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($total_pages > 1): ?>
    <div style="display:flex;justify-content:center;gap:6px;margin-top:20px;flex-wrap:wrap;">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>"
         style="padding:6px 12px;border-radius:4px;font-size:12px;font-weight:600;text-decoration:none;
                background:<?= $i===$page?'var(--accent)':'var(--surface)' ?>;
                color:<?= $i===$page?'var(--white)':'var(--muted)' ?>;
                border:1.5px solid <?= $i===$page?'var(--accent)':'var(--border)' ?>;"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="empty-state">
      <div class="icon">👕</div>
      <h3>No products found</h3>
      <p><?= $search ? 'Try a different search.' : 'Add your first product to get started.' ?></p>
      <button class="btn btn-primary" onclick="openAddModal()" style="margin-top:12px;">＋ Add Product</button>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── ADD / EDIT MODAL ── -->
<div class="modal-overlay" id="product-modal">
  <div class="modal-box" style="max-width:680px;" onclick="event.stopPropagation()">
    <div class="modal-header">
      <div class="modal-title" id="modal-title">Add Product</div>
      <button class="modal-close" onclick="closeProductModal()">✕</button>
    </div>
    <div class="modal-body" id="modal-body">
      <!-- Rendered by JS -->
    </div>
  </div>
</div>

<!-- ── DELETE CONFIRM ── -->
<div class="modal-overlay" id="del-modal">
  <div class="modal-box" style="max-width:380px;text-align:center;" onclick="event.stopPropagation()">
    <div style="padding:36px;">
      <div style="font-size:44px;margin-bottom:16px;">🗑️</div>
      <h3 style="font-family:'Syne',sans-serif;font-weight:700;margin-bottom:8px;">Delete Product?</h3>
      <p style="font-size:13px;color:var(--muted);margin-bottom:24px;" id="del-msg">This will permanently remove the product.</p>
      <div style="display:flex;gap:10px;justify-content:center;">
        <button class="btn btn-secondary" onclick="document.getElementById('del-modal').classList.remove('open')">Cancel</button>
        <button class="btn btn-danger" id="btn-confirm-del">Delete</button>
      </div>
    </div>
  </div>
</div>

<style>
.size-row, .color-row {
  display:flex;align-items:center;gap:8px;margin-bottom:6px;
}
.size-row input, .color-row input {
  padding:8px 11px;border:1.5px solid var(--border);border-radius:var(--radius);
  background:var(--bg);font-family:'Manrope',sans-serif;font-size:13px;
  color:var(--text);outline:none;transition:border-color .15s;
}
.size-row input:focus, .color-row input:focus { border-color:var(--accent); }
.remove-btn {
  width:28px;height:28px;border:1.5px solid var(--border);border-radius:4px;
  background:transparent;cursor:pointer;color:var(--muted);font-size:12px;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.remove-btn:hover { border-color:#B83232;color:#B83232; }
</style>

<script>
let editingId = null;
let sizes = [], colors = [], sizeId = 0, colorId = 0, variantStocks = {};

function openAddModal() {
  editingId = null;
  sizes = []; colors = []; sizeId = 0; colorId = 0; variantStocks = {};
  document.getElementById('modal-title').textContent = 'Add Product';
  renderForm(null);
  document.getElementById('product-modal').classList.add('open');
  addSize('Free Size'); addColor('Assorted', '#888888');
}

async function openEditModal(id) {
  editingId = id;
  sizes = []; colors = []; sizeId = 0; colorId = 0; variantStocks = {};
  document.getElementById('modal-title').textContent = 'Edit Product';
  document.getElementById('modal-body').innerHTML = '<div style="text-align:center;padding:40px;color:var(--muted);"><div class="spin" style="margin:0 auto 12px;"></div>Loading...</div>';
  document.getElementById('product-modal').classList.add('open');

  const res  = await fetch(`../api/products.php?action=product_detail&id=${id}`);
  const data = await res.json();
  if (!data.success) { closeProductModal(); showToast('Failed to load product.', 'err'); return; }

  const p = data.product;
  (p.sizes||[]).forEach(s => { const tid=++sizeId; sizes.push({tempId:tid,dbId:s.id,label:s.size_label}); });
  (p.colors||[]).forEach(c => { const tid=++colorId; colors.push({tempId:tid,dbId:c.id,name:c.color_name,hex:c.color_hex}); });

  // Map variant stocks
  const szDbToTemp = {}; sizes.forEach(s => { if(s.dbId) szDbToTemp[s.dbId]=s.tempId; });
  const clDbToTemp = {}; colors.forEach(c => { if(c.dbId) clDbToTemp[c.dbId]=c.tempId; });
  (p.variants||[]).forEach(v => {
    const tSz=szDbToTemp[v.size_id], tCl=clDbToTemp[v.color_id];
    if(tSz && tCl) { variantStocks[tSz+'-'+tCl]=v.stock; }
  });

  renderForm(p);
}

function renderForm(p) {
  document.getElementById('modal-body').innerHTML = `
    <div style="display:flex;flex-direction:column;gap:18px;">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div style="grid-column:1/-1;">
          <label class="field-label">Item Name *</label>
          <input class="field-input" type="text" id="pf-name" value="${esc(p?.name||'')}" placeholder="e.g. Vintage Polo Shirt" maxlength="200">
        </div>
        <div>
          <label class="field-label">Category *</label>
          <select class="field-input" id="pf-category">
            ${['Tops','Bottoms','Dresses','Outerwear','Footwear','Bags','Accessories','Sets','Others'].map(c=>`<option value="${c}" ${p?.category===c?'selected':''}>${c}</option>`).join('')}
          </select>
        </div>
        <div>
          <label class="field-label">Condition *</label>
          <select class="field-input" id="pf-condition">
            ${['Excellent','Good','Fair','Poor'].map(c=>`<option value="${c}" ${p?.condition_label===c?'selected':''}>${c}</option>`).join('')}
          </select>
        </div>
        <div style="grid-column:1/-1;">
          <label class="field-label">Description</label>
          <textarea class="field-input" id="pf-description" rows="2" style="resize:vertical;" placeholder="Brand, material, measurements, defects...">${esc(p?.description||'')}</textarea>
        </div>
        <div>
          <label class="field-label">Price (₱) *</label>
          <input class="field-input" type="number" id="pf-price" value="${p?.base_price||''}" placeholder="0.00" min="1" step="0.01">
        </div>
        <div>
          <label class="field-label">Image URL</label>
          <input class="field-input" type="url" id="pf-image" value="${esc(p?.image_url||'')}" placeholder="https://..." oninput="previewImg(this.value)">
        </div>
        <div style="grid-column:1/-1;display:none;" id="img-preview-wrap">
          <img id="img-preview-tag" src="" style="width:70px;height:84px;object-fit:cover;border-radius:4px;border:1.5px solid var(--border);">
        </div>
      </div>

      <!-- Sizes -->
      <div>
        <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:13px;margin-bottom:10px;">📐 Sizes *</div>
        <div id="sizes-list"></div>
        <button type="button" onclick="addSize()" class="btn btn-secondary btn-sm" style="margin-top:6px;">+ Add Size</button>
      </div>

      <!-- Colors -->
      <div>
        <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:13px;margin-bottom:10px;">🎨 Colors *</div>
        <div id="colors-list"></div>
        <button type="button" onclick="addColor()" class="btn btn-secondary btn-sm" style="margin-top:6px;">+ Add Color</button>
      </div>

      <!-- Variants -->
      <div id="variants-section" style="display:none;">
        <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:13px;margin-bottom:10px;">📦 Stock per Variant *</div>
        <div id="variants-grid" style="display:flex;flex-direction:column;gap:6px;"></div>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:8px;border-top:1.5px solid var(--border);">
        <button class="btn btn-secondary" onclick="closeProductModal()">Cancel</button>
        <button class="btn btn-primary" id="btn-save-product" onclick="saveProduct()">
          ${editingId ? 'Save Changes' : 'Add Product'}
        </button>
      </div>
    </div>
  `;

  renderSizes(); renderColors();
  if (p?.image_url) previewImg(p.image_url);
}

function previewImg(url) {
  const wrap = document.getElementById('img-preview-wrap');
  const tag  = document.getElementById('img-preview-tag');
  if (!url || !wrap || !tag) return;
  tag.src = url;
  tag.onload  = () => { wrap.style.display='block'; };
  tag.onerror = () => { wrap.style.display='none'; };
}

function addSize(label='') {
  const id = ++sizeId;
  sizes.push({ tempId:id, dbId:null, label });
  renderSizes();
}
function removeSize(id) { sizes=sizes.filter(s=>s.tempId!==id); renderSizes(); rebuildVariants(); }
function updateSizeLabel(id, val) { const s=sizes.find(s=>s.tempId===id); if(s){s.label=val;rebuildVariants();} }
function renderSizes() {
  const el = document.getElementById('sizes-list');
  if (!el) return;
  el.innerHTML = sizes.map(s => `
    <div class="size-row">
      <input type="text" placeholder="XS, S, M, L, XL, Free Size" value="${esc(s.label)}"
        maxlength="20" oninput="updateSizeLabel(${s.tempId}, this.value)" style="flex:1;">
      <button type="button" class="remove-btn" onclick="removeSize(${s.tempId})">✕</button>
    </div>`).join('');
  rebuildVariants();
}

function addColor(name='', hex='#888888') {
  const id = ++colorId;
  colors.push({ tempId:id, dbId:null, name, hex });
  renderColors();
}
function removeColor(id) { colors=colors.filter(c=>c.tempId!==id); renderColors(); rebuildVariants(); }
function updateColorHex(id, val)  { const c=colors.find(c=>c.tempId===id); if(c) c.hex=val; }
function updateColorName(id, val) { const c=colors.find(c=>c.tempId===id); if(c){c.name=val;rebuildVariants();} }
function renderColors() {
  const el = document.getElementById('colors-list');
  if (!el) return;
  el.innerHTML = colors.map(c => `
    <div class="color-row">
      <input type="color" value="${esc(c.hex)}" oninput="updateColorHex(${c.tempId},this.value)"
        style="width:38px;height:34px;padding:2px;border-radius:4px;border:1.5px solid var(--border);cursor:pointer;background:var(--bg);">
      <input type="text" placeholder="Color name" value="${esc(c.name)}"
        maxlength="50" oninput="updateColorName(${c.tempId},this.value)" style="flex:1;">
      <button type="button" class="remove-btn" onclick="removeColor(${c.tempId})">✕</button>
    </div>`).join('');
  rebuildVariants();
}

function rebuildVariants() {
  const section = document.getElementById('variants-section');
  const grid    = document.getElementById('variants-grid');
  if (!section || !grid) return;
  const vs = sizes.filter(s=>s.label.trim());
  const vc = colors.filter(c=>c.name.trim());
  if (!vs.length || !vc.length) { section.style.display='none'; return; }
  section.style.display='block';
  grid.innerHTML = vs.map(s => vc.map(c => {
    const key = s.tempId+'-'+c.tempId;
    const stock = variantStocks[key] ?? 0;
    return `<div style="display:flex;align-items:center;gap:10px;padding:9px 12px;background:var(--bg);border-radius:5px;border:1px solid var(--border);">
      <span style="background:var(--tag-bg);padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;">${esc(s.label)}</span>
      <span style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--muted);">
        <span style="width:10px;height:10px;border-radius:50%;background:${esc(c.hex)};display:inline-block;border:1px solid var(--border);"></span>
        ${esc(c.name)}
      </span>
      <div style="margin-left:auto;display:flex;align-items:center;gap:6px;">
        <label style="font-size:11px;color:var(--muted);">Stock:</label>
        <input type="number" min="0" value="${stock}"
          oninput="variantStocks['${key}']=parseInt(this.value)||0"
          style="width:60px;padding:5px 8px;border:1.5px solid var(--border);border-radius:4px;
                 background:var(--white);font-size:12px;font-weight:600;text-align:center;outline:none;"
          onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border)'">
        <span style="font-size:11px;color:var(--muted);">pcs</span>
      </div>
    </div>`;
  }).join('')).join('');
}

async function saveProduct() {
  const vs = sizes.filter(s=>s.label.trim());
  const vc = colors.filter(c=>c.name.trim());
  const name  = document.getElementById('pf-name')?.value.trim();
  const price = parseFloat(document.getElementById('pf-price')?.value);

  if (!name)      return showToast('Product name is required.','err');
  if (!price||price<=0) return showToast('Enter a valid price.','err');
  if (!vs.length) return showToast('Add at least one size.','err');
  if (!vc.length) return showToast('Add at least one color.','err');

  const sizesPayload  = vs.map(s=>({temp_id:s.tempId,db_id:s.dbId||null,label:s.label.trim()}));
  const colorsPayload = vc.map(c=>({temp_id:c.tempId,db_id:c.dbId||null,name:c.name.trim(),hex:c.hex}));
  const variants = [];
  vs.forEach(s => vc.forEach(c => {
    const key=s.tempId+'-'+c.tempId;
    variants.push({size_temp_id:s.tempId,color_temp_id:c.tempId,db_id:s.dbId||null,stock:variantStocks[key]??0});
  }));

  const btn = document.getElementById('btn-save-product');
  btn.disabled=true; btn.textContent='Saving...';

  const fd = new FormData();
  fd.append('action',          editingId ? 'edit' : 'add');
  fd.append('name',            name);
  fd.append('description',     document.getElementById('pf-description')?.value||'');
  fd.append('base_price',      price);
  fd.append('image_url',       document.getElementById('pf-image')?.value||'');
  fd.append('category',        document.getElementById('pf-category')?.value||'Tops');
  fd.append('condition_label', document.getElementById('pf-condition')?.value||'Good');
  fd.append('sizes',           JSON.stringify(sizesPayload));
  fd.append('colors',          JSON.stringify(colorsPayload));
  fd.append('variants',        JSON.stringify(variants));
  if (editingId) fd.append('id', editingId);

  const res  = await fetch('../api/products.php', { method:'POST', body:fd });
  const data = await res.json();

  if (data.success) {
    showToast(editingId ? 'Product updated ✓' : 'Product added ✓', 'ok');
    closeProductModal();
    setTimeout(() => location.reload(), 700);
  } else {
    showToast(data.message || 'Failed to save.', 'err');
    btn.disabled=false; btn.textContent = editingId ? 'Save Changes' : 'Add Product';
  }
}

function closeProductModal() {
  document.getElementById('product-modal').classList.remove('open');
}
document.getElementById('product-modal').addEventListener('click', closeProductModal);

// Delete
function confirmDelete(id, name) {
  document.getElementById('del-msg').textContent = `"${name}" will be permanently removed from the shop.`;
  document.getElementById('del-modal').classList.add('open');
  document.getElementById('btn-confirm-del').onclick = async function() {
    this.disabled=true; this.textContent='Deleting...';
    const fd=new FormData(); fd.append('action','delete'); fd.append('id',id);
    const res  = await fetch('../api/products.php',{method:'POST',body:fd});
    const data = await res.json();
    document.getElementById('del-modal').classList.remove('open');
    if(data.success) { showToast('Product deleted.',''); setTimeout(()=>location.reload(),600); }
    else { showToast(data.message||'Failed.','err'); this.disabled=false; this.textContent='Delete'; }
  };
}
document.getElementById('del-modal').addEventListener('click', e => {
  if(e.target===document.getElementById('del-modal')) document.getElementById('del-modal').classList.remove('open');
});

// If ?action=add in URL, auto-open modal
<?php if (($_GET['action'] ?? '') === 'add'): ?>
window.addEventListener('load', openAddModal);
<?php endif; ?>
</script>

<?php include 'footer.php'; ?>