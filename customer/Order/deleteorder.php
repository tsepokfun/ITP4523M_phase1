<?php
session_start();require_once __DIR__.'/../../conn.php';require_once __DIR__.'/../../auth.php';requireCustomer();
$cid=currentUserId();$cn=currentUserName();$msg='';$mt='';

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['order_id'])){
    $oid=(int)$_POST['order_id'];
    $s=mysqli_prepare($conn,"SELECT order_id,furniture_id,order_quantity,delivery_date FROM Orders WHERE order_id=? AND customer_id=?");
    mysqli_stmt_bind_param($s,'ii',$oid,$cid);mysqli_stmt_execute($s);$or=mysqli_stmt_get_result($s);$o=mysqli_fetch_assoc($or);mysqli_stmt_close($s);
    if(!$o){$msg='Order not found.';$mt='error';}
    else{
        $days=(strtotime($o['delivery_date'])-strtotime(date('Y-m-d')))/86400;
        if($days<2){$msg='Cannot delete: delivery date is less than 2 days away.';$mt='error';}
        else{
            mysqli_begin_transaction($conn);
            try{
                $fid=(int)$o['furniture_id'];$qty=(int)$o['order_quantity'];
                $s=mysqli_prepare($conn,"DELETE FROM Orders WHERE order_id=?");mysqli_stmt_bind_param($s,'i',$oid);
                if(!mysqli_stmt_execute($s))throw new Exception('Delete failed');mysqli_stmt_close($s);
                $s=mysqli_prepare($conn,"UPDATE Furniture SET stock_quantity=stock_quantity+? WHERE furniture_id=?");mysqli_stmt_bind_param($s,'ii',$qty,$fid);
                if(!mysqli_stmt_execute($s))throw new Exception('Stock restore failed');mysqli_stmt_close($s);
                $fm=mysqli_query($conn,"SELECT material_id,material_quantity FROM Furniture_Material WHERE furniture_id=$fid");
                while($fr=mysqli_fetch_assoc($fm)){
                    $used=$qty*(float)$fr['material_quantity'];
                    $s2=mysqli_prepare($conn,"UPDATE Material SET physical_quantity=physical_quantity+? WHERE material_id=?");mysqli_stmt_bind_param($s2,'di',$used,$fr['material_id']);
                    if(!mysqli_stmt_execute($s2))throw new Exception('Material restore failed');mysqli_stmt_close($s2);
                }
                mysqli_commit($conn);$msg="Order #{$oid} deleted. Stocks restored.";$mt='success';
            }catch(Exception $e){mysqli_rollback($conn);$msg=$e->getMessage();$mt='error';}
        }
    }
}

