<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit39f9beb53b1373d0867d6c40ad5c979f
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit39f9beb53b1373d0867d6c40ad5c979f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit39f9beb53b1373d0867d6c40ad5c979f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit39f9beb53b1373d0867d6c40ad5c979f::$classMap;

        }, null, ClassLoader::class);
    }
}
