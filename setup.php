<?php
require_once 'includes/db.php';

$sql = file_get_contents('db/ecommerce.sql');

$sql = preg_replace('/USE\s+\w+;/i', '', $sql);
$sql = preg_replace('/CREATE\s+DATABASE[^;]+;/i', '', $sql);

$conn->multi_query($sql);

echo "Database imported successfully!";
?>