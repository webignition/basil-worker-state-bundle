<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Services;

use webignition\BasilWorker\PersistenceBundle\Entity\Test;
use webignition\BasilWorker\PersistenceBundle\Services\Repository\TestRepository;

class ExecutionState
{
    public const STATE_AWAITING = 'awaiting';
    public const STATE_RUNNING = 'running';
    public const STATE_COMPLETE = 'complete';
    public const STATE_CANCELLED = 'cancelled';

    public const FINISHED_STATES = [
        self::STATE_COMPLETE,
        self::STATE_CANCELLED,
    ];

    private TestRepository $testRepository;

    public function __construct(TestRepository $testRepository)
    {
        $this->testRepository = $testRepository;
    }

    /**
     * @return ExecutionState::STATE_*
     */
    public function get(): string
    {
        $hasFailedTests = 0 !== $this->testRepository->count(['state' => Test::STATE_FAILED]);
        $hasCancelledTests = 0 !== $this->testRepository->count(['state' => Test::STATE_CANCELLED]);

        if ($hasFailedTests || $hasCancelledTests) {
            return self::STATE_CANCELLED;
        }

        $hasFinishedTests = 0 !== $this->testRepository->count(['state' => Test::FINISHED_STATES]);
        $hasRunningTests = 0 !== $this->testRepository->count(['state' => Test::STATE_RUNNING]);
        $hasAwaitingTests = 0 !== $this->testRepository->count(['state' => Test::STATE_AWAITING]);

        if ($hasFinishedTests) {
            return $hasAwaitingTests || $hasRunningTests
                ? self::STATE_RUNNING
                : self::STATE_COMPLETE;
        }

        return $hasRunningTests ? self::STATE_RUNNING : self::STATE_AWAITING;
    }
}
