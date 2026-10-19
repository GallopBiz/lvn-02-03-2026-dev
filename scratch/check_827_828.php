<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

Config::set('database.connections.dynamic.database', '2025_2026');
DB::purge('dynamic');

$r = DB::connection('dynamic')->table('inquiry_registration')->whereIn('id', [827, 828])->get();
print_r($r);
