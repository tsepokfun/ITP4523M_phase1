<?php
session_start();
require_once __DIR__ . '/../../conn.php';
require_once __DIR__ . '/../../auth.php';
requireCustomer();

$customerId   = currentUserId();
$customerName = currentUserName();

$furnitureId = isset($_GET['fid']) ? (int)$_GET['fid'] : 0;

$stmt = mysqli_prepare($conn,
    "SELECT furniture_id, furniture_name, description, image, price, stock_quantity
     FROM Furniture WHERE furniture_id = ?"
);
mysqli_stmt_bind_param($stmt, 'i', $furnitureId);
mysqli_stmt_execute($stmt);
$furnitureResult = mysqli_stmt_get_result($stmt);
$furniture = mysqli_fetch_assoc($furnitureResult);
mysqli_stmt_close($stmt);

if (!$furniture) { $furniture = null; }

$message = ''; $messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $furniture) {
    $orderQuantity   = isset($_POST['order_quantity'])   ? (int)$_POST['order_quantity'] : 0;
    $deliveryAddress = isset($_POST['delivery_address']) ? trim($_POST['delivery_address']) : '';
    $deliveryDate    = isset($_POST['delivery_date'])    ? trim($_POST['delivery_date']) : '';
    $orderDate       = date('Y-m-d');
    $errors = array();

    if ($orderQuantity < 1) {
        $errors[] = 'Order quantity must be at least 1.';
    }
    if ($orderQuantity > $furniture['stock_quantity']) {
        $errors[] = 'Insufficient stock. Only ' . $furniture['stock_quantity'] . ' item(s) available.';
    }
    if ($deliveryAddress === '') { $errors[] = 'Delivery address is required.'; }
    if ($deliveryDate === '') { $errors[] = 'Delivery date is required.'; }
    if ($deliveryDate !== '' && $deliveryDate < $orderDate) {
        $errors[] = 'Delivery date cannot be earlier than order date.';
    }

    $totalAmount = $furniture['price'] * $orderQuantity;

    if (empty($errors)) {
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn,
                "INSERT INTO Orders (customer_id, furniture_id, order_date, order_quantity,
                 total_amount, delivery_address, delivery_date, order_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'Open')"
            );
            mysqli_stmt_bind_param($stmt, 'iisidss',
                $customerId, $furnitureId, $orderDate, $orderQuantity,
                $totalAmount, $deliveryAddress, $deliveryDate
            );
            if (!mysqli_stmt_execute($stmt)) throw new Exception('Failed to create order.');
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($conn,
                "UPDATE Furniture SET stock_quantity = stock_quantity - ? WHERE furniture_id = ?"
            );
            mysqli_stmt_bind_param($stmt, 'ii', $orderQuantity, $furnitureId);
            if (!mysqli_stmt_execute($stmt)) throw new Exception('Failed to update furniture stock.');
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($conn,
                "SELECT material_id, material_quantity FROM Furniture_Material WHERE furniture_id = ?"
            );
            mysqli_stmt_bind_param($stmt, 'i', $furnitureId);
            mysqli_stmt_execute($stmt);
            $fmResult = mysqli_stmt_get_result($stmt);
            while ($fmRow = mysqli_fetch_assoc($fmResult)) {
                $matId  = $fmRow['material_id'];
                $matQpu = (float)$fmRow['material_quantity'];
                $used   = $orderQuantity * $matQpu;
                $stmt2 = mysqli_prepare($conn,
                    "UPDATE Material SET physical_quantity = physical_quantity - ? WHERE material_id = ?"
                );
                mysqli_stmt_bind_param($stmt2, 'di', $used, $matId);
                if (!mysqli_stmt_execute($stmt2)) throw new Exception('Failed to update material stock.');
                mysqli_stmt_close($stmt2);
            }
            mysqli_stmt_close($stmt);

            mysqli_commit($conn);
            $furniture['stock_quantity'] -= $orderQuantity;
            $message = "Order placed successfully! Product: {$furniture['furniture_name']}, Quantity: {$orderQuantity}, Total: $" . number_format($totalAmount, 2);
            $messageType = 'success';
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $message = $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}

mysqli_close($conn);

