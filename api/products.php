<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── LIST PRODUCTS ──────────────────────────────────────────────────────────
if ($action === 'list') {
    $search   = trim($_GET['search']   ?? '');
    $category = trim($_GET['category'] ?? '');

    $where = []; $params = []; $types = '';

    if ($search) {
        $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $like = "%$search%"; $params[] = $like; $params[] = $like; $types .= 'ss';
    }
    if ($category && $category !== 'All') {
        $where[] = "p.category = ?"; $params[] = $category; $types .= 's';
    }

    $sql = "SELECT p.*, COALESCE(SUM(pv.stock),0) AS total_stock FROM products p
            LEFT JOIN product_variants pv ON pv.product_id = p.id";
    if ($where) $sql .= " WHERE " . implode(' AND ', $where);
    $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($products as &$prod) {
        $pid = $prod['id'];
        $prod['sizes']  = $conn->query("SELECT * FROM product_sizes WHERE product_id=$pid ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        $prod['colors'] = $conn->query("SELECT * FROM product_colors WHERE product_id=$pid ORDER BY id")->fetch_all(MYSQLI_ASSOC);
        $variants = $conn->query("SELECT size_id, color_id, stock, id FROM product_variants WHERE product_id=$pid");
        $varMap = [];
        while ($v = $variants->fetch_assoc())
            $varMap[$v['size_id'] . '-' . $v['color_id']] = ['stock' => (int)$v['stock'], 'id' => (int)$v['id']];
        $prod['variant_map'] = $varMap;
    }
    echo json_encode(['success'=>true,'products'=>$products]);
    exit;
}

// ── GET VARIANT ────────────────────────────────────────────────────────────
if ($action === 'get_variant') {
    $size_id  = (int)($_GET['size_id']  ?? 0);
    $color_id = (int)($_GET['color_id'] ?? 0);
    $stmt = $conn->prepare("SELECT id, stock FROM product_variants WHERE size_id=? AND color_id=?");
    $stmt->bind_param('ii', $size_id, $color_id);
    $stmt->execute();
    $variant = $stmt->get_result()->fetch_assoc();
    echo $variant ? json_encode(['success'=>true,'variant'=>$variant])
                  : json_encode(['success'=>false,'message'=>'Variant not found']);
    exit;
}

// ── CATEGORIES ─────────────────────────────────────────────────────────────
if ($action === 'categories') {
    $result = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
    echo json_encode(['success'=>true,'categories'=>array_column($result->fetch_all(MYSQLI_ASSOC),'category')]);
    exit;
}

// ── MY LISTINGS ────────────────────────────────────────────────────────────
if ($action === 'my_listings') {
    requireApiUser();
    $uid = $_SESSION['user_id'];
    $sql = "SELECT p.*, COALESCE(SUM(pv.stock),0) AS total_stock
            FROM products p
            LEFT JOIN product_variants pv ON pv.product_id = p.id
            WHERE p.seller_id = ?
            GROUP BY p.id ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success'=>true,'products'=>$products]);
    exit;
}

// ── PRODUCT DETAIL (PUBLIC — para sa user modal) ───────────────────────────
if ($action === 'detail') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }

    $p = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
    if (!$p) { echo json_encode(['success'=>false,'message'=>'Product not found.']); exit; }

    $p['sizes']  = $conn->query("SELECT * FROM product_sizes WHERE product_id=$id ORDER BY id")->fetch_all(MYSQLI_ASSOC);
    $p['colors'] = $conn->query("SELECT * FROM product_colors WHERE product_id=$id ORDER BY id")->fetch_all(MYSQLI_ASSOC);

    $variants = $conn->query("SELECT size_id, color_id, stock, id FROM product_variants WHERE product_id=$id");
    $varMap = [];
    while ($v = $variants->fetch_assoc())
        $varMap[$v['size_id'] . '-' . $v['color_id']] = ['stock' => (int)$v['stock'], 'id' => (int)$v['id']];
    $p['variant_map'] = $varMap;

    echo json_encode(['success'=>true,'product'=>$p]);
    exit;
}

