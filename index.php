<?php
/**
 * index.php - Premium Living Login Page
 * PHP 5.6 compatible
 */
session_start();

// If already logged in, show a notice instead of redirecting
// (allows browser back button to work; protected pages still enforce auth via auth.php)
$alreadyLoggedIn = isset($_SESSION['user']);
if ($alreadyLoggedIn) {
    $currentRole = $_SESSION['user']['role'] === 'staff' ? 'Staff' : 'Customer';
    $currentName = htmlspecialchars($_SESSION['user']['name']);
}

require_once __DIR__ . '/conn.php';

$loginError   = '';
$loginSuccess = '';
$formUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = isset($_POST['action'])   ? $_POST['action']   : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $formUsername = htmlspecialchars($username);

    if ($username === '' || $password === '') {
        $loginError = 'Please enter both username and password.';
    } else {
        if ($action === 'staff_login') {
            $stmt = mysqli_prepare($conn,
                "SELECT staff_id, staff_name FROM Staff WHERE staff_name = ? AND password = ?"
            );
            mysqli_stmt_bind_param($stmt, 'ss', $username, $password);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                $_SESSION['user'] = array(
                    'id'   => (int)$row['staff_id'],
                    'name' => $row['staff_name'],
                    'role' => 'staff',
                );
                header('Location: Staff/dashboard.php');
                exit();
            } else {
                $loginError = 'Invalid staff username or password.';
            }
            mysqli_stmt_close($stmt);

        } elseif ($action === 'customer_login') {
            $stmt = mysqli_prepare($conn,
                "SELECT customer_id, customer_name FROM Customer WHERE customer_name = ? AND password = ?"
            );
            mysqli_stmt_bind_param($stmt, 'ss', $username, $password);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                $_SESSION['user'] = array(
                    'id'   => (int)$row['customer_id'],
                    'name' => $row['customer_name'],
                    'role' => 'customer',
                );
                header('Location: customer/homepage.php');
                exit();
            } else {
                $loginError = 'Invalid customer username or password.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $loginError = 'Invalid login action.';
        }
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Premium Living</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { background-color: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; padding: 16px; }
        .login-card { background: white; padding: 40px 32px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.05); width: 100%; max-width: 420px; text-align: center; transition: all 0.2s ease; }
        h2 { color: #202124; margin-bottom: 8px; font-weight: 500; font-size: 28px; letter-spacing: -0.2px; }
        .subhead { color: #5f6368; font-size: 14px; margin-bottom: 24px; border-bottom: 1px solid #e8eaed; display: inline-block; padding-bottom: 6px; }
        .info-note { background-color: #f8f9fa; border-left: 3px solid #1a73e8; padding: 12px; font-size: 13px; color: #3c4043; border-radius: 6px; margin-bottom: 24px; text-align: left; }
        .info-note small { display: block; line-height: 1.4; }
        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; font-size: 13px; font-weight: 500; color: #5f6368; margin-bottom: 6px; }
        input { width: 100%; padding: 12px 14px; border: 1px solid #dadce0; border-radius: 8px; font-size: 15px; outline: none; transition: all 0.2s; background-color: #fff; }
        input:focus { border-color: #1a73e8; box-shadow: 0 0 0 2px rgba(26,115,232,0.2); }
        .btn-login { background-color: #1a73e8; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s, transform 0.05s; margin-bottom: 12px; }
        .btn-login:hover { background-color: #1557b0; }
        .btn-login:active { transform: scale(0.98); }
        .btn-customer { background-color: #34a853; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .btn-customer:hover { background-color: #2c8e46; }
        .already-note { background-color: #e6f4ea; border-left: 3px solid #34a853; padding: 16px; font-size: 14px; color: #137333; border-radius: 6px; margin-bottom: 24px; text-align: center; }
        .msg-box { color: #d93025; background-color: #fce8e6; border-radius: 6px; padding: 10px 12px; font-size: 13px; margin-bottom: 16px; text-align: center; border-left: 3px solid #d93025; }
        hr { margin: 20px 0 12px; border: none; border-top: 1px solid #e8eaed; }
        .customer-hint { font-size: 12px; color: #5f6368; margin-top: 8px; margin-bottom: 4px; }
        @media (max-width: 480px) { .login-card { padding: 28px 20px; } }
    </style>
</head>
<body>
<div class="login-card">
    <h2>Premium Living</h2>
    <div class="subhead">Secure access portal</div>
    <div class="info-note">
        <small>
            &#128274; <strong>Staff:</strong> Use staff username &amp; password to access admin panel.<br>
            &#127968; <strong>Customer:</strong> Use customer username &amp; password to access customer panel.
        </small>
    </div>

    <?php if ($alreadyLoggedIn): ?>
        <div class="already-note">
            &#x2705; Already signed in as <strong><?php echo $currentName; ?></strong> (<?php echo $currentRole; ?>)
            <br><br>
            <a class="btn-login" style="display:inline-block;text-decoration:none;width:auto;padding:10px 24px;" href="<?php echo ($_SESSION['user']['role']==='staff') ? 'Staff/dashboard.php' : 'customer/homepage.php'; ?>">Go to Dashboard</a>
            <a class="btn-login btn-customer" style="display:inline-block;text-decoration:none;width:auto;padding:10px 24px;margin-left:8px;" href="logout.php">Sign Out</a>
        </div>
    <?php endif; ?>

    <?php if ($loginError !== ''): ?>
        <div class="msg-box"><?php echo htmlspecialchars($loginError); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php" id="loginForm">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" value="<?php echo $formUsername; ?>" autocomplete="off" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" autocomplete="off" required>
        </div>
        <button type="submit" name="action" value="staff_login" class="btn-login">Log in to Admin Panel</button>
        <div class="customer-hint">&#128071; Customer access &#128071;</div>
        <button type="submit" name="action" value="customer_login" class="btn-login btn-customer">Get in to the Customer Panel</button>
    </form>
</div>
<script>
(function() {
    var form = document.getElementById('loginForm');
    form.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); form.querySelector('button[value="staff_login"]').click(); }
    });
})();
</script>
</body>
</html>
