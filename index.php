<?php

define('APPLICATION_PATH', __DIR__);

require_once 'App/FrontController.php';
$app = new App\FrontController();
$app->run();