// ── PRODUCT DETAIL (ADMIN — para sa admin edit modal) ─────────────────────
if ($action === 'product_detail') {
    requireApiAdmin();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }

    $p = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
    if (!$p) { echo json_encode(['success'=>false]); exit; }

    $p['sizes']    = $conn->query("SELECT * FROM product_sizes WHERE product_id=$id")->fetch_all(MYSQLI_ASSOC);
    $p['colors']   = $conn->query("SELECT * FROM product_colors WHERE product_id=$id")->fetch_all(MYSQLI_ASSOC);
    $p['variants'] = $conn->query(
        "SELECT pv.*, ps.size_label, pc.color_name, pc.color_hex
         FROM product_variants pv
         JOIN product_sizes ps ON ps.id = pv.size_id
         JOIN product_colors pc ON pc.id = pv.color_id
         WHERE pv.product_id = $id"
    )->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success'=>true,'product'=>$p]);
    exit;
}

// ── ADD PRODUCT ────────────────────────────────────────────────────────────
if ($action === 'add') {
    requireApiUser();

    $name            = trim($_POST['name']            ?? '');
    $description     = trim($_POST['description']     ?? '');
    $base_price      = (float)($_POST['base_price']   ?? 0);
    $image_url       = trim($_POST['image_url']       ?? '');
    $category        = trim($_POST['category']        ?? 'Tops');
    $condition_label = trim($_POST['condition_label'] ?? 'Good');
    $sizes    = json_decode($_POST['sizes']    ?? '[]', true) ?: [];
    $colors   = json_decode($_POST['colors']   ?? '[]', true) ?: [];
    $variants = json_decode($_POST['variants'] ?? '[]', true) ?: [];

    if (!$name || $base_price <= 0)
        die(json_encode(['success'=>false,'message'=>'Name and valid price are required.']));
    if (empty($sizes) || empty($colors))
        die(json_encode(['success'=>false,'message'=>'Add at least one size and one color.']));

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO products (name,description,base_price,image_url,category,condition_label) VALUES (?,?,?,?,?,?)");
        $seller_id = isset($_POST['seller_id']) ? (int)$_POST['seller_id'] : null;
        $stmt->bind_param('ssdsss', $name, $description, $base_price, $image_url, $category, $condition_label);
        $stmt->execute();
        $pid = $conn->insert_id;
        if ($seller_id) {
            $conn->query("UPDATE products SET seller_id=$seller_id WHERE id=$pid");
        }

        $size_id_map = [];
        foreach ($sizes as $sz) {
            $label = trim($sz['label'] ?? '');
            if (!$label) continue;
            $s = $conn->prepare("INSERT INTO product_sizes (product_id,size_label) VALUES (?,?)");
            $s->bind_param('is', $pid, $label);
            $s->execute();
            $size_id_map[$sz['temp_id']] = $conn->insert_id;
        }

        $color_id_map = [];
        foreach ($colors as $cl) {
            $cname = trim($cl['name'] ?? ''); $chex = trim($cl['hex'] ?? '#888888');
            if (!$cname) continue;
            $c = $conn->prepare("INSERT INTO product_colors (product_id,color_name,color_hex) VALUES (?,?,?)");
            $c->bind_param('iss', $pid, $cname, $chex);
            $c->execute();
            $color_id_map[$cl['temp_id']] = $conn->insert_id;
        }

        foreach ($variants as $v) {
            $sid   = $size_id_map[$v['size_temp_id']]   ?? null;
            $cid   = $color_id_map[$v['color_temp_id']] ?? null;
            $stock = max(0, (int)($v['stock'] ?? 0));
            if (!$sid || !$cid) continue;
            $vst = $conn->prepare("INSERT INTO product_variants (product_id,size_id,color_id,stock) VALUES (?,?,?,?)");
            $vst->bind_param('iiii', $pid, $sid, $cid, $stock);
            $vst->execute();
        }

        $conn->commit();
        echo json_encode(['success'=>true,'message'=>'Product added!','id'=>$pid]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success'=>false,'message'=>'Failed: '.$e->getMessage()]);
    }
    exit;
}

// ── UPDATE VARIANT STOCK (admin) ───────────────────────────────────────────
if ($action === 'update_variant_stock') {
    requireApiAdmin();
    $variant_id = (int)($_POST['variant_id'] ?? 0);
    $stock      = max(0, (int)($_POST['stock'] ?? 0));
    $stmt = $conn->prepare("UPDATE product_variants SET stock=? WHERE id=?");
    $stmt->bind_param('ii', $stock, $variant_id);
    $stmt->execute();
    echo json_encode(['success'=>true]);
    exit;
}

// ── DELETE PRODUCT (admin) ─────────────────────────────────────────────────
if ($action === 'delete') {
    requireApiAdmin();
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['success'=>true,'message'=>'Product deleted.']);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Unknown action.']);