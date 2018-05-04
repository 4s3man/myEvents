<?php

ini_set('display_errors', 0);

require_once __DIR__.'/../vendor/autoload.php';

umask(0002);

$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/prod.php';
require __DIR__.'/../src/controllers.php';
$app->run();
