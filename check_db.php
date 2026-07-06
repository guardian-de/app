<?php
require 'vendor/autoload.php';
$db = \Config\Database::connect();
$fields = $db->getFieldNames('users');
echo implode(', ', $fields);
