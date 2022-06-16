<?php

declare(strict_types=1);

namespace PeibinLaravel\Context;

use ArrayObject;
use PeibinLaravel\Context\Contracts\Coroutine as CoroutineContract;
use PeibinLaravel\Context\Exceptions\CoroutineDestroyedException;
use PeibinLaravel\Context\Exceptions\RunningInNonCoroutineException;
use PeibinLaravel\Context\Exceptions\RuntimeException;
use Swoole\Coroutine as SwooleCo;

class Coroutine implements CoroutineContract
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * @var int
     */
    private $id;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public static function create(callable $callable, ...$data): static
    {
        $coroutine = new static($callable);
        $coroutine->execute(...$data);
        return $coroutine;
    }

    public function execute(...$data): static
    {
        $this->id = SwooleCo::create($this->callable, ...$data);
        return $this;
    }

    public function getId(): int
    {
        if (is_null($this->id)) {
            throw new RuntimeException('Coroutine was not be executed.');
        }
        return $this->id;
    }

    public static function id(): int
    {
        return SwooleCo::getCid();
    }

    public static function pid(?int $id = null)
    {
        if ($id) {
            $cid = SwooleCo::getPcid($id);
            if ($cid === false) {
                throw new CoroutineDestroyedException(sprintf('Coroutine #%d has been destroyed.', $id));
            }
        } else {
            $cid = SwooleCo::getPcid();
        }
        if ($cid === false) {
            throw new RunningInNonCoroutineException('Non-Coroutine environment don\'t has parent coroutine id.');
        }
        return max(0, $cid);
    }

    public static function set(array $config)
    {
        SwooleCo::set($config);
    }

    /**
     *
     * @param int|null $id
     * @return ArrayObject|null
     */
    public static function getContextFor(?int $id = null): ?ArrayObject
    {
        if ($id === null) {
            return SwooleCo::getContext();
        }

        return SwooleCo::getContext($id);
    }

    public static function defer(callable $callable)
    {
        SwooleCo::defer($callable);
    }
}
