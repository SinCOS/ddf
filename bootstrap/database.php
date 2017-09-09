<?php
// use Illuminate\Events\Dispatcher;
// use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;


$capsule = new Capsule;

$capsule->addConnection([
    'driver' => getenv('DB_DRIVER'),
    'host' => getenv('DB_HOST'),
    'database' => getenv('DB_DATABASE'),
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'charset' => 'utf8',
    'port' => getenv('DB_PORT'),
    'collation' => 'utf8_unicode_ci',
    'prefix' => 'cc_'
]);
$capsule->setAsGlobal();
// Capsule::setPaginator(function()use($app){
// 	return new App\Library\Paginator('page');
// });

$capsule->bootEloquent();
