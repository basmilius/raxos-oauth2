<?php
declare(strict_types=1);

$local = dirname(__DIR__) . '/vendor/autoload.php';
require is_file($local) ? $local : dirname(__DIR__, 2) . '/vendor/autoload.php';

foreach (glob(__DIR__ . '/Fixtures/*.php') ?: [] as $fixture) {
    require_once $fixture;
}
