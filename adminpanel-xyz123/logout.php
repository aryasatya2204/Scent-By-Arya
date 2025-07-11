<?php
// /adminpanel-xyz123/logout.php
session_start();
session_unset();
session_destroy();
// Arahkan ke halaman login admin setelah logout
header("Location: login.php");
exit;
?>