<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
header('Content-Type: application/json');

requireApiUser();
$user_id = $_SESSION['user_id'];
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

// ── LIST CART ──────────────────────────────────────────────────────────────
if ($action === 'list') {
    $sql = "SELECT c.id, c.quantity, c.variant_id,
                   p.id AS product_id, p.name, p.base_price, p.image_url, p.category,
                   ps.size_label, pc.color_name, pc.color_hex,
                   pv.stock
            FROM cart c
            JOIN products p         ON p.id  = c.product_id
            JOIN product_variants pv ON pv.id = c.variant_id
            JOIN product_sizes ps    ON ps.id = pv.size_id
            JOIN product_colors pc   ON pc.id = pv.color_id
            WHERE c.user_id = ?
            ORDER BY c.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $total = array_sum(array_map(fn($i) => $i['base_price'] * $i['quantity'], $items));
    $count = array_sum(array_column($items, 'quantity'));

    echo json_encode(['success'=>true,'items'=>$items,'total'=>$total,'count'=>$count]);
    exit;
}

// ── ADD TO CART ────────────────────────────────────────────────────────────
if ($action === 'add') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $variant_id = (int)($_POST['variant_id'] ?? 0);
    $quantity   = max(1, (int)($_POST['quantity'] ?? 1));

    if (!$product_id || !$variant_id)
        die(json_encode(['success'=>false,'message'=>'Invalid product or variant.']));

    // Check stock
    $sv = $conn->prepare("SELECT stock FROM product_variants WHERE id=?");
    $sv->bind_param('i', $variant_id);
    $sv->execute();
    $variant = $sv->get_result()->fetch_assoc();

    if (!$variant || $variant['stock'] < 1)
        die(json_encode(['success'=>false,'message'=>'This variant is out of stock.']));

    // Check existing cart
    $ce = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id=? AND variant_id=?");
    $ce->bind_param('ii', $user_id, $variant_id);
    $ce->execute();
    $existing = $ce->get_result()->fetch_assoc();

    if ($existing) {
        $new_qty = $existing['quantity'] + $quantity;
        if ($new_qty > $variant['stock'])
            $new_qty = $variant['stock'];
        $upd = $conn->prepare("UPDATE cart SET quantity=? WHERE id=?");
        $upd->bind_param('ii', $new_qty, $existing['id']);
        $upd->execute();
    } else {
        if ($quantity > $variant['stock']) $quantity = $variant['stock'];
        $ins = $conn->prepare("INSERT INTO cart (user_id,product_id,variant_id,quantity) VALUES (?,?,?,?)");
        $ins->bind_param('iiii', $user_id, $product_id, $variant_id, $quantity);
        $ins->execute();
    }

    // Return new count
    $cnt = $conn->prepare("SELECT COALESCE(SUM(quantity),0) AS total FROM cart WHERE user_id=?");
    $cnt->bind_param('i', $user_id);
    $cnt->execute();
    $count = (int)$cnt->get_result()->fetch_assoc()['total'];

    echo json_encode(['success'=>true,'message'=>'Added to cart!','count'=>$count]);
    exit;
}

// ── UPDATE QUANTITY ────────────────────────────────────────────────────────
if ($action === 'update') {
    $cart_id  = (int)($_POST['cart_id']  ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    // Verify ownership
    $own = $conn->prepare("SELECT c.id, pv.stock FROM cart c JOIN product_variants pv ON pv.id=c.variant_id WHERE c.id=? AND c.user_id=?");
    $own->bind_param('ii', $cart_id, $user_id);
    $own->execute();
    $row = $own->get_result()->fetch_assoc();

    if (!$row) die(json_encode(['success'=>false,'message'=>'Cart item not found.']));
    if ($quantity > $row['stock']) $quantity = $row['stock'];

    $upd = $conn->prepare("UPDATE cart SET quantity=? WHERE id=?");
    $upd->bind_param('ii', $quantity, $cart_id);
    $upd->execute();

    echo json_encode(['success'=>true]);
    exit;
}

// ── REMOVE FROM CART ───────────────────────────────────────────────────────
if ($action === 'remove') {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    $del = $conn->prepare("DELETE FROM cart WHERE id=? AND user_id=?");
    $del->bind_param('ii', $cart_id, $user_id);
    $del->execute();

    $cnt = $conn->prepare("SELECT COALESCE(SUM(quantity),0) AS total FROM cart WHERE user_id=?");
    $cnt->bind_param('i', $user_id);
    $cnt->execute();
    $count = (int)$cnt->get_result()->fetch_assoc()['total'];

    echo json_encode(['success'=>true,'count'=>$count]);
    exit;
}

// ── CLEAR CART ─────────────────────────────────────────────────────────────
if ($action === 'clear') {
    $del = $conn->prepare("DELETE FROM cart WHERE user_id=?");
    $del->bind_param('i', $user_id);
    $del->execute();
    echo json_encode(['success'=>true]);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Unknown action.']);