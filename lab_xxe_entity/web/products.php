<?php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: index.php');
// XXE VULNERABILITY: LIBXML_NOENT abilita il parsing delle entity esterne
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xml'])) {
    $xml = $_POST['xml'];
    $dom = new DOMDocument();
    $dom->loadXML($xml, LIBXML_NOENT); // <-- Punto debole meccanico
    echo '<pre>'.htmlspecialchars($dom->saveXML()).'</pre>';
}
?>

<h1>Products</h1>
<h2>Contact to administrator with document xml</h2>
	<form method="post">
		<textarea name="xml" rows="9" cols="80"></textarea>
		<button>Send</button>
	</form>
<p>Try:</p>
<a href="dashboard.php">Back</a>
