<?php
/**
 * conn.php ???�用資�?庫�??設�?
 * 
 * ?�?��?要�?��資�?庫�? PHP ?�面?��??�此檔�??? * ????�數依照?�目規格設�??? */

$hostname = "127.0.0.1";
$database = "projectDB";
$username = "root";
$password = "";

$conn = mysqli_connect($hostname, $username, $password, $database);

// 檢查????�否?��?
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// 設�?字�?編碼??UTF-8
mysqli_set_charset($conn, "utf8");

/**
 * 計算家具的派生庫存量 = 根據原材料庫存可生產的數量
 * 公式：min( floor(Material.physical_quantity / Furniture_Material.material_quantity) )
 * 若家具未配置任何原材料，返回 0
 */
function getDerivedStock($conn, $furniture_id) {
    $fid = (int)$furniture_id;
    $result = mysqli_query($conn,
        "SELECT MIN(FLOOR(m.physical_quantity / fm.material_quantity)) AS derived_stock
         FROM Furniture_Material fm
         JOIN Material m ON fm.material_id = m.material_id
         WHERE fm.furniture_id = $fid"
    );
    if (!$result) return 0;
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    if ($row && $row['derived_stock'] !== null) {
        $stock = (int)$row['derived_stock'];
        return $stock > 0 ? $stock : 0;
    }
    return 0;
}
