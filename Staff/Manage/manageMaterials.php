<?php
session_start();
require_once __DIR__ . '/../../conn.php';
require_once __DIR__ . '/../../auth.php';
requireStaff();
require_once __DIR__ . '/../../sort_utils.php';

$allowed = array('material_id' => 'ID', 'material_name' => 'Name', 'physical_quantity' => 'Qty', 'unit' => 'Unit');
list($sort, $order) = get_sort_params($allowed, 'material_id');
$msg = '';
$mt = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mid = (int)$_POST['material_id'];
    if (isset($_POST['delete'])) {
        $chk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM Furniture_Material WHERE material_id=$mid"))['cnt'];
        if ($chk > 0) {
            $msg = "Cannot delete: used by {$chk} furniture(s).";
            $mt = 'error';
        } else {
            mysqli_query($conn, "DELETE FROM Material WHERE material_id=$mid");
            $msg = 'Material deleted.';
            $mt = 'success';
        }
    } elseif (isset($_POST['update'])) {
        $n = trim($_POST['material_name']);
        $q = (float)$_POST['physical_quantity'];
        $u = trim($_POST['unit']);
        $st = mysqli_prepare($conn, "UPDATE Material SET material_name=?, physical_quantity=?, unit=? WHERE material_id=?");
        mysqli_stmt_bind_param($st, 'sdsi', $n, $q, $u, $mid);
        if (mysqli_stmt_execute($st)) {
            $msg = 'Material updated.';
            $mt = 'success';
        } else {
            $msg = 'Update failed.';
            $mt = 'error';
        }
        mysqli_stmt_close($st);
    }
}

$r = mysqli_query($conn, "SELECT * FROM Material " . sort_clause($sort, $order));
$rs = array();
while ($rw = mysqli_fetch_assoc($r)) {
    $rs[] = $rw;
}
mysqli_free_result($r);

$rc = array();
$rr = mysqli_query($conn, "SELECT material_id, COUNT(*) as cnt FROM Furniture_Material GROUP BY material_id");
while ($rw2 = mysqli_fetch_assoc($rr)) {
    $rc[$rw2['material_id']] = $rw2['cnt'];
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Materials</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .mc{flex:1;display:flex;flex-direction:column;overflow-y:auto;padding:24px 32px}.tw{background:#fff;border-radius:20px;padding:20px;overflow-x:auto;box-shadow:0 1px 3px rgba(0,0,0,.06)}.m{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:500;font-size:.9rem}.me{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}.ms{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}table{width:100%;border-collapse:collapse;table-layout:fixed}th,td{padding:10px 12px;border-bottom:1px solid #e0e0e0;text-align:center}th{font-weight:500;color:#5f6368;font-size:13px}.is{padding:6px 8px;border-radius:6px;border:1px solid #dadce0;font-size:12px;width:90px}.iu{width:60px}.bs{padding:6px 12px;border-radius:16px;font-size:12px;font-weight:500;cursor:pointer;border:none;color:#fff;background:#1a73e8}.bs:hover{background:#1557b0}.bd{background:#d93025}.bd:hover{background:#b3261e}.bs:disabled{background:#9aa0a6;cursor:not-allowed}
    </style>
</head>
<body>
<div id="sidebar-container"></div>
<div class="mc">
    <div class="top-bar"><div class="page-title">Manage Materials</div></div>
    <div class="content-container">
        <?php if ($msg): ?><div class="m m<?php echo $mt[0]; ?>"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th><?php echo sortable_th('material_id', 'ID', $sort, $order); ?></th>
                        <th><?php echo sortable_th('material_name', 'Name', $sort, $order); ?></th>
                        <th><?php echo sortable_th('physical_quantity', 'Qty', $sort, $order); ?></th>
                        <th><?php echo sortable_th('unit', 'Unit', $sort, $order); ?></th>
                        <th>Used</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rs as $rw): $mid = $rw['material_id']; $cnt = isset($rc[$mid]) ? $rc[$mid] : 0; ?>
                        <tr>
                            <td colspan="7" style="padding:0">
                                <form method="post">
                                    <table style="width:100%;table-layout:fixed;border-collapse:collapse">
                                        <tr>
                                            <td><input type="hidden" name="material_id" value="<?php echo $mid; ?>"><?php echo $mid; ?></td>
                                            <td><input type="text" name="material_name" value="<?php echo htmlspecialchars($rw['material_name']); ?>" class="is" required></td>
                                            <td><input type="number" name="physical_quantity" value="<?php echo $rw['physical_quantity']; ?>" step="0.01" class="is iu" required></td>
                                            <td><input type="text" name="unit" value="<?php echo htmlspecialchars($rw['unit']); ?>" class="is iu" required></td>
                                            <td><?php echo $cnt; ?></td>
                                            <td><button type="submit" name="update" value="1" class="bs">Save</button></td>
                                            <td><button type="submit" name="delete" value="1" class="bs bd" <?php echo $cnt > 0 ? 'disabled' : ''; ?> onclick="<?php echo $cnt > 0 ? '' : 'return confirm(\'Delete this material?\');'; ?>">Del</button> <small style="color:<?php echo $cnt > 0 ? '#d93025' : '#5f6368'; ?>">(<?php echo $cnt; ?> furniture)</small></td>
                                        </tr>
                                    </table>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="../sidebar.js"></script><script>loadSidebar('manage-materials');</script>
</body>
</html>
