<?php

$loader = require_once __DIR__ . '/../vendor/autoload.php';

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$settings = require(__DIR__."/../config/default.php");
return new Loopia\App\Application($settings);


