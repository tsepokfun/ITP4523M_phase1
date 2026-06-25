<?php
session_start();require_once __DIR__.'/../../conn.php';require_once __DIR__.'/../../auth.php';requireStaff();
$r=mysqli_query($conn,"SELECT * FROM Furniture ORDER BY furniture_id");
$rows=[];while($rw=mysqli_fetch_assoc($r))$rows[]=$rw;mysqli_free_result($r);mysqli_close($conn);
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>View Furniture</title><link rel="stylesheet" href="../style.css">
<style>.main-content{padding:24px 32px}.tbl-wrap{background:#fff;border-radius:20px;padding:20px;overflow-x:auto;box-shadow:0 1px 3px rgba(0,0,0,.06)}table{width:100%;border-collapse:collapse}th,td{padding:12px 14px;border-bottom:1px solid #e0e0e0;text-align:left}th{font-weight:500;color:#5f6368;font-size:13px}.low{color:#d93025;font-weight:500}</style></head>
<body><div id="sidebar-container"></div><div class="main-content"><div class="top-bar"><div class="page-title">View Furniture</div></div>
<div class="content-container"><div class="tbl-wrap"><table><thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Description</th></tr></thead><tbody>
<?php foreach($rows as $rw):$low=(int)$rw['stock_quantity']<5;?>
<tr><td><?php echo $rw['furniture_id'];?></td><td><strong><?php echo htmlspecialchars($rw['furniture_name']);?></strong></td><td>$<?php echo number_format($rw['price'],2);?></td><td class="<?php echo $low?'low':'';?>"><?php echo $rw['stock_quantity'];?></td><td><?php echo htmlspecialchars(isset($rw['description']) ? $rw['description'] : '');?></td></tr>
<?php endforeach;?></tbody></table></div></div></div>
<script src="../sidebar.js"></script><script>loadSidebar('view-furniture');</script></body></html>
