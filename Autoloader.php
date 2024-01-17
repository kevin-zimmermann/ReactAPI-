<?php

class Autoloader {
    public static function register() {
        spl_autoload_register([__CLASS__, 'load']);
    }

    public static function load($className) {
        // Convert namespace separators to directory separators
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);

        // Include the class file
        require_once __DIR__ . '/class/'  . $className . '.php';
    }
}

// Register the autoloader
Autoloader::register();
