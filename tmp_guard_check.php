<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$guard = Illuminate\Support\Facades\Auth::guard('sanctum');
echo get_class($guard) . PHP_EOL;
