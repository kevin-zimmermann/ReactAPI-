<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite8929ec374b91f1f73fe1bb78454430a
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite8929ec374b91f1f73fe1bb78454430a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite8929ec374b91f1f73fe1bb78454430a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite8929ec374b91f1f73fe1bb78454430a::$classMap;

        }, null, ClassLoader::class);
    }
}
