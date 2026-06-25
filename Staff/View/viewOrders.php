<?php
session_start();require_once __DIR__.'/../../conn.php';require_once __DIR__.'/../../auth.php';requireStaff();
$r=mysqli_query($conn,"SELECT o.*,c.customer_name,f.furniture_name FROM Orders o JOIN Customer c ON o.customer_id=c.customer_id JOIN Furniture f ON o.furniture_id=f.furniture_id ORDER BY o.order_date DESC");
$rows=[];while($rw=mysqli_fetch_assoc($r))$rows[]=$rw;mysqli_free_result($r);mysqli_close($conn);
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>View Orders</title><link rel="stylesheet" href="../style.css">
<style>.main-content{padding:24px 32px}.tbl-wrap{background:#fff;border-radius:20px;padding:20px;overflow-x:auto;box-shadow:0 1px 3px rgba(0,0,0,.06)}table{width:100%;border-collapse:collapse}th,td{padding:12px 14px;border-bottom:1px solid #e0e0e0;text-align:left}th{font-weight:500;color:#5f6368;font-size:13px}.st{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:500}.st-Open{background:#e8f0fe;color:#1a73e8}.st-Approved{background:#e6f4ea;color:#137333}.st-Rejected{background:#fce8e6;color:#c5221f}</style></head>
<body><div id="sidebar-container"></div><div class="main-content"><div class="top-bar"><div class="page-title">View Orders</div></div>
<div class="content-container"><div class="tbl-wrap"><table><thead><tr><th>ID</th><th>Customer</th><th>Furniture</th><th>Qty</th><th>Total</th><th>Date</th><th>Delivery</th><th>Status</th></tr></thead><tbody>
<?php foreach($rows as $rw):?>
<tr><td>#<?php echo $rw['order_id'];?></td><td><?php echo htmlspecialchars($rw['customer_name']);?></td><td><?php echo htmlspecialchars($rw['furniture_name']);?></td><td><?php echo $rw['order_quantity'];?></td><td>$<?php echo number_format($rw['total_amount'],2);?></td><td><?php echo $rw['order_date'];?></td><td><?php echo $rw['delivery_date'];?></td><td><span class="st st-<?php echo htmlspecialchars($rw['order_status']);?>"><?php echo htmlspecialchars($rw['order_status']);?></span></td></tr>
<?php endforeach;?></tbody></table></div></div></div>
<script src="../sidebar.js"></script><script>loadSidebar('view-orders');</script></body></html>
