<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit120b9b47f7ea9772c7fb7734126a258d
{
    public static $files = array (
        '5c2defbf7f7cf93c47ed4965a7eb595e' => __DIR__ . '/..' . '/seregazhuk/pinterest-bot/src/Helpers/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'seregazhuk\\PinterestBot\\' => 24,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'seregazhuk\\PinterestBot\\' => 
        array (
            0 => __DIR__ . '/..' . '/seregazhuk/pinterest-bot/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit120b9b47f7ea9772c7fb7734126a258d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit120b9b47f7ea9772c7fb7734126a258d::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
