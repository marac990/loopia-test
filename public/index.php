<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
//phpinfo();
$app = require_once __DIR__ . '/../src/application.php';
$app->run();