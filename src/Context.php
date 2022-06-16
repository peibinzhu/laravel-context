<?php

declare(strict_types=1);

namespace PeibinLaravel\Context;

use ArrayObject;
use Closure;

class Context
{
    protected static $nonCoContext = [];

    public static function set(string $id, $value)
    {
        if (Coroutine::id() > 0) {
            Coroutine::getContextFor()[$id] = $value;
        } else {
            static::$nonCoContext[$id] = $value;
        }
        return $value;
    }

    public static function get(string $id, $default = null, $coroutineId = null)
    {
        if (Coroutine::id() > 0) {
            return Coroutine::getContextFor($coroutineId)[$id] ?? $default;
        }

        return static::$nonCoContext[$id] ?? $default;
    }

    public static function has(string $id, $coroutineId = null): bool
    {
        if (Coroutine::id() > 0) {
            return isset(Coroutine::getContextFor($coroutineId)[$id]);
        }

        return isset(static::$nonCoContext[$id]);
    }

    /**
     * Release the context when you are not in coroutine environment.
     */
    public static function destroy(string $id): void
    {
        unset(static::$nonCoContext[$id]);
    }

    /**
     * Copy the context from a coroutine to current coroutine.
     * This method will delete the origin values in current coroutine.
     */
    public static function copy(int $fromCoroutineId, array $keys = []): void
    {
        $from = Coroutine::getContextFor($fromCoroutineId);
        if ($from === null) {
            return;
        }

        $current = Coroutine::getContextFor();

        if ($keys) {
            $map = array_intersect_key($from->getArrayCopy(), array_flip($keys));
        } else {
            $map = $from->getArrayCopy();
        }

        $current->exchangeArray($map);
    }

    /**
     * Retrieve the value and override it by closure.
     */
    public static function override(string $id, Closure $closure)
    {
        $value = null;
        if (self::has($id)) {
            $value = self::get($id);
        }
        $value = $closure($value);
        self::set($id, $value);
        return $value;
    }

    /**
     * Retrieve the value and store it if not exists.
     * @param string $id
     * @param mixed  $value
     * @return false|mixed|null
     */
    public static function getOrSet(string $id, mixed $value): mixed
    {
        if (!self::has($id)) {
            return self::set($id, value($value));
        }
        return self::get($id);
    }

    public static function getContainer(): ArrayObject|array|null
    {
        if (Coroutine::id() > 0) {
            return Coroutine::getContextFor();
        }

        return static::$nonCoContext;
    }
}
