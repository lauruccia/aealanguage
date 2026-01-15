<?php
// clear-cache.php (TEMP) — elimina dopo l'uso

ini_set('display_errors', '1');
error_reporting(E_ALL);

// In base al tuo errore, Laravel sembra essere in public_html
$laravelRoot = __DIR__;

// Controlli di sicurezza path
if (!file_exists($laravelRoot . '/bootstrap/app.php')) {
    http_response_code(500);
    echo "<pre>ERRORE: bootstrap/app.php non trovato in {$laravelRoot}</pre>";
    exit;
}

if (!file_exists($laravelRoot . '/vendor/autoload.php')) {
    http_response_code(500);
    echo "<pre>ERRORE: vendor/autoload.php non trovato in {$laravelRoot}</pre>";
    exit;
}

require $laravelRoot . '/vendor/autoload.php';
$app = require $laravelRoot . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$commands = ['cache:clear','config:clear','route:clear','view:clear','optimize:clear'];

echo "<pre>";
foreach ($commands as $cmd) {
    try {
        $kernel->call($cmd);
        echo $cmd . ": OK\n";
    } catch (Throwable $e) {
        echo $cmd . ": ERROR - " . $e->getMessage() . "\n";
    }
}
echo "</pre>";
