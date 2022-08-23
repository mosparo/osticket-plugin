<?php

spl_autoload_register(function ($class) {
    $prefix = 'MosparoOsTicket\\';
    $baseDir = __DIR__ . '/MosparoOsTicket/';

    $length = strlen($prefix);
    if (strncmp($prefix, $class, $length) !== 0) {
        return;
    }

    $relativeClass = substr($class, $length);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once($file);
    }
});