<?php
session_start();require_once __DIR__.'/../../conn.php';require_once __DIR__.'/../../auth.php';requireCustomer();
$cid=currentUserId();$msg='';$mt='';
$s=mysqli_prepare($conn,"SELECT contact_number,address FROM Customer WHERE customer_id=?");mysqli_stmt_bind_param($s,'i',$cid);mysqli_stmt_execute($s);$r=mysqli_stmt_get_result($s);$c=mysqli_fetch_assoc($r);mysqli_stmt_close($s);
if($_SERVER['REQUEST_METHOD']==='POST'){
    $op=isset($_POST['old_password'])?$_POST['old_password']:'';$np=isset($_POST['new_password'])?$_POST['new_password']:'';
    $ph=trim(isset($_POST['contact_number'])?$_POST['contact_number']:'');$ad=trim(isset($_POST['address'])?$_POST['address']:'');
    $s=mysqli_prepare($conn,"SELECT password FROM Customer WHERE customer_id=?");mysqli_stmt_bind_param($s,'i',$cid);mysqli_stmt_execute($s);$pr=mysqli_stmt_get_result($s);$pw=mysqli_fetch_assoc($pr);mysqli_stmt_close($s);
    if($op!==''&&$op!==$pw['password']){$msg='Old password incorrect.';$mt='error';}
    elseif($ph===''&&$ad===''&&$np===''){$msg='Fill at least one field.';$mt='error';}
    else{$up=array();$pa=array();$tp='';
        if($np!==''){$up[]='password=?';$pa[]=$np;$tp.='s';}
        if($ph!==''){$up[]='contact_number=?';$pa[]=$ph;$tp.='s';$c['contact_number']=$ph;}
        if($ad!==''){$up[]='address=?';$pa[]=$ad;$tp.='s';$c['address']=$ad;}
        if(!empty($up)){$np2=mysqli_real_escape_string($conn,$np);$ph2=mysqli_real_escape_string($conn,$ph);$ad2=mysqli_real_escape_string($conn,$ad);
            $sets=array();
            if($np!=='')$sets[]="password='$np2'";
            if($ph!=='')$sets[]="contact_number='$ph2'";
            if($ad!=='')$sets[]="address='$ad2'";
            $sql="UPDATE Customer SET ".implode(', ',$sets)." WHERE customer_id=$cid";
            if(mysqli_query($conn,$sql)){$msg='Profile updated!';$mt='success';}else{$msg='Update failed.';$mt='error';}
        }
    }
}
mysqli_close($conn);
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Update Profile</title><link rel="stylesheet" href="../style.css">
<style>.fc{background:#fff;padding:40px;border-radius:24px;box-shadow:0 4px 12px rgba(0,0,0,.05);max-width:600px;margin:0 auto}.m{padding:14px;border-radius:12px;margin-bottom:20px;font-size:.9rem;font-weight:500}.m.e{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}.m.s{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}.fg{margin-bottom:20px}.fg label{display:block;font-size:13px;font-weight:500;color:#5f6368;margin-bottom:6px}.fg input{width:100%;padding:12px 14px;border:1px solid #dadce0;border-radius:8px;font-size:15px;outline:none}.fg input:focus{border-color:#1a73e8;box-shadow:0 0 0 2px rgba(26,115,232,.2)}.bg{display:flex;gap:16px;margin-top:28px;justify-content:center}.btn{padding:10px 28px;border-radius:40px;font-size:14px;font-weight:500;cursor:pointer;border:none;transition:.2s;text-decoration:none;display:inline-block}.btn-s{background:#1a73e8;color:#fff}.btn-s:hover{background:#1557b0}.btn-r{background:#fff;color:#5f6368;border:1px solid #dadce0}.btn-r:hover{background:#f1f3f4}.note{font-size:12px;color:#5f6368;margin-top:4px}</style></head>
<body><a href="customer.php" class="button-back">&#8592;</a><h1>Update Customer Profile</h1><div class="fc">
<?php if($msg):?><div class="m <?php echo $mt;?>"><?php echo htmlspecialchars($msg);?></div><?php endif;?>
<form method="post"><div class="fg"><label>Old Password *</label><input type="password" name="old_password" placeholder="Enter current password" required><div class="note">Required to verify your identity.</div></div>
<div class="fg"><label>New Password</label><input type="password" name="new_password" placeholder="Leave blank to keep current"><div class="note">Only fill if changing password.</div></div>
<div class="fg"><label>Contact Number</label><input type="text" name="contact_number" value="<?php echo htmlspecialchars(isset($c['contact_number'])?$c['contact_number']:'');?>" placeholder="Enter new contact number"></div>
<div class="fg"><label>Address</label><input type="text" name="address" value="<?php echo htmlspecialchars(isset($c['address'])?$c['address']:'');?>" placeholder="Enter new address"></div>
<div class="bg"><button type="reset" class="btn btn-r">Reset</button><button type="submit" class="btn btn-s">Save Changes</button></div></form></div></body></html>
