<?php
session_start();
require_once __DIR__ . '/../../conn.php';
require_once __DIR__ . '/../../auth.php';
requireStaff();

$furnitureId = isset($_GET['fid']) ? (int)$_GET['fid'] : 0;
$msg = '';
$mt = '';

// Fetch furniture info
$furniture = null;
if ($furnitureId > 0) {
    $fResult = mysqli_query($conn, "SELECT furniture_id, furniture_name FROM Furniture WHERE furniture_id = $furnitureId");
    $furniture = mysqli_fetch_assoc($fResult);
    mysqli_free_result($fResult);
}

// Fetch all materials for dropdown
$matResult = mysqli_query($conn, "SELECT material_id, material_name, unit, physical_quantity FROM Material ORDER BY material_name");
$allMaterials = array();
while ($rw = mysqli_fetch_assoc($matResult)) $allMaterials[] = $rw;
mysqli_free_result($matResult);

// Fetch current material assignments
$currentMats = array();
if ($furnitureId > 0) {
    $fmResult = mysqli_query($conn,
        "SELECT fm.material_id, fm.material_quantity, m.material_name, m.unit
         FROM Furniture_Material fm
         JOIN Material m ON fm.material_id = m.material_id
         WHERE fm.furniture_id = $furnitureId
         ORDER BY m.material_name"
    );
    while ($rw = mysqli_fetch_assoc($fmResult)) $currentMats[] = $rw;
    mysqli_free_result($fmResult);
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $furniture) {
    $materialIds = isset($_POST['material_id']) ? $_POST['material_id'] : array();
    $materialQtys = isset($_POST['material_quantity']) ? $_POST['material_quantity'] : array();

    mysqli_begin_transaction($conn);
    try {
        // Delete old assignments
        $s = mysqli_prepare($conn, "DELETE FROM Furniture_Material WHERE furniture_id = ?");
        mysqli_stmt_bind_param($s, 'i', $furnitureId);
        if (!mysqli_stmt_execute($s)) throw new Exception('Failed to clear old materials.');
        mysqli_stmt_close($s);

        // Insert new assignments
        if (is_array($materialIds) && count($materialIds) > 0) {
            foreach ($materialIds as $i => $mid) {
                $mid = (int)$mid;
                $mqty = (float)(isset($materialQtys[$i]) ? $materialQtys[$i] : 0);
                if ($mid > 0 && $mqty > 0) {
                    $s = mysqli_prepare($conn,
                        "INSERT INTO Furniture_Material (furniture_id, material_id, material_quantity) VALUES (?, ?, ?)"
                    );
                    mysqli_stmt_bind_param($s, 'iid', $furnitureId, $mid, $mqty);
                    if (!mysqli_stmt_execute($s)) throw new Exception('Failed to add material.');
                    mysqli_stmt_close($s);
                }
            }
        }

        mysqli_commit($conn);
        $msg = "Materials updated for {$furniture['furniture_name']}.";
        $mt = 'success';

        // Refresh current assignments
        $currentMats = array();
        $fmResult = mysqli_query($conn,
            "SELECT fm.material_id, fm.material_quantity, m.material_name, m.unit
             FROM Furniture_Material fm
             JOIN Material m ON fm.material_id = m.material_id
             WHERE fm.furniture_id = $furnitureId
             ORDER BY m.material_name"
        );
        while ($rw = mysqli_fetch_assoc($fmResult)) $currentMats[] = $rw;
        mysqli_free_result($fmResult);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg = $e->getMessage();
        $mt = 'error';
    }
}

