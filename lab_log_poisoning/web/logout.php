<?php
// logout.php - Gestione della chiusura della sessione staff
session_start();
$_SESSION = array();
session_destroy();
header("Location: index.php");
exit;
?>
