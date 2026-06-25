<?php
/**
 * conn.php ???▒чФиш│Зц?х║лщА??шинх?
 * 
 * ?А?Йщ?шжБщА?Оеш│Зц?х║лч? PHP ?БщЭв?Жх??ецндцкФц??? * ????ГцХ╕ф╛ЭчЕз?ЕчЫошжПца╝шинх??? */

$hostname = "127.0.0.1";
$database = "projectDB";
$username = "root";
$password = "";

$conn = mysqli_connect($hostname, $username, $password, $database);

// цквцЯе????пхРж?Рх?
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// шинх?хнЧх?ч╖ичв╝??UTF-8
mysqli_set_charset($conn, "utf8");