mysqli_close($conn);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Furniture Materials</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .mc { padding: 24px 32px; }
        .fc { background: #fff; border-radius: 20px; padding: 32px; max-width: 800px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .m { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-weight: 500; font-size: .9rem; }
        .me { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .ms { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .info-header { margin-bottom: 24px; }
        .info-header h2 { color: #202124; margin-bottom: 4px; }
        .info-header p { color: #5f6368; font-size: .9rem; }
        .derived-stock-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: 600; font-size: .9rem; background: #e8f0fe; color: #1a73e8; margin-top: 8px; }
        .mr { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
        .mr select { flex: 2; padding: 10px 14px; border: 1px solid #dadce0; border-radius: 8px; font-size: 14px; outline: none; }
        .mr select:focus { border-color: #1a73e8; box-shadow: 0 0 0 2px rgba(26,115,232,.2); }
        .mr input { flex: 1; padding: 10px 14px; border: 1px solid #dadce0; border-radius: 8px; font-size: 14px; outline: none; }
        .mr input:focus { border-color: #1a73e8; box-shadow: 0 0 0 2px rgba(26,115,232,.2); }
        .mr .unit-badge { min-width: 60px; text-align: center; color: #5f6368; font-size: .85rem; font-weight: 500; }
        .mr button { background: #d93025; color: #fff; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 16px; flex-shrink: 0; }
        .br { display: flex; gap: 12px; margin-top: 24px; }
        .btn { padding: 10px 24px; border-radius: 24px; font-size: 14px; font-weight: 500; cursor: pointer; border: none; transition: .2s; }
        .bs { background: #1a73e8; color: #fff; }
        .bs:hover { background: #1557b0; }
        .ba { background: #f8f9fa; color: #1a73e8; border: 1px solid #dadce0; font-size: 13px; }
        .ba:hover { background: #e8f0fe; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px 14px; border-bottom: 1px solid #e0e0e0; text-align: center; }
        th { font-weight: 500; color: #5f6368; font-size: 13px; background: #f8f9fc; }
        .empty-msg { text-align: center; padding: 40px 20px; color: #5f6368; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; color: #1a73e8; text-decoration: none; font-size: .9rem; margin-bottom: 20px; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div id="sidebar-container"></div>
<div class="mc">
    <div class="top-bar">
        <div class="page-title">Manage Furniture Materials</div>
    </div>
    <div style="height: 16px;"></div>

    <a href="manageFurniture.php" class="back-link">&#8592; Back to Manage Furniture</a>

    <?php if ($msg): ?>
        <div class="m <?php echo $mt === 'error' ? 'me' : 'ms'; ?>"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if (!$furniture): ?>
        <div class="fc">
            <div class="empty-msg">Furniture not found. Please select a furniture from the <a href="manageFurniture.php">Manage Furniture</a> page.</div>
        </div>
    <?php else: ?>
        <div class="fc">
            <div class="info-header">
                <h2>🪑 <?php echo htmlspecialchars($furniture['furniture_name']); ?> <span style="color:#5f6368;font-size:.9rem;">(#<?php echo $furniture['furniture_id']; ?>)</span></h2>
                <p>Configure which raw materials and how much of each is needed to produce one unit of this furniture.</p>
                <?php
                // Re-open connection for derived stock display
                $conn2 = mysqli_connect("127.0.0.1", "root", "", "projectDB");
                if ($conn2) {
                    mysqli_set_charset($conn2, "utf8");
                    $derivedStock = getDerivedStock($conn2, $furniture['furniture_id']);
                    echo '<div class="derived-stock-badge">📦 Current Derived Stock: ' . $derivedStock . ' unit(s)</div>';
                    mysqli_close($conn2);
                }
                ?>
            </div>

            <form method="post" id="materialsForm">
                <h3 style="margin: 20px 0 12px; color: #202124;">Current Material Assignments</h3>

                <?php if (empty($currentMats)): ?>
                    <div class="empty-msg" style="background:#fafbfc;border-radius:12px;">
                        No materials assigned yet. Add materials below to define the recipe for this furniture.
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>Unit</th>
                                <th>Quantity per Unit</th>
                                <th>Current Stock</th>
                                <th>Max Units</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($currentMats as $cm):
                                // Fetch current stock for this material
                                $matStock = 0;
                                $smResult = mysqli_query($conn, "SELECT physical_quantity FROM Material WHERE material_id = " . (int)$cm['material_id']);
                                if ($smRow = mysqli_fetch_assoc($smResult)) $matStock = (float)$smRow['physical_quantity'];
                                $maxUnits = $cm['material_quantity'] > 0 ? floor($matStock / $cm['material_quantity']) : 'N/A';
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($cm['material_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($cm['unit']); ?></td>
                                    <td><?php echo number_format($cm['material_quantity'], 2); ?></td>
                                    <td><?php echo number_format($matStock, 2); ?> <?php echo htmlspecialchars($cm['unit']); ?></td>
                                    <td><?php echo $maxUnits; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <h3 style="margin: 24px 0 12px; color: #202124;">Edit Material Assignments</h3>
                <p style="color:#5f6368;font-size:.85rem;margin-bottom:16px;">Modify the materials below and click Save. All previous assignments will be replaced.</p>

                <div id="mc">
                    <?php if (!empty($currentMats)): ?>
                        <?php foreach ($currentMats as $cm): ?>
                            <div class="mr">
                                <select name="material_id[]">
                                    <option value="">-- Select --</option>
                                    <?php foreach ($allMaterials as $am): ?>
                                        <option value="<?php echo $am['material_id']; ?>" <?php echo $am['material_id'] == $cm['material_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($am['material_name'] . ' (' . $am['unit'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="material_quantity[]" step="0.01" min="0.01" placeholder="Qty" value="<?php echo $cm['material_quantity']; ?>">
                                <span class="unit-badge"><?php echo htmlspecialchars($cm['unit']); ?></span>
                                <button type="button" class="rm">&#x2715;</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mr">
                            <select name="material_id[]">
                                <option value="">-- Select --</option>
                                <?php foreach ($allMaterials as $am): ?>
                                    <option value="<?php echo $am['material_id']; ?>">
                                        <?php echo htmlspecialchars($am['material_name'] . ' (' . $am['unit'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="material_quantity[]" step="0.01" min="0.01" placeholder="Qty">
                            <span class="unit-badge"></span>
                            <button type="button" class="rm" style="display:none">&#x2715;</button>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="button" class="btn ba" id="am">+ Add Material</button>

                <div class="br">
                    <button type="submit" class="btn bs">💾 Save Changes</button>
                    <a href="manageFurniture.php" class="btn" style="background:#f1f3f4;color:#202124;text-decoration:none;">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script src="../sidebar.js"></script>
<script>loadSidebar('manage-furniture-materials');</script>
<script>
// Add material row
document.getElementById('am').onclick = function() {
    var container = document.getElementById('mc');
    var template = container.querySelector('.mr').cloneNode(true);
    template.querySelector('select').value = '';
    template.querySelector('input').value = '';
    template.querySelector('.unit-badge').textContent = '';
    var btn = template.querySelector('.rm');
    btn.style.display = 'inline-block';
    btn.onclick = function() {
        template.remove();
        var rows = document.querySelectorAll('.mr');
        if (rows.length === 1) rows[0].querySelector('.rm').style.display = 'none';
    };
    container.appendChild(template);
};

// Initialize remove buttons for existing rows
document.querySelectorAll('.rm').forEach(function(btn) {
    btn.onclick = function() {
        var row = this.closest('.mr');
        row.remove();
        var rows = document.querySelectorAll('.mr');
        if (rows.length === 1) rows[0].querySelector('.rm').style.display = 'none';
    };
});

// Show unit when material is selected
document.getElementById('mc').addEventListener('change', function(e) {
    if (e.target.tagName === 'SELECT') {
        var selectedOption = e.target.options[e.target.selectedIndex];
        var unitBadge = e.target.parentElement.querySelector('.unit-badge');
        if (unitBadge) {
            var text = selectedOption.textContent;
            var match = text.match(/\(([^)]+)\)/);
            unitBadge.textContent = match ? match[1] : '';
        }
    }
});
</script>
</body>
</html>
