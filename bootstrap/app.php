<?php

use Respect\Validation\Validator as v;
date_default_timezone_set('Asia/Shanghai');
session_start();

require __DIR__ . '/../vendor/autoload.php';

try {
	$dotenv = (new \Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
	//
}

$app = new \Slim\App([
	'settings' => [
		'displayErrorDetails' => true
	],

]);

require_once __DIR__ . '/database.php';

$container = $app->getContainer();

$container['db'] = function ($container) use ($capsule) {
	return $capsule;
};

$container['log'] = function($container){
	$log = new \Monolog\Logger('logger_info');
	$file_handler = new \Monolog\Handler\StreamHandler('../logs/'.date('Y-m-d') . '.log');
	$log->pushHandler($file_handler);
	return $log;
};
$container['auth'] = function($container) {
	return new \App\Auth\Auth;
};

$container['flash'] = function($container) {
	return new \Slim\Flash\Messages;
};

$container['view'] = function ($container) {
	$view = new \Slim\Views\Twig(__DIR__ . '/../resources/views/', [
		'cache' => false,
	]);

	$view->addExtension(new \Slim\Views\TwigExtension(
		$container->router,
		$container->request->getUri()
	));

	$view->getEnvironment()->addGlobal('auth',[
		'check' => $container->auth->check(),
		'user' => $container->auth->user(),
		'app' => $container,
	]);
	$view->getEnvironment()->addGlobal('slim',[
		'web_root' => getenv('WEB_ROOT'),
		'uriBase' => $container->request->getUri()->getBasePath()
	]);
	$view->getEnvironment()->addGlobal('flash',$container->flash);

	return $view;
};

$container['validator'] = function ($container) {
	return new App\Validation\Validator;
};

$container['HomeController'] = function($container) {
	return new \App\Controllers\HomeController($container);
};
$container['StoreCashierController'] = function($container) {
	return new \App\Controllers\StoreCashierController($container);
};
$container['StoreFontController'] = function($container){
	return new \App\Controllers\StoreFontController($container);
};
$container['AuthController'] = function($container) {
	return new \App\Controllers\Auth\AuthController($container);
};

$container['PasswordController'] = function($container) {
	return new \App\Controllers\Auth\PasswordController($container);
};

$container['csrf'] = function($container) {
	return new \Slim\Csrf\Guard;
};



$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));

// $app->add($container->csrf);

v::with('App\\Validation\\Rules\\');

require __DIR__ . '/../app/routes.php';
