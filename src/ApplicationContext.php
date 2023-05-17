<?php

declare(strict_types=1);

namespace PeibinLaravel\Context;

use Illuminate\Contracts\Container\Container;

class ApplicationContext
{
    protected static ?Container $container = null;

    public static function getContainer(): Container
    {
        return self::$container;
    }

    public static function hasContainer(): bool
    {
        return isset(self::$container);
    }

    public static function setContainer(Container $container): Container
    {
        self::$container = $container;
        return $container;
    }
}
