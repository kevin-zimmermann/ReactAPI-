<?php

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($className) {
            include $className . '.php';
//            $classFile = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';
//            if (file_exists($classFile)) {
//
//                require_once $classFile;
//            }
        });
    }
}
Autoloader::register();
