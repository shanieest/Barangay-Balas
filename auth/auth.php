<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function logout() {
    session_unset();
    session_destroy();
    header("Location: signup.php");
    exit();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: signup.php");
    exit();
}

$inactive = 50000; //.minutes
if (isset($_SESSION['last_activity'])) {
    $session_life = time() - $_SESSION['last_activity'];
    if ($session_life > $inactive) {
        logout();
    }
}
$_SESSION['last_activity'] = time();

?>