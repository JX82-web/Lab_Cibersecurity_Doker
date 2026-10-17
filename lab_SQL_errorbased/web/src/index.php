<?php
require_once __DIR__.'/config.php';
//$q=$_GET['q'] ?? '';
$q = (isset($_GET['q']) && $_GET['q'] !== '') ? $_GET['q'] : '0';
$result=$conn->query("SELECT id,title,body FROM reports WHERE id=$q");
if(!$result){http_response_code(500);die($conn->error);}
?><!doctype html><html><body><h1>Report Viewer</h1><form><input name="q"><button>Open</button></form><?php while($r=$result->fetch_assoc()): ?><h2><?=htmlspecialchars($r['title'])?></h2><p><?=htmlspecialchars($r['body'])?></p><?php endwhile; ?></body></html>
