<?php session_start();require_once __DIR__."/../../conn.php";require_once __DIR__."/../../auth.php";requireStaff();$msg="";$mt="";
$mr=mysqli_query($conn,"SELECT material_id,material_name,unit FROM Material ORDER BY material_name");$mats=array();while($rw=mysqli_fetch_assoc($mr))$mats[]=$rw;mysqli_free_result($mr);
if($_SERVER["REQUEST_METHOD"]==="POST"){$nm=trim(isset($_POST["furniture_name"])?$_POST["furniture_name"]:"");$dc=trim(isset($_POST["description"])?$_POST["description"]:"");$pr=(float)(isset($_POST["price"])?$_POST["price"]:0);$sq=(int)(isset($_POST["stock_quantity"])?$_POST["stock_quantity"]:0);$mi=isset($_POST["material_id"])?$_POST["material_id"]:array();$mq=isset($_POST["material_quantity"])?$_POST["material_quantity"]:array();

// Handle image file upload -> store in /image/ folder
$imageExt = null; $imageTmp = null;
if (isset($_FILES["image_file"]) && $_FILES["image_file"]["error"] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES["image_file"]["name"], PATHINFO_EXTENSION));
    $allowed = array("jpg", "jpeg", "png", "gif", "webp");
    if (!in_array($ext, $allowed)) {
        $msg = "Only JPG, PNG, GIF, WEBP images allowed."; $mt = "error";
    } elseif ($_FILES["image_file"]["size"] > 5 * 1024 * 1024) {
        $msg = "Image must be under 5MB."; $mt = "error";
    } else {
        $imageExt = $ext;
        $imageTmp = $_FILES["image_file"]["tmp_name"];
    }
}

if($msg!==""){/* skip insert */}else
if($nm===""||$pr<=0){$msg="Name and valid price required.";$mt="error";}else{mysqli_begin_transaction($conn);try{
// Insert without image column (image stored in /image/ folder)
$s=mysqli_prepare($conn,"INSERT INTO Furniture(furniture_name,description,price,stock_quantity) VALUES(?,?,?,?)");
mysqli_stmt_bind_param($s,"ssdi",$nm,$dc,$pr,$sq);
if(!mysqli_stmt_execute($s))throw new Exception("Insert failed");
$nf=mysqli_insert_id($conn);mysqli_stmt_close($s);
if(is_array($mi)&&count($mi)>0){foreach($mi as $i=>$mid){$mid=(int)$mid;$mqty=(float)(isset($mq[$i])?$mq[$i]:0);if($mid>0&&$mqty>0){$s=mysqli_prepare($conn,"INSERT INTO Furniture_Material(furniture_id,material_id,material_quantity) VALUES(?,?,?)");mysqli_stmt_bind_param($s,"iid",$nf,$mid,$mqty);if(!mysqli_stmt_execute($s))throw new Exception("Material link failed");mysqli_stmt_close($s);}}}
mysqli_commit($conn);
// Save uploaded image to /image/ folder
if ($imageTmp !== null && $imageExt !== null) {
    $destPath = __DIR__ . '/../../image/' . $nf . '.' . $imageExt;
    foreach (array('png','jpg','jpeg','gif','webp') as $oldExt) {
        $oldFile = __DIR__ . '/../../image/' . $nf . '.' . $oldExt;
        if (file_exists($oldFile)) unlink($oldFile);
    }
    move_uploaded_file($imageTmp, $destPath);
}
$msg="Furniture created (ID: $nf).";$mt="success";}catch(Exception $e){mysqli_rollback($conn);$msg=$e->getMessage();$mt="error";}}}mysqli_close($conn);
?><!DOCTYPE html><html><head><meta charset="UTF-8"><title>Create Furniture</title><link rel="stylesheet" href="../style.css"><style>.pw{flex:1;padding:24px 32px;overflow-y:auto}.fc{background:#fff;border-radius:20px;padding:32px;max-width:800px;box-shadow:0 1px 3px rgba(0,0,0,.06)}.m{padding:12px 16px;border-radius:10px;margin-bottom:20px;font-weight:500;font-size:.9rem}.me{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}.ms{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}.fr{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}.ff{grid-template-columns:1fr}label{display:block;font-size:13px;font-weight:500;color:#5f6368;margin-bottom:5px}input,textarea,select{width:100%;padding:10px 14px;border:1px solid #dadce0;border-radius:8px;font-size:14px;outline:none;font-family:inherit}input:focus,select:focus,textarea:focus{border-color:#1a73e8;box-shadow:0 0 0 2px rgba(26,115,232,.2)}textarea{resize:vertical;min-height:80px}.mr{display:flex;gap:10px;align-items:center;margin-bottom:10px}.mr select{flex:2}.mr input{flex:1}.mr button{background:#d93025;color:#fff;border:none;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:16px;flex-shrink:0}.br{display:flex;gap:12px;margin-top:24px}.btn{padding:10px 24px;border-radius:24px;font-size:14px;font-weight:500;cursor:pointer;border:none;transition:.2s}.bs{background:#1a73e8;color:#fff}.bs:hover{background:#1557b0}.ba{background:#f8f9fa;color:#1a73e8;border:1px solid #dadce0;font-size:13px}.ba:hover{background:#e8f0fe}</style></head>
<body><div id="sidebar-container"></div><div class="pw"><div class="top-bar"><div class="page-title">Create Furniture</div></div><div style="height:24px"></div><?php if($msg):?><div class="m <?php echo $mt==="error"?"me":"ms";?>"><?php echo htmlspecialchars($msg);?></div><?php endif;?>
<div class="fc"><form method="post" id="ff" enctype="multipart/form-data"><div class="fr"><div><label>Furniture Name *</label><input type="text" name="furniture_name" required></div><div><label>Upload Image (JPG/PNG)</label><input type="file" name="image_file" accept="image/jpeg,image/png,image/gif,image/webp" style="padding:8px"></div></div>
<div class="fr"><div><label>Price ($) *</label><input type="number" name="price" step="0.01" min="0.01" required></div><div><label>Stock Quantity</label><input type="number" name="stock_quantity" min="0" value="0"></div></div>
<div class="fr ff"><div><label>Description</label><textarea name="description" placeholder="Describe..."></textarea></div></div>
<h3 style="margin:20px 0 12px;color:#202124;">Materials Used</h3>
<div id="mc"><div class="mr"><select name="material_id[]"><option value="">-- Select --</option><?php foreach($mats as $m):?><option value="<?php echo $m["material_id"];?>"><?php echo htmlspecialchars($m["material_name"]." (".$m["unit"].")");?></option><?php endforeach;?></select><input type="number" name="material_quantity[]" step="0.01" min="0.01" placeholder="Qty"><button type="button" class="rm" style="display:none">x</button></div></div>
<button type="button" class="btn ba" id="am">+ Add Material</button>
<div class="br"><button type="submit" class="btn bs">Create Furniture</button><a href="../dashboard.php" class="btn" style="background:#f1f3f4;color:#202124;text-decoration:none;">Cancel</a></div></form></div></div>
<script src="../sidebar.js"></script><script>loadSidebar("create-furniture");</script>
<script>document.getElementById("am").onclick=function(){var c=document.getElementById("mc");var t=c.querySelector(".mr").cloneNode(true);t.querySelector("select").value="";t.querySelector("input").value="";var b=t.querySelector(".rm");b.style.display="inline-block";b.onclick=function(){t.remove();var rs=document.querySelectorAll(".mr");if(rs.length===1)rs[0].querySelector(".rm").style.display="none";};c.appendChild(t);};</script></body></html>