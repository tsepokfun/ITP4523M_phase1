<?php
session_start();require_once __DIR__.'/../../conn.php';require_once __DIR__.'/../../auth.php';requireStaff();require_once __DIR__.'/../../sort_utils.php';$allowed=['staff_id'=>'ID','staff_name'=>'Name','password'=>'Password'];list($sort,$order)=get_sort_params($allowed,'staff_id');
$msg='';$mt='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $sid=(int)$_POST['staff_id'];
    if(isset($_POST['delete'])){
        if($sid===(int)currentUserId()){$msg="Cannot delete your own account.";$mt='error';}
        else{mysqli_query($conn,"DELETE FROM Staff WHERE staff_id=$sid");$msg="Staff deleted.";$mt='success';}
    }elseif(isset($_POST['update'])){
        $name=trim($_POST['staff_name']);$pw=trim($_POST['password']);
        $s=mysqli_prepare($conn,"UPDATE Staff SET staff_name=?,password=? WHERE staff_id=?");
        mysqli_stmt_bind_param($s,'ssi',$name,$pw,$sid);
        if(mysqli_stmt_execute($s)){$msg="Staff updated.";$mt='success';}else{$msg="Update failed.";$mt='error';}mysqli_stmt_close($s);
    }
}
$r=mysqli_query($conn,"SELECT * FROM Staff ".sort_clause($sort,$order));
$rows=[];while($rw=mysqli_fetch_assoc($r))$rows[]=$rw;mysqli_free_result($r);mysqli_close($conn);
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Manage Staff</title><link rel="stylesheet" href="../style.css">
<style>.main-content{padding:24px 32px}.tbl-wrap{background:#fff;border-radius:20px;padding:20px;overflow-x:auto;box-shadow:0 1px 3px rgba(0,0,0,.06)}.m{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:500;font-size:.9rem}.m.e{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}.m.s{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}
table{width:100%;border-collapse:collapse;table-layout:fixed}th,td{padding:10px 12px;border-bottom:1px solid #e0e0e0;text-align:center}th{font-weight:500;color:#5f6368;font-size:13px}.inp-sm{padding:6px 8px;border-radius:6px;border:1px solid #dadce0;font-size:12px;width:120px}.btn-sm{padding:6px 12px;border-radius:16px;font-size:12px;font-weight:500;cursor:pointer;border:none;color:#fff;background:#1a73e8}.btn-sm:hover{background:#1557b0}.btn-sm.danger{background:#d93025}.btn-sm.danger:hover{background:#b3261e}.btn-sm:disabled{background:#9aa0a6;cursor:not-allowed}</style></head>
<body><div id="sidebar-container"></div><div class="main-content"><div class="top-bar"><div class="page-title">Manage Staff</div></div>
<div class="content-container"><?php if($msg):?><div class="m <?php echo $mt;?>"><?php echo htmlspecialchars($msg);?></div><?php endif;?>
<div class="tbl-wrap"><table><thead><tr><th><?php echo sortable_th('staff_id','ID',$sort,$order);?></th><th><?php echo sortable_th('staff_name','Name',$sort,$order);?></th><th><?php echo sortable_th('password','Password',$sort,$order);?></th><th>Edit</th><th>Delete</th></tr></thead><tbody>
<?php foreach($rows as $rw):$sid=$rw['staff_id'];$isSelf=($sid===(int)currentUserId());?>
<tr><form method="post"><input type="hidden" name="staff_id" value="<?php echo $sid;?>">
<td><?php echo $sid;?></td>
<td><input type="text" name="staff_name" value="<?php echo htmlspecialchars($rw['staff_name']);?>" class="inp-sm" required></td>
<td><input type="text" name="password" value="<?php echo htmlspecialchars($rw['password']);?>" class="inp-sm" required></td>
<td><button type="submit" name="update" value="1" class="btn-sm">Save</button></td>
<td><button type="submit" name="delete" value="1" class="btn-sm danger" <?php echo $isSelf?'disabled title="Cannot delete yourself"':'';?> onclick="return confirm('Delete staff <?php echo htmlspecialchars(addslashes($rw['staff_name']));?>?');">Del</button></td>
</form></tr>
<?php endforeach;?></tbody></table></div></div></div>
<script src="../sidebar.js"></script><script>loadSidebar('manage-employees');</script></body></html>
