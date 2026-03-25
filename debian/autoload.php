<?php
/**
 * Autoload aggregator for raiffeisenbank-statement-tools (pkg-php-tools)
 *
 * This file is installed to /usr/share/php/raiffeisenbank-statement-tools/autoload.php
 * and pulls in dependency autoloaders provided by pkg-php-tools, then
 * registers a lightweight PSR-4 loader for local project classes if present.
 */

// Load dependency autoloaders provided by other packages
require_once '/usr/share/php/Ease/autoload.php';
require_once '/usr/share/php/Raiffeisenbank/autoload.php';

// Lightweight PSR-4 loader for project classes under SpojeNet\RaiffeisenBank\
spl_autoload_register(function ($class) {
    $prefix = 'SpojeNet\\RaiffeisenBank\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative = substr($class, $len);
    $file = '/usr/share/raiffeisenbank-statement-tools/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

return true;
