<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') header('Location: index.php');
// XXE cred exfil: legge un file di credenziali e lo inietta nel response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search = $_POST['search'];
    $xml = '<?xml version="1.0"?><root><q>'.$search.'</q></root>';
    $dom = new DOMDocument();
    $dom->loadXML($xml, LIBXML_NOENT);
    echo '<pre>'.htmlspecialchars($dom->saveXML()).'</pre>';
}
?>
<h1>Admin Panel</h1>
<form method="post">
    <input name="search" placeholder="Search">
    <button>Search</button>
</form>
<!-- <p>Exfil cred: <!ENTITY xxe SYSTEM "file:///var/www/html/.admin_creds"></p> -->
<a href="dashboard.php">Back</a>
