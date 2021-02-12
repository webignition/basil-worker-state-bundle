<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Services;

use webignition\BasilWorker\PersistenceBundle\Services\Repository\CallbackRepository;
use webignition\BasilWorker\PersistenceBundle\Services\Store\CallbackStore;

class CallbackState
{
    public const STATE_AWAITING = 'awaiting';
    public const STATE_RUNNING = 'running';
    public const STATE_COMPLETE = 'complete';

    private CallbackStore $callbackStore;
    private CallbackRepository $repository;

    public function __construct(CallbackStore $callbackStore, CallbackRepository $repository)
    {
        $this->callbackStore = $callbackStore;
        $this->repository = $repository;
    }

    /**
     * @return CallbackState::STATE_*
     */
    public function get(): string
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
}
