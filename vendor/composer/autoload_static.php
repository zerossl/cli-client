<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5ef229ad54970f519a4ee026ca81d5fe
{
    public static $prefixLengthsPsr4 = array (
        'Z' => 
        array (
            'ZeroSSL\\CliClient\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ZeroSSL\\CliClient\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5ef229ad54970f519a4ee026ca81d5fe::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5ef229ad54970f519a4ee026ca81d5fe::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5ef229ad54970f519a4ee026ca81d5fe::$classMap;

        }, null, ClassLoader::class);
    }
}
