<?php
session_start();
require_once __DIR__ . '/../../conn.php';
require_once __DIR__ . '/../../auth.php';
requireStaff();

$msg = '';
$mt = '';

$cr = mysqli_query($conn, "SELECT customer_id, customer_name FROM Customer ORDER BY customer_name");
$cs = array();
while ($rw = mysqli_fetch_assoc($cr)) {
    $cs[] = $rw;
}
mysqli_free_result($cr);

$fr = mysqli_query($conn, "SELECT furniture_id, furniture_name, price FROM Furniture ORDER BY furniture_name");
$fs = array();
while ($rw = mysqli_fetch_assoc($fr)) {
    $fs[] = $rw;
}
mysqli_free_result($fr);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cid = (int)(isset($_POST['customer_id']) ? $_POST['customer_id'] : 0);
    $fid = (int)(isset($_POST['furniture_id']) ? $_POST['furniture_id'] : 0);
    $qty = (int)(isset($_POST['order_quantity']) ? $_POST['order_quantity'] : 0);
    $ad = trim(isset($_POST['delivery_address']) ? $_POST['delivery_address'] : '');
    $dd = isset($_POST['delivery_date']) ? $_POST['delivery_date'] : '';
    $od = date('Y-m-d');

    if ($cid < 1 || $fid < 1 || $qty < 1 || $ad === '' || $dd === '') {
        $msg = 'All fields required.';
        $mt = 'error';
    } else {
        $priceResult = mysqli_query($conn, "SELECT price FROM Furniture WHERE furniture_id = $fid");
        $priceRow = $priceResult ? mysqli_fetch_assoc($priceResult) : null;
        if ($priceResult) {
            mysqli_free_result($priceResult);
        }

        if (!$priceRow) {
            $msg = 'Furniture not found.';
            $mt = 'error';
        } else {
            $price = (float)$priceRow['price'];
            $stock = getDerivedStock($conn, $fid);
            if ($qty > $stock) {
                $msg = 'Order quantity exceeds available stock. Please reduce.';
                $mt = 'error';
            } else {
                $total = $price * $qty;
                mysqli_begin_transaction($conn);
                try {
                    $s = mysqli_prepare($conn, "INSERT INTO Orders(customer_id, furniture_id, order_date, order_quantity, total_amount, delivery_address, delivery_date, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Open')");
                    mysqli_stmt_bind_param($s, 'iisidss', $cid, $fid, $od, $qty, $total, $ad, $dd);
                    if (!mysqli_stmt_execute($s)) {
                        throw new Exception('Insert failed');
                    }
                    mysqli_stmt_close($s);

                    $fmr = mysqli_query($conn, "SELECT material_id, material_quantity FROM Furniture_Material WHERE furniture_id = $fid");
                    while ($fm = mysqli_fetch_assoc($fmr)) {
                        $used = $qty * (float)$fm['material_quantity'];
                        $s2 = mysqli_prepare($conn, "UPDATE Material SET physical_quantity = physical_quantity - ? WHERE material_id = ?");
                        mysqli_stmt_bind_param($s2, 'di', $used, $fm['material_id']);
                        if (!mysqli_stmt_execute($s2)) {
                            throw new Exception('Material stock update failed');
                        }
                        mysqli_stmt_close($s2);
                    }
                    if ($fmr) {
                        mysqli_free_result($fmr);
                    }

                    mysqli_commit($conn);
                    $msg = 'Order created. Total: $' . number_format($total, 2);
                    $mt = 'success';
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $msg = $e->getMessage();
                    $mt = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Create Order</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .mc{padding:24px 32px}.fc{background:#fff;border-radius:20px;padding:32px;max-width:700px;box-shadow:0 1px 3px rgba(0,0,0,.06)}.m{padding:12px 16px;border-radius:10px;margin-bottom:20px;font-weight:500;font-size:.9rem}.me{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}.ms{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}.fg{margin-bottom:16px}.fg label{display:block;font-size:13px;font-weight:500;color:#5f6368;margin-bottom:6px}.fg input,.fg select{width:100%;padding:10px 14px;border:1px solid #dadce0;border-radius:8px;font-size:15px;outline:none}.fg input:focus,.fg select:focus{border-color:#1a73e8;box-shadow:0 0 0 2px rgba(26,115,232,.2)}.fr{display:grid;grid-template-columns:1fr 1fr;gap:16px}.btn{padding:10px 28px;border-radius:24px;font-size:14px;font-weight:500;cursor:pointer;border:none;background:#1a73e8;color:#fff;margin-top:12px}.btn:hover{background:#1557b0}
    </style>
</head>
<body>
<div id="sidebar-container"></div>
<div class="mc">
    <div class="top-bar"><div class="page-title">Create Order</div></div>
    <div class="content-container">
        <div class="fc">
            <?php if ($msg): ?><div class="m m<?php echo $mt[0]; ?>"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
            <form method="post">
                <div class="fr">
                    <div class="fg">
                        <label>Customer *</label>
                        <select name="customer_id" required>
                            <option value="">-- Select --</option>
                            <?php foreach ($cs as $c): ?>
                                <option value="<?php echo $c['customer_id']; ?>"><?php echo htmlspecialchars($c['customer_name']); ?> (#<?php echo $c['customer_id']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="fg">
                        <label>Furniture *</label>
                        <select name="furniture_id" required>
                            <option value="">-- Select --</option>
                            <?php foreach ($fs as $f): ?>
                                <option value="<?php echo $f['furniture_id']; ?>"><?php echo htmlspecialchars($f['furniture_name']); ?> - $<?php echo number_format($f['price'], 2); ?> (stock: <?php echo getDerivedStock($conn, $f['furniture_id']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="fr">
                    <div class="fg"><label>Order Quantity *</label><input type="number" name="order_quantity" min="1" value="1" required></div>
                    <div class="fg"><label>Delivery Date *</label><input type="date" name="delivery_date" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required></div>
                </div>
                <div class="fg"><label>Delivery Address *</label><input type="text" name="delivery_address" required placeholder="Enter delivery address"></div>
                <button type="submit" class="btn">Create Order</button>
            </form>
        </div>
    </div>
</div>
<?php mysqli_close($conn); ?>
<script src="../sidebar.js"></script><script>loadSidebar('create-order');</script>
</body>
</html>
