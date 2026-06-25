<?php
session_start();require_once __DIR__.'/../../conn.php';require_once __DIR__.'/../../auth.php';requireCustomer();
$cid=currentUserId();$cn=currentUserName();
$s=mysqli_prepare($conn,"SELECT customer_name,contact_number,address FROM Customer WHERE customer_id=?");
mysqli_stmt_bind_param($s,'i',$cid);mysqli_stmt_execute($s);$r=mysqli_stmt_get_result($s);$c=mysqli_fetch_assoc($r);mysqli_stmt_close($s);mysqli_close($conn);
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Customer Profile</title><link rel="stylesheet" href="../style.css">
<style>.pc{background:#fff;padding:40px;border-radius:24px;box-shadow:0 4px 12px rgba(0,0,0,.05)}.ig{display:grid;grid-template-columns:1fr 1fr;gap:24px}.ii{border-bottom:1px solid #f1f3f4;padding-bottom:12px}.il{font-size:12px;color:#1a73e8;font-weight:500;margin-bottom:4px;text-transform:uppercase}.iv{font-size:16px;color:#202124;min-height:22px}.bg{grid-column:1/span 2;display:flex;justify-content:center;gap:16px;margin-top:28px}.btn{padding:10px 28px;border-radius:40px;font-size:14px;font-weight:500;text-decoration:none;border:none;cursor:pointer;transition:.2s}.btn-p{background:#1a73e8;color:#fff}.btn-p:hover{background:#1557b0}@media(max-width:600px){.ig{grid-template-columns:1fr}.bg{grid-column:1}}</style></head>
<body><a href="../homepage.php" class="button-back">&#8592;</a><h1>Customer Profile</h1><div class="card-container pc"><div class="ig">
<div class="ii"><span class="il">Customer ID</span><span class="iv">#<?php echo $cid;?></span></div>
<div class="ii"><span class="il">Customer Name</span><span class="iv"><?php echo htmlspecialchars(isset($c['customer_name'])?$c['customer_name']:$cn);?></span></div>
<div class="ii"><span class="il">Contact Number</span><span class="iv"><?php echo htmlspecialchars(isset($c['contact_number'])?$c['contact_number']:'-');?></span></div>
<div class="ii"><span class="il">Address</span><span class="iv"><?php echo htmlspecialchars(isset($c['address'])?$c['address']:'-');?></span></div>
<div class="bg"><a href="customerupdate.php" class="btn btn-p">Update Profile</a><a href="../homepage.php" class="btn" style="background:#f1f3f4;color:#202124;">Back to Home</a></div></div></div></body></html>
