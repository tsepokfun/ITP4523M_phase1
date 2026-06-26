<?php
session_start();require_once __DIR__.'/../../conn.php';require_once __DIR__.'/../../auth.php';requireCustomer();
$cid=currentUserId();$cn=currentUserName();
$s=mysqli_prepare($conn,"SELECT customer_name,contact_number,address FROM Customer WHERE customer_id=?");
mysqli_stmt_bind_param($s,'i',$cid);mysqli_stmt_execute($s);$r=mysqli_stmt_get_result($s);$c=mysqli_fetch_assoc($r);mysqli_stmt_close($s);mysqli_close($conn);
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Customer Profile</title><link rel="stylesheet" href="../style.css">
<style>.pc{background:#fff;padding:48px;border-radius:24px;box-shadow:0 4px 12px rgba(0,0,0,.05);max-width:600px;margin:0 auto}.ig{display:grid;grid-template-columns:1fr;gap:20px}.ii{background:#f8f9fc;border-radius:16px;padding:20px 24px;border:1px solid #eef2f6;transition:.2s}.ii:hover{background:#f0f4fe;border-color:#c5d9f7}.il{font-size:11px;color:#1a73e8;font-weight:600;margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px;display:block}.iv{font-size:17px;color:#202124;line-height:1.5}.bg{display:flex;justify-content:center;gap:16px;margin-top:32px}.btn{padding:12px 32px;border-radius:40px;font-size:14px;font-weight:500;text-decoration:none;border:none;cursor:pointer;transition:.2s;display:inline-block}.btn-p{background:#1a73e8;color:#fff}.btn-p:hover{background:#1557b0}.btn-s{background:#f1f3f4;color:#202124}.btn-s:hover{background:#e2e6ea}</style></head>
<body><a href="../homepage.php" class="button-back">&#8592;</a><h1>Customer Profile</h1><div class="card-container"><div class="pc"><div class="ig">
<div class="ii"><span class="il">Customer ID</span><span class="iv">#<?php echo $cid;?></span></div>
<div class="ii"><span class="il">Customer Name</span><span class="iv"><?php echo htmlspecialchars(isset($c['customer_name'])?$c['customer_name']:$cn);?></span></div>
<div class="ii"><span class="il">Contact Number</span><span class="iv"><?php echo htmlspecialchars(isset($c['contact_number'])?$c['contact_number']:'-');?></span></div>
<div class="ii"><span class="il">Address</span><span class="iv"><?php echo htmlspecialchars(isset($c['address'])?$c['address']:'-');?></span></div>
<div class="bg"><a href="customerupdate.php" class="btn btn-p">Update Profile</a><a href="../homepage.php" class="btn btn-s">Back to Home</a></div></div></div></body></html>