function getProductImagePath($fid) {
    return '/image.php?fid=' . ((int)$fid);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make an Order</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:Inter,system-ui,sans-serif;background:#f5f7fb;padding:32px 24px;color:#1e293b}
        .order-container{max-width:960px;margin:0 auto;background:#fff;border-radius:32px;box-shadow:0 8px 20px rgba(0,0,0,.03);padding:28px 32px 40px}
        .top-bar{display:flex;align-items:center;flex-wrap:wrap;margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid #e9edf2}
        .nav-button{background:#fff;border-radius:40px;padding:10px 22px;font-weight:500;font-size:.95rem;text-decoration:none;color:#1e3a5f;border:1px solid #dce3ec;display:inline-flex;align-items:center;gap:8px;transition:.2s}
        .nav-button:hover{background:#f0f4fa;border-color:#b9c4d4;transform:translateY(-1px)}
        .btn-select-all{margin-left:auto}
        h1{font-size:1.9rem;font-weight:600;color:#1e3a5f;margin-bottom:28px;border-left:4px solid #2c6e9e;padding-left:20px}
        .msg-box{padding:14px 18px;border-radius:12px;margin-bottom:24px;font-size:.95rem;font-weight:500}
        .msg-box.error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
        .msg-box.success{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}
        .info-grid{display:flex;flex-wrap:wrap;gap:28px}
        .preview-section{flex:1.2;min-width:240px;background:#fafcff;border-radius:24px;padding:20px;border:1px solid #edf2f7;text-align:center}
        .details-section{flex:2.5;min-width:280px;display:flex;flex-direction:column;gap:20px}
        .form-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:22px}
        .info-item{display:flex;flex-direction:column;gap:8px}
        .info-label{font-size:.85rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#4a627a}
        input,textarea{padding:12px 16px;border-radius:16px;border:1px solid #dce3ec;font-size:.95rem;background:#fff;transition:.2s;font-family:inherit}
        input:focus,textarea:focus{outline:none;border-color:#2c6e9e;box-shadow:0 0 0 3px rgba(44,110,158,.15)}
        input[readonly]{background:#f8fafd;color:#2c3e4e}
        .product-preview-img{max-width:100%;border-radius:20px;margin-top:12px;box-shadow:0 8px 18px rgba(0,0,0,.05);border:1px solid #eef2f8;max-height:260px;object-fit:contain}
        .stock-badge{display:inline-block;padding:4px 14px;border-radius:24px;font-size:.8rem;font-weight:600}.stock-badge.in-stock{background:#dcfce7;color:#166534}.stock-badge.low-stock{background:#fef3c7;color:#92400e}.stock-badge.sold-out{background:#fee2e2;color:#991b1b}
        .price-tag{font-size:1.6rem;font-weight:700;color:#1e4a76;margin:8px 0}
        .button-group{display:flex;flex-wrap:wrap;gap:20px;margin-top:32px;padding-top:20px;border-top:1px solid #edf2f6}
        .button{border:none;padding:12px 28px;border-radius:40px;font-weight:600;font-size:.95rem;cursor:pointer;transition:.2s}
        .button-primary{background:#1e4a76;color:#fff;box-shadow:0 2px 6px rgba(0,0,0,.1)}.button-primary:hover{background:#0f3a5c;transform:translateY(-1px)}.button-primary:disabled{background:#94a3b8;cursor:not-allowed}
        @media(max-width:560px){.top-bar{flex-direction:column;gap:12px}.btn-select-all{margin-left:0}.order-container{padding:20px}}
    </style>
</head>
<body>
<div class="order-container">
    <div class="top-bar">
        <a class="nav-button" href="/customer/homepage.php">&#8592; Back to Homepage</a>
        <a class="nav-button btn-select-all" href="/customer/CreateOrder/selectproduct.php">Select all products &#10133;</a>
    </div>
    <h1>Make an Order</h1>
    <?php if ($message): ?><div class="msg-box <?php echo $messageType; ?>"><?php echo $message; ?></div><?php endif; ?>
    <?php if (!$furniture): ?>
        <div class="msg-box error">Product not found. Please <a href="/customer/CreateOrder/selectproduct.php">select a product</a>.</div>
    <?php else: ?>
    <div class="info-grid">
        <div class="preview-section">
            <div class="info-label">Product Preview</div>
            <img src="<?php echo htmlspecialchars(getProductImagePath($furniture['furniture_id'])); ?>" alt="<?php echo htmlspecialchars($furniture['furniture_name']); ?>" class="product-preview-img" onerror="this.style.display='none'">
            <div class="price-tag">$<?php echo number_format($furniture['price'], 2); ?></div>
            <?php $qty = $furniture['stock_quantity'];
            if ($qty > 10) echo '<span class="stock-badge in-stock">In Stock: ' . $qty . '</span>';
            elseif ($qty > 0) echo '<span class="stock-badge low-stock">Low Stock: ' . $qty . '</span>';
            else echo '<span class="stock-badge sold-out">Sold Out</span>'; ?>
        </div>
        <div class="details-section">
            <form method="post" id="orderForm">
                <div class="form-grid">
                    <div class="info-item"><label class="info-label">Furniture</label><input type="text" readonly value="#<?php echo $furniture['furniture_id']; ?> - <?php echo htmlspecialchars($furniture['furniture_name']); ?>"></div>
                    <div class="info-item"><label class="info-label">Customer</label><input type="text" readonly value="<?php echo htmlspecialchars($customerName); ?> (ID: <?php echo $customerId; ?>)"></div>
                    <div class="info-item"><label class="info-label">Order Date</label><input type="date" value="<?php echo date('Y-m-d'); ?>" readonly></div>
                    <div class="info-item"><label class="info-label" for="order_quantity">Order Quantity</label><input type="number" id="order_quantity" name="order_quantity" min="1" max="<?php echo $furniture['stock_quantity']; ?>" value="1" required></div>
                    <div class="info-item"><label class="info-label">Total Amount ($)</label><input type="text" id="total_amount" readonly value="$<?php echo number_format($furniture['price'], 2); ?>"></div>
                    <div class="info-item" style="grid-column:span 2;"><label class="info-label" for="delivery_address">Delivery Address</label><textarea id="delivery_address" name="delivery_address" rows="2" required></textarea></div>
                    <div class="info-item"><label class="info-label" for="delivery_date">Delivery Date</label><input type="date" id="delivery_date" name="delivery_date" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required></div>
                </div>
                <div class="button-group">
                    <?php if ($furniture['stock_quantity'] > 0): ?><button type="submit" class="button button-primary">Submit Order</button>
                    <?php else: ?><button type="button" class="button button-primary" disabled>Sold Out</button><?php endif; ?>
                    <button type="reset" class="button">Reset</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
<script>
var price = <?php echo $furniture ? $furniture['price'] : 0; ?>;
var qtyInput = document.getElementById('order_quantity');
var totalInput = document.getElementById('total_amount');
if (qtyInput && totalInput) {
    qtyInput.addEventListener('input', function() {
        var qty = parseInt(this.value) || 0;
        totalInput.value = '$' + (qty * price).toFixed(2);
    });
}
</script>
</body>
</html>
