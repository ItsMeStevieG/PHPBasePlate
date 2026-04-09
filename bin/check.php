<?php

declare(strict_types=1);

/**
 * PHPBasePlate V3 - Environment Check
 *
 * Usage: php bin/check.php
 */

echo "PHPBasePlate V3 - Environment Check\n";
echo str_repeat('=', 40) . "\n\n";

$pass = 0;
$fail = 0;

function check(string $label, bool $result, string $detail = ''): void
{
    global $pass, $fail;
    $icon = $result ? "\033[32m✓\033[0m" : "\033[31m✗\033[0m";
    echo "  {$icon} {$label}";
    if ($detail !== '') {
        echo " ({$detail})";
    }
    echo "\n";
    $result ? $pass++ : $fail++;
}

// PHP Version
$phpVersion = PHP_VERSION;
check('PHP >= 8.3', version_compare($phpVersion, '8.3.0', '>='), $phpVersion);

// Required extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'fileinfo', 'openssl', 'session'];
foreach ($requiredExtensions as $ext) {
    check("ext-{$ext}", extension_loaded($ext));
}

// Paths
$basePath = dirname(__DIR__);

echo "\nPaths:\n";
check('vendor/ exists', is_dir($basePath . '/vendor'));
check('public/ exists', is_dir($basePath . '/public'));
check('config/ exists', is_dir($basePath . '/config'));
check('resources/schemas/ exists', is_dir($basePath . '/resources/schemas'));
check('resources/views/ exists', is_dir($basePath . '/resources/views'));

// Writable paths
echo "\nWritable directories:\n";
$writablePaths = ['storage/cache', 'storage/logs', 'storage/sessions', 'storage/temp', 'public/uploads'];
foreach ($writablePaths as $path) {
    $full = $basePath . '/' . $path;
    $writable = is_dir($full) && is_writable($full);
    check($path, $writable, $writable ? 'writable' : 'not writable');
}

// Environment file
echo "\nConfiguration:\n";
check('.env file exists', file_exists($basePath . '/.env'));
check('.env.example exists', file_exists($basePath . '/.env.example'));
check('composer.json exists', file_exists($basePath . '/composer.json'));

// Schema files
echo "\nSchemas:\n";
$schemas = glob($basePath . '/resources/schemas/*.json');
check('Schema files found', count($schemas) > 0, count($schemas) . ' files');
foreach ($schemas as $schema) {
    $content = json_decode(file_get_contents($schema), true);
    $valid = $content !== null && isset($content['name']);
    check('  ' . basename($schema), $valid, $valid ? $content['name'] : 'invalid JSON');
}

// Summary
echo "\n" . str_repeat('=', 40) . "\n";
echo "Results: \033[32m{$pass} passed\033[0m, \033[31m{$fail} failed\033[0m\n";

if ($fail > 0) {
    echo "\n\033[33mPlease fix the issues above before proceeding.\033[0m\n";
    exit(1);
}

echo "\n\033[32mEnvironment is ready!\033[0m\n";
exit(0);
