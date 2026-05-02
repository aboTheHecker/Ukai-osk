<?php
require_once 'includes/db.php';

$sql = file_get_contents('db/ecommerce.sql');
$conn->multi_query($sql);

echo "Database imported successfuly";

?>