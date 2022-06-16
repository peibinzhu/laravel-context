<?php

declare(strict_types=1);

namespace PeibinLaravel\Context\Contracts;

use ArrayObject;
use PeibinLaravel\Context\Exceptions\CoroutineDestroyedException;
use PeibinLaravel\Context\Exceptions\RunningInNonCoroutineException;

interface Coroutine
{
    /**
     * @param callable $callable [required]
     */
    public function __construct(callable $callable);

    /**
     * @param mixed ...$data
     * @return $this
     */
    public function execute(...$data): static;

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param callable $callable [required]
     * @param mixed    ...$data
     * @return $this
     */
    public static function create(callable $callable, ...$data): static;

    /**
     * @return int returns coroutine id from current coroutine, -1 in non coroutine environment
     */
    public static function id(): int;

    /**
     * Returns the parent coroutine ID.
     * Returns 0 when running in the top level coroutine.
     * @throws RunningInNonCoroutineException when running in non-coroutine context
     * @throws CoroutineDestroyedException when the coroutine has been destroyed
     */
    public static function pid(?int $id = null);

    /**
     * Set config to coroutine.
     */
    public static function set(array $config);

    /**
     * @param null|int $id coroutine id
     * @return null|ArrayObject
     */
    public static function getContextFor(?int $id = null): ?ArrayObject;

    /**
     * Execute callback when coroutine destruct.
     */
    public static function defer(callable $callable);
}
