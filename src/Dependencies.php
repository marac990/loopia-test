<?php

define('DOMAIN', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);

$settings = require dirname(__DIR__) . '/config/default.php';
$injector = new \Auryn\Injector;


$injector->alias(\Loopia\App\Api\CredentialsInterface::class, \Loopia\App\Api\Credentials::class);

$injector->alias('Http\Request', 'Http\HttpRequest');
$injector->share('Http\HttpRequest');
$injector->define('Http\Request', [
    ':get' => $_GET,
    ':post' => $_POST,
    ':cookies' => $_COOKIE,
    ':files' => $_FILES,
    ':server' => $_SERVER,
    ':inputStream' => file_get_contents('php://input')
]);

$injector->alias('Http\Response', 'Http\HttpResponse');
$injector->share('Http\HttpResponse');

$injector->alias('Loopia\App\Template\Renderer', 'Loopia\App\Template\MustacheRenderer');
$injector->define('Mustache_Engine', [
    ':options' => [
        'loader' => new Mustache_Loader_FilesystemLoader(dirname(__DIR__) . '/template', [
            'extension' => '.html',
        ])
    ],
]);
$injector->define(\Loopia\App\Api\Credentials::class, [$settings['api.username'], $settings['api.password']]);

$credentials = $injector->make(\Loopia\App\Api\Credentials::class);
$injector->define(\Loopia\App\Api\Client::class, [$credentials, $settings['api.endpoint']]);

return $injector;