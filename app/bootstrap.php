<?php

session_start();

date_default_timezone_set('Asia/Singapore');

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');
ini_set('log_errors', 'Off');
ini_set('error_log', __DIR__ . '/tmp/logs');

$uri = filter_var(rtrim(filter_input(INPUT_GET, 'url', FILTER_SANITIZE_STRING), '/'), FILTER_SANITIZE_URL);

$router = new Router(explode('/', $uri));
$router->map();

$w = ucWordsByUnderscore($router->getController());

$class = $w . 'Controller';
$cFile = "app/controllers/$class.php";

$method = $router->getAction();
$id = $router->getId();

if (file_exists($cFile)) {
    require $cFile;
    $controller = new $class();
    $controller->{$method}($id);
} else {
    echo json_encode(array('error' => 'not found'));
}