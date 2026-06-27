<?php
session_start();require_once __DIR__.'/../../conn.php';require_once __DIR__.'/../../auth.php';requireStaff();require_once __DIR__.'/../../sort_utils.php';$allowed=['o.order_id'=>'ID','c.customer_name'=>'Customer','f.furniture_name'=>'Furniture','o.order_quantity'=>'Qty','o.total_amount'=>'Total','o.delivery_date'=>'Delivery','o.order_status'=>'Status'];list($sort,$order)=get_sort_params($allowed,'o.order_date');
$msg='';$mt='';

// Handle Update
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['action'])){
    $oid=(int)$_POST['order_id'];
    if($_POST['action']==='update_status'){
        $status=$_POST['order_status'];
        $s=mysqli_prepare($conn,"UPDATE Orders SET order_status=? WHERE order_id=?");mysqli_stmt_bind_param($s,'si',$status,$oid);
        mysqli_stmt_execute($s);mysqli_stmt_close($s);
        $msg="Order #{$oid} status updated.";$mt='success';
    }elseif($_POST['action']==='update_qty'){
        $newQty=(int)$_POST['order_quantity'];
        // Get old quantity and furniture_id
        $qr=mysqli_query($conn,"SELECT order_quantity,furniture_id FROM Orders WHERE order_id=$oid");
        $old=mysqli_fetch_assoc($qr);$oldQty=(int)$old['order_quantity'];$fid=(int)$old['furniture_id'];
        $delta=$newQty-$oldQty;
        mysqli_begin_transaction($conn);
        try{
            $s=mysqli_prepare($conn,"UPDATE Orders SET order_quantity=?,total_amount=total_amount/?*? WHERE order_id=?");mysqli_stmt_bind_param($s,'iiii',$newQty,$oldQty,$newQty,$oid);
            if(!mysqli_stmt_execute($s))throw new Exception('Update failed');mysqli_stmt_close($s);
            // Material stock adjustment
            $fmRes=mysqli_query($conn,"SELECT material_id,material_quantity FROM Furniture_Material WHERE furniture_id=$fid");
            while($fm=mysqli_fetch_assoc($fmRes)){
                $used=$delta*(float)$fm['material_quantity'];
                $s2=mysqli_prepare($conn,"UPDATE Material SET physical_quantity=physical_quantity-? WHERE material_id=?");mysqli_stmt_bind_param($s2,'di',$used,$fm['material_id']);
                if(!mysqli_stmt_execute($s2))throw new Exception('Material stock update failed');mysqli_stmt_close($s2);
            }
            mysqli_commit($conn);$msg="Order #{$oid} quantity updated.";$mt='success';
        }catch(Exception $e){mysqli_rollback($conn);$msg=$e->getMessage();$mt='error';}
    }
}

$r=mysqli_query($conn,"SELECT o.*,c.customer_name,f.furniture_name FROM Orders o JOIN Customer c ON o.customer_id=c.customer_id JOIN Furniture f ON o.furniture_id=f.furniture_id ".sort_clause($sort,$order));
$rows=[];while($rw=mysqli_fetch_assoc($r))$rows[]=$rw;mysqli_free_result($r);mysqli_close($conn);
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Manage Orders</title><link rel="stylesheet" href="../style.css">
<style>.main-content{padding:24px 32px}.tbl-wrap{background:#fff;border-radius:20px;padding:20px;overflow-x:auto;box-shadow:0 1px 3px rgba(0,0,0,.06);margin-bottom:24px}
.m{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:500;font-size:.9rem}.m.e{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}.m.s{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}
table{width:100%;border-collapse:collapse;table-layout:fixed}th,td{padding:10px 12px;border-bottom:1px solid #e0e0e0;text-align:center}th{font-weight:500;color:#5f6368;font-size:13px}
.st{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:500}.st-Open{background:#e8f0fe;color:#1a73e8}.st-Approved{background:#e6f4ea;color:#137333}.st-Rejected{background:#fce8e6;color:#c5221f}
.sel{padding:6px 10px;border-radius:6px;border:1px solid #dadce0;font-size:12px}.btn-sm{padding:6px 14px;border-radius:16px;font-size:12px;font-weight:500;cursor:pointer;border:none;color:#fff;background:#1a73e8}.btn-sm:hover{background:#1557b0}.btn-sm.danger{background:#d93025}.btn-sm.danger:hover{background:#b3261e}
form.inline{display:inline-flex;gap:6px;align-items:center}.qty-inp{width:60px;padding:6px;border-radius:6px;border:1px solid #dadce0;font-size:12px;text-align:center}</style></head>
<body><div id="sidebar-container"></div><div class="main-content"><div class="top-bar"><div class="page-title">Manage Orders</div></div>
<div class="content-container">
<?php if($msg):?><div class="m <?php echo $mt;?>"><?php echo htmlspecialchars($msg);?></div><?php endif;?>
<div class="tbl-wrap"><table><thead><tr><th><?php echo sortable_th('o.order_id','ID',$sort,$order);?></th><th><?php echo sortable_th('c.customer_name','Customer',$sort,$order);?></th><th><?php echo sortable_th('f.furniture_name','Furniture',$sort,$order);?></th><th><?php echo sortable_th('o.order_quantity','Qty',$sort,$order);?></th><th><?php echo sortable_th('o.total_amount','Total',$sort,$order);?></th><th><?php echo sortable_th('o.delivery_date','Delivery',$sort,$order);?></th><th><?php echo sortable_th('o.order_status','Status',$sort,$order);?></th><th>Change Status</th><th>Change Qty</th></tr></thead><tbody>
<?php foreach($rows as $rw):?>
<tr><td>#<?php echo $rw['order_id'];?></td><td><?php echo htmlspecialchars($rw['customer_name']);?></td><td><?php echo htmlspecialchars($rw['furniture_name']);?></td><td><?php echo $rw['order_quantity'];?></td>
<td>$<?php echo number_format($rw['total_amount'],2);?></td><td><?php echo $rw['delivery_date'];?></td><td><span class="st st-<?php echo htmlspecialchars($rw['order_status']);?>"><?php echo htmlspecialchars($rw['order_status']);?></span></td>
<td><form method="post" class="inline"><input type="hidden" name="action" value="update_status"><input type="hidden" name="order_id" value="<?php echo $rw['order_id'];?>">
<select name="order_status" class="sel"><option value="Open" <?php echo $rw['order_status']==='Open'?'selected':'';?>>Open</option><option value="Approved" <?php echo $rw['order_status']==='Approved'?'selected':'';?>>Approved</option><option value="Rejected" <?php echo $rw['order_status']==='Rejected'?'selected':'';?>>Rejected</option></select>
<button type="submit" class="btn-sm">Update</button></form></td>
<td><form method="post" class="inline" onsubmit="return confirm('Change quantity for order #<?php echo $rw['order_id'];?>? Stock will be adjusted.');"><input type="hidden" name="action" value="update_qty"><input type="hidden" name="order_id" value="<?php echo $rw['order_id'];?>">
<input type="number" name="order_quantity" value="<?php echo $rw['order_quantity'];?>" min="1" class="qty-inp">
<button type="submit" class="btn-sm">Change</button></form></td></tr>
<?php endforeach;?></tbody></table></div></div></div>
<script src="../sidebar.js"></script><script>loadSidebar('manage-orders');</script></body></html>
