<?php
session_start();require_once __DIR__.'/../../conn.php';require_once __DIR__.'/../../auth.php';require_once __DIR__.'/../../sort_utils.php';requireStaff();
$allowed=['material_id'=>'ID','material_name'=>'Name','physical_quantity'=>'Quantity','unit'=>'Unit'];list($sort,$order)=get_sort_params($allowed,'material_id');
$r=mysqli_query($conn,"SELECT * FROM Material ".sort_clause($sort,$order));
$rows=[];while($rw=mysqli_fetch_assoc($r))$rows[]=$rw;mysqli_free_result($r);mysqli_close($conn);
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>View Materials</title><link rel="stylesheet" href="../style.css">
<style>.main-content{padding:24px 32px}.tbl-wrap{background:#fff;border-radius:20px;padding:20px;overflow-x:auto;box-shadow:0 1px 3px rgba(0,0,0,.06)}table{width:100%;border-collapse:collapse;table-layout:fixed}th,td{padding:12px 14px;border-bottom:1px solid #e0e0e0;text-align:center}th{font-weight:500;color:#5f6368;font-size:13px}.low{color:#d93025;font-weight:500}</style></head>
<body><div id="sidebar-container"></div><div class="main-content"><div class="top-bar"><div class="page-title">View Materials</div></div>
<div class="content-container"><div class="tbl-wrap"><table><thead><tr><th><?php echo sortable_th('material_id','ID',$sort,$order);?></th><th><?php echo sortable_th('material_name','Name',$sort,$order);?></th><th><?php echo sortable_th('physical_quantity','Quantity',$sort,$order);?></th><th><?php echo sortable_th('unit','Unit',$sort,$order);?></th></tr></thead><tbody>
<?php foreach($rows as $rw):$low=(int)$rw['physical_quantity']<50;?>
<tr><td><?php echo $rw['material_id'];?></td><td><strong><?php echo htmlspecialchars($rw['material_name']);?></strong></td><td class="<?php echo $low?'low':'';?>"><?php echo $rw['physical_quantity'];?></td><td><?php echo htmlspecialchars($rw['unit']);?></td></tr>
<?php endforeach;?></tbody></table></div></div></div>
<script src="../sidebar.js"></script><script>loadSidebar('view-materials');</script></body></html>