$s=mysqli_prepare($conn,"SELECT o.order_id,o.order_date,o.furniture_id,f.furniture_name,o.order_quantity,o.total_amount,o.delivery_date,o.order_status FROM Orders o JOIN Furniture f ON o.furniture_id=f.furniture_id WHERE o.customer_id=? ORDER BY o.delivery_date DESC");
mysqli_stmt_bind_param($s,'i',$cid);mysqli_stmt_execute($s);$res=mysqli_stmt_get_result($s);
$ords=array();while($rw=mysqli_fetch_assoc($res))$ords[]=$rw;mysqli_stmt_close($s);mysqli_close($conn);
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Delete Order</title><link rel="stylesheet" href="../style.css">
<style>.page-header{text-align:center;margin-bottom:30px}.page-header h1{font-size:2rem;color:#202124;margin-bottom:8px}.page-header p{color:#5f6368;font-size:1rem}.msg{padding:14px 18px;border-radius:12px;margin-bottom:24px;font-weight:500;font-size:.95rem}.msg.e{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}.msg.s{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}.filter-bar{background:#fff;border-radius:24px;padding:16px 24px;margin-bottom:28px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;border:1px solid #f0f2f5}#tableSearch{padding:10px 16px;border-radius:40px;border:1px solid #dadce0;width:240px;outline:none}#tableSearch:focus{border-color:#1a73e8;box-shadow:0 0 0 2px rgba(26,115,232,.2)}.tbl-wrap{overflow-x:auto;border-radius:20px;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.05);border:1px solid #f0f2f5}table{width:100%;border-collapse:collapse;font-size:.9rem}th{background:#f8f9fc;padding:16px 14px;font-weight:600;color:#202124;border-bottom:1px solid #e9ecef;white-space:nowrap}td{padding:14px 14px;border-bottom:1px solid #f1f3f4;color:#3c4043}tr:hover{background-color:#fafbfe}.st{display:inline-block;padding:4px 12px;border-radius:40px;font-size:.75rem;font-weight:500;background:#e8f0fe;color:#1a73e8;text-transform:capitalize}.btn-d{background:none;border:none;color:#d93025;font-weight:500;cursor:pointer;padding:6px 14px;border-radius:40px;transition:.2s;font-size:.8rem}.btn-d:hover{background:#fce8e6}.btn-d:disabled{color:#9aa0a6;cursor:not-allowed;background:none}.days{font-size:.75rem;color:#5f6368;margin-left:8px}.days.soon{color:#d93025;font-weight:500}.empty{text-align:center;padding:48px 20px;color:#5f6368}@media(max-width:768px){.filter-bar{flex-direction:column;align-items:stretch}#tableSearch{width:100%}}</style></head>
<body><a href="../homepage.php" class="button-back">&#8592;</a><div class="card-container"><div class="page-header"><h1>Delete Order</h1><p><?php echo htmlspecialchars($cn);?> - Remove an order</p></div>
<?php if($msg):?><div class="msg <?php echo $mt;?>"><?php echo htmlspecialchars($msg);?></div><?php endif;?>
<div class="filter-bar"><div><span style="font-weight:500;color:#5f6368">Search:</span> <input type="text" id="tableSearch" placeholder="Order ID, status..."></div><a href="order.php" style="color:#1a73e8;text-decoration:none;padding:8px 18px;border:1px solid #dadce0;border-radius:40px;">View Orders</a></div>
<div class="tbl-wrap"><?php if(empty($ords)):?><div class="empty">No orders to delete.</div><?php else:?>
<table><thead><tr><th>Order ID</th><th>Date</th><th>Furniture</th><th>Qty</th><th>Total</th><th>Delivery</th><th>Status</th><th>Action</th></tr></thead><tbody id="tableBody">
<?php foreach($ords as $o):$dd=$o['delivery_date'];$dl=(strtotime($dd)-strtotime(date('Y-m-d')))/86400;$can=($dl>=2);?>
<tr><td><strong>#<?php echo $o['order_id'];?></strong></td><td><?php echo $o['order_date'];?></td><td><?php echo htmlspecialchars($o['furniture_name']);?> (#<?php echo $o['furniture_id'];?>)</td><td><?php echo $o['order_quantity'];?></td><td>$<?php echo number_format($o['total_amount'],2);?></td><td><?php echo htmlspecialchars($dd);?><span class="days<?php echo $dl<2?' soon':'';?>">(<?php echo $dl>=0?floor($dl).'d':'past';?>)</span></td><td><span class="st"><?php echo htmlspecialchars($o['order_status']);?></span></td><td><?php if($can):?><form method="post" style="display:inline" onsubmit="return confirm('Delete order #<?php echo $o['order_id'];?>? This will restore all stocks.');"><input type="hidden" name="order_id" value="<?php echo $o['order_id'];?>"><button type="submit" class="btn-d">Delete</button></form><?php else:?><button type="button" class="btn-d" disabled title="Only 2+ days before delivery">Locked</button><?php endif;?></td></tr>
<?php endforeach;?></tbody></table><?php endif;?></div></div>
<script>(function(){var si=document.getElementById('tableSearch');if(si){si.addEventListener('input',function(){var t=this.value.toLowerCase();var rs=document.querySelectorAll('#tableBody tr');rs.forEach(function(r){r.style.display=r.innerText.toLowerCase().indexOf(t)!==-1?'':'none';});});}})();</script>
</body></html>

