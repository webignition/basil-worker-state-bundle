<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Services;

use webignition\BasilWorker\PersistenceBundle\Services\Store\CallbackStore;

class CompilationState
{
    public const STATE_AWAITING = 'awaiting';
    public const STATE_RUNNING = 'running';
    public const STATE_FAILED = 'failed';
    public const STATE_COMPLETE = 'complete';
    public const STATE_UNKNOWN = 'unknown';

    public const FINISHED_STATES = [
        self::STATE_COMPLETE,
        self::STATE_FAILED,
    ];

    private CallbackStore $callbackStore;
    private SourcePathFinder $sourcePathFinder;

    public function __construct(CallbackStore $callbackStore, SourcePathFinder $sourcePathFinder)
    {
        $this->callbackStore = $callbackStore;
        $this->sourcePathFinder = $sourcePathFinder;
    }

    /**
     * @param CompilationState::STATE_* ...$states
     *
     * @return bool
     */
    public function is(...$states): bool
    {
        $states = array_filter($states, function ($item) {
            return is_string($item);
        });

        return in_array($this->getCurrentState(), $states);
    }

    /**
     * @return CompilationState::STATE_*
     */
    public function getCurrentState(): string
    {
        if (0 !== $this->callbackStore->getCompileFailureTypeCount()) {
            return CompilationState::STATE_FAILED;
        }

        $compiledSources = $this->sourcePathFinder->findCompiledPaths();
        $nextSource = $this->sourcePathFinder->findNextNonCompiledPath();

        if ([] === $compiledSources) {
            return is_string($nextSource)
                ? CompilationState::STATE_RUNNING
                : CompilationState::STATE_AWAITING;
        }

        return is_string($nextSource)
            ? CompilationState::STATE_RUNNING
            : CompilationState::STATE_COMPLETE;
    }
}
