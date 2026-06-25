<?php
/**
 * logout.php ???»å‡º?•ç?
 *
 * æ¸…é™¤?€??session è³‡æ?ä¸¦å??‘ç™»?¥é??¢ã€? */

session_start();

// æ¸…é™¤?€??session è®Šæ•¸
$_SESSION = [];

// ?ªé™¤ session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// ?·æ? session
session_destroy();

// å°Žå??»å…¥?é¢
header('Location: index.php');
exit();
