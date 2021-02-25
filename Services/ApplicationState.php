<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Services;

use webignition\BasilWorker\PersistenceBundle\Services\Store\CallbackStore;
use webignition\BasilWorker\PersistenceBundle\Services\Store\JobStore;
use webignition\BasilWorker\PersistenceBundle\Services\Store\SourceStore;

class ApplicationState
{
    public const STATE_AWAITING_JOB = 'awaiting-job';
    public const STATE_AWAITING_SOURCES = 'awaiting-sources';
    public const STATE_COMPILING = 'compiling';
    public const STATE_EXECUTING = 'executing';
    public const STATE_COMPLETING_CALLBACKS = 'completing-callbacks';
    public const STATE_COMPLETE = 'complete';
    public const STATE_TIMED_OUT = 'timed-out';

    public function __construct(
        private JobStore $jobStore,
        private CompilationState $compilationState,
        private ExecutionState $executionState,
        private CallbackState $callbackState,
        private CallbackStore $callbackStore,
        private SourceStore $sourceStore
    ) {
    }

    /**
     * @return ApplicationState::STATE_*
     */
    public function get(): string
    {
        if (false === $this->jobStore->has()) {
            return self::STATE_AWAITING_JOB;
        }

        if (0 !== $this->callbackStore->getJobTimeoutTypeCount()) {
            return self::STATE_TIMED_OUT;
        }

        if (false === $this->sourceStore->hasAny()) {
            return self::STATE_AWAITING_SOURCES;
        }

        if (false === in_array($this->compilationState->get(), CompilationState::FINISHED_STATES)) {
            return self::STATE_COMPILING;
        }

        if (false === in_array($this->executionState->get(), ExecutionState::FINISHED_STATES)) {
            return self::STATE_EXECUTING;
        }

        if (in_array($this->callbackState->get(), [CallbackState::STATE_AWAITING, CallbackState::STATE_RUNNING])) {
            return self::STATE_COMPLETING_CALLBACKS;
        }

        return self::STATE_COMPLETE;
    }
}
