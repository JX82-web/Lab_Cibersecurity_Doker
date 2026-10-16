<?php
$host = 'db';
$user = 'root';
$pass = 'cybersecurity_pass_2026';
$dbname = 'lab_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die();
}
?>
