<?php

/*
|--------------------------------------------------------------------------
| Behat Bootstrap File
|--------------------------------------------------------------------------
|
| This file bootstraps the Laravel application so that Behat can use
| Laravel's facades and helpers.
|
*/

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

