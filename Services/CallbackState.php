<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Services;

use webignition\BasilWorker\PersistenceBundle\Services\Repository\CallbackRepository;
use webignition\BasilWorker\PersistenceBundle\Services\Store\CallbackStore;

class CallbackState implements \Stringable
{
    public const STATE_AWAITING = 'awaiting';
    public const STATE_RUNNING = 'running';
    public const STATE_COMPLETE = 'complete';

    public function __construct(
        private CallbackStore $callbackStore,
        private CallbackRepository $repository
    ) {
    }

    /**
     * @return self::STATE_*
     */
    public function __toString(): string
    {
        $callbackCount = $this->repository->count([]);
        $finishedCallbackCount = $this->callbackStore->getFinishedCount();

        if (0 === $callbackCount) {
            return self::STATE_AWAITING;
        }

        return $finishedCallbackCount === $callbackCount
            ? self::STATE_COMPLETE
            : self::STATE_RUNNING;
    }

    /**
     * @param CallbackState::STATE_* ...$states
     */
    public function is(...$states): bool
    {
        $states = array_filter($states, function ($item) {
            return is_string($item);
        });

        return in_array((string) $this, $states);
    }
}
