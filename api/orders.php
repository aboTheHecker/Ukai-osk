<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
header('Content-Type: application/json');

requireApiUser();
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

// ── PLACE ORDER ────────────────────────────────────────────────────────────
if ($action === 'place') {
    $recipient_name    = trim($_POST['recipient_name']    ?? '');
    $recipient_email   = trim($_POST['recipient_email']   ?? '');
    $recipient_contact = trim($_POST['recipient_contact'] ?? '');
    $recipient_address = trim($_POST['recipient_address'] ?? '');
    $payment_method    = trim($_POST['payment_method']    ?? 'cod');
    $notes             = trim($_POST['notes']             ?? '');

    if (!$recipient_name || !$recipient_email || !$recipient_contact || !$recipient_address)
        die(json_encode(['success'=>false,'message'=>'Please fill in all delivery fields.']));

    // Get cart items
    $sql = "SELECT c.quantity, c.variant_id,
                   p.id AS product_id, p.name, p.base_price, p.image_url,
                   ps.size_label, pc.color_name, pc.color_hex, pv.stock
            FROM cart c
            JOIN products p          ON p.id  = c.product_id
            JOIN product_variants pv ON pv.id = c.variant_id
            JOIN product_sizes ps    ON ps.id = pv.size_id
            JOIN product_colors pc   ON pc.id = pv.color_id
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (!$items) die(json_encode(['success'=>false,'message'=>'Your cart is empty.']));

    // Validate stock
    foreach ($items as $item) {
        if ($item['quantity'] > $item['stock']) {
            die(json_encode(['success'=>false,
                'message'=>"'{$item['name']}' only has {$item['stock']} in stock."]));
        }
    }

    $total = array_sum(array_map(fn($i) => $i['base_price'] * $i['quantity'], $items));

    $conn->begin_transaction();
    try {
        // Insert order
        $ord = $conn->prepare(
            "INSERT INTO orders (user_id,total_amount,payment_method,recipient_name,recipient_address,recipient_email,recipient_contact,notes)
             VALUES (?,?,?,?,?,?,?,?)");
        $ord->bind_param('idssssss', $user_id, $total, $payment_method,
            $recipient_name, $recipient_address, $recipient_email, $recipient_contact, $notes);
        $ord->execute();
        $order_id = $conn->insert_id;

        // Insert order items + deduct stock
        foreach ($items as $item) {
            $oi2 = $conn->prepare(
                "INSERT INTO order_items (order_id,product_id,product_name,size_label,color_name,color_hex,quantity,price,image_url)
                 VALUES (?,?,?,?,?,?,?,?,?)");
            $oi2->bind_param('iissssids',
                $order_id, $item['product_id'], $item['name'],
                $item['size_label'], $item['color_name'], $item['color_hex'],
                $item['quantity'], $item['base_price'], $item['image_url']);
            $oi2->execute();

            // Deduct stock
            $ds = $conn->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ?");
            $ds->bind_param('ii', $item['quantity'], $item['variant_id']);
            $ds->execute();
        }

        // Clear cart
        $del = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $del->bind_param('i', $user_id);
        $del->execute();

        $conn->commit();
        echo json_encode(['success'=>true,'order_id'=>$order_id]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success'=>false,'message'=>'Order failed: '.$e->getMessage()]);
    }
    exit;
}

// ── MY ORDERS (user) ───────────────────────────────────────────────────────
if ($action === 'my_orders') {
    $stmt = $conn->prepare(
        "SELECT o.*,
                (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) AS item_count
         FROM orders o WHERE o.user_id=? ORDER BY o.created_at DESC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($orders as &$order) {
        $oi = $conn->prepare(
            "SELECT product_name, size_label, color_name, color_hex, quantity, price, image_url
             FROM order_items WHERE order_id=? LIMIT 5");
        $oi->bind_param('i', $order['id']);
        $oi->execute();
        $order['items'] = $oi->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    echo json_encode(['success'=>true,'orders'=>$orders]);
    exit;
}

// ── ORDER DETAIL (user + admin) ────────────────────────────────────────────
if ($action === 'detail') {
    $order_id = (int)($_GET['id'] ?? 0);
    if (!$order_id) die(json_encode(['success'=>false,'message'=>'Invalid order ID.']));

    if ($is_admin) {
        // Admin can view any order
        $stmt = $conn->prepare(
            "SELECT o.*, u.name AS customer_name, u.email AS customer_email
             FROM orders o JOIN users u ON u.id = o.user_id
             WHERE o.id = ?");
        $stmt->bind_param('i', $order_id);
    } else {
        // User can only view their own orders
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
        $stmt->bind_param('ii', $order_id, $user_id);
    }

    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    if (!$order) die(json_encode(['success'=>false,'message'=>'Order not found.']));

    $oi = $conn->prepare("SELECT * FROM order_items WHERE order_id=?");
    $oi->bind_param('i', $order_id);
    $oi->execute();
    $order['items'] = $oi->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success'=>true,'order'=>$order]);
    exit;
}

// ── UPDATE STATUS (admin only) ─────────────────────────────────────────────
if ($action === 'update_status') {
    requireApiAdmin();
    $order_id = (int)($_POST['order_id'] ?? 0);
    $status   = trim($_POST['status'] ?? '');
    $allowed  = ['pending','processing','shipped','delivered','cancelled'];

    if (!$order_id) die(json_encode(['success'=>false,'message'=>'Invalid order ID.']));
    if (!in_array($status, $allowed))
        die(json_encode(['success'=>false,'message'=>'Invalid status.']));

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param('si', $status, $order_id);
    $stmt->execute();

    echo json_encode(['success'=>true]);
    exit;
}

// ── CANCEL ORDER (user only) ───────────────────────────────────────────────
if ($action === 'cancel') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $stmt = $conn->prepare(
        "UPDATE orders SET status='cancelled' WHERE id=? AND user_id=? AND status='pending'");
    $stmt->bind_param('ii', $order_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Cannot cancel this order.']);
    }
    exit;
}

echo json_encode(['success'=>false,'message'=>'Unknown action.']);