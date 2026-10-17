<?php
$conn=new mysqli('sqli_db','web_user','WebDB_Error_2026!','errorlab');
if($conn->connect_errno){http_response_code(500);die('Database connection failed.');}
