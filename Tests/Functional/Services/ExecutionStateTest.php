<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Functional\Services;

use webignition\BasilWorker\PersistenceBundle\Entity\Test;
use webignition\BasilWorker\StateBundle\Services\ExecutionState;
use webignition\BasilWorker\StateBundle\Tests\Functional\AbstractFunctionalTest;
use webignition\BasilWorker\StateBundle\Tests\Model\EntityConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\TestConfiguration;
use webignition\SymfonyTestServiceInjectorTrait\TestClassServicePropertyInjectorTrait;

class ExecutionStateTest extends AbstractFunctionalTest
{
    use TestClassServicePropertyInjectorTrait;

    private ExecutionState $executionState;

    protected function setUp(): void
    {
        parent::setUp();
        $this->injectContainerServicesIntoClassProperties();
    }

    /**
     * @dataProvider isDataProvider
     *
     * @param array<ExecutionState::STATE_*> $expectedIsStates
     * @param array<ExecutionState::STATE_*> $expectedIsNotStates
     */
    public function testIs(
        EntityConfiguration $entityConfiguration,
        array $expectedIsStates,
        array $expectedIsNotStates
    ): void {
        $this->entityCreator->create($entityConfiguration);

        self::assertTrue($this->executionState->is(...$expectedIsStates));
        self::assertFalse($this->executionState->is(...$expectedIsNotStates));
    }

    /**
     * @return array<mixed>
     */
    public function isDataProvider(): array
    {
        return [
            'awaiting: not has finished tests and not has running tests and not has awaiting tests' => [
                'entityConfiguration' => new EntityConfiguration(),
                'expectedIsStates' => [
                    ExecutionState::STATE_AWAITING,
                ],
                'expectedIsNotStates' => [
                    ExecutionState::STATE_RUNNING,
                    ExecutionState::STATE_COMPLETE,
                    ExecutionState::STATE_CANCELLED,
                ],
            ],
            'running: not has finished tests and has running tests and not has awaiting tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_RUNNING),
                    ]),
                'expectedIsStates' => [
                    ExecutionState::STATE_RUNNING,
                ],
                'expectedIsNotStates' => [
                    ExecutionState::STATE_AWAITING,
                    ExecutionState::STATE_COMPLETE,
                    ExecutionState::STATE_CANCELLED,
                ],
            ],
            'awaiting: not has finished tests and not has running tests and has awaiting tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_AWAITING),
                    ]),
                'expectedIsStates' => [
                    ExecutionState::STATE_AWAITING,
                ],
                'expectedIsNotStates' => [
                    ExecutionState::STATE_RUNNING,
                    ExecutionState::STATE_COMPLETE,
                    ExecutionState::STATE_CANCELLED,
                ],
            ],
            'running: has complete tests and has running tests and not has awaiting tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_COMPLETE),
                        TestConfiguration::create()->withState(Test::STATE_RUNNING),
                    ]),
                'expectedIsStates' => [
                    ExecutionState::STATE_RUNNING,
                ],
                'expectedIsNotStates' => [
                    ExecutionState::STATE_AWAITING,
                    ExecutionState::STATE_COMPLETE,
                    ExecutionState::STATE_CANCELLED,
                ],
            ],
            'running: has complete tests and not has running tests and has awaiting tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_COMPLETE),
                        TestConfiguration::create()->withState(Test::STATE_AWAITING),
                    ]),
                'expectedIsStates' => [
                    ExecutionState::STATE_RUNNING,
                ],
                'expectedIsNotStates' => [
                    ExecutionState::STATE_AWAITING,
                    ExecutionState::STATE_COMPLETE,
                    ExecutionState::STATE_CANCELLED,
                ],
            ],
            'complete: has finished tests and not has running tests and not has awaiting tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_COMPLETE),
                    ]),
                'expectedIsStates' => [
                    ExecutionState::STATE_COMPLETE,
                ],
                'expectedIsNotStates' => [
                    ExecutionState::STATE_AWAITING,
                    ExecutionState::STATE_RUNNING,
                    ExecutionState::STATE_CANCELLED,
                ],
            ],
            'cancelled: has failed tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_FAILED),
                    ]),
                'expectedIsStates' => [
                    ExecutionState::STATE_CANCELLED,
                ],
                'expectedIsNotStates' => [
                    ExecutionState::STATE_AWAITING,
                    ExecutionState::STATE_RUNNING,
                    ExecutionState::STATE_COMPLETE,
                ],
            ],
            'cancelled: has cancelled tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_CANCELLED),
                    ]),
                'expectedIsStates' => [
                    ExecutionState::STATE_CANCELLED,
                ],
                'expectedIsNotStates' => [
                    ExecutionState::STATE_AWAITING,
                    ExecutionState::STATE_RUNNING,
                    ExecutionState::STATE_COMPLETE,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getCurrentStateDataProvider
     */
    public function testGetCurrentState(EntityConfiguration $entityConfiguration, string $expectedCurrentState): void
    {
        $this->entityCreator->create($entityConfiguration);

        self::assertSame($expectedCurrentState, $this->executionState->getCurrentState());
    }

    /**
     * @return array<mixed>
     */
    public function getCurrentStateDataProvider(): array
    {
        return [
            'awaiting: not has finished tests and not has running tests and not has awaiting tests' => [
                'entityConfiguration' => new EntityConfiguration(),
                'expectedCurrentState' => ExecutionState::STATE_AWAITING,
            ],
            'running: not has finished tests and has running tests and not has awaiting tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_RUNNING),
                    ]),
                'expectedCurrentState' => ExecutionState::STATE_RUNNING,
            ],
            'awaiting: not has finished tests and not has running tests and has awaiting tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_AWAITING),
                    ]),
                'expectedCurrentState' => ExecutionState::STATE_AWAITING,
            ],
            'running: has complete tests and has running tests and not has awaiting tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_COMPLETE),
                        TestConfiguration::create()->withState(Test::STATE_RUNNING),
                    ]),
                'expectedCurrentState' => ExecutionState::STATE_RUNNING,
            ],
            'running: has complete tests and not has running tests and has awaiting tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_COMPLETE),
                        TestConfiguration::create()->withState(Test::STATE_AWAITING),
                    ]),
                'expectedCurrentState' => ExecutionState::STATE_RUNNING,
            ],
            'complete: has finished tests and not has running tests and not has awaiting tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_COMPLETE),
                    ]),
                'expectedCurrentState' => ExecutionState::STATE_COMPLETE,
            ],
            'cancelled: has failed tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_FAILED),
                    ]),
                'expectedCurrentState' => ExecutionState::STATE_CANCELLED,
                'expectedIsNotStates' => [
                    ExecutionState::STATE_AWAITING,
                    ExecutionState::STATE_RUNNING,
                    ExecutionState::STATE_COMPLETE,
                ],
            ],
            'cancelled: has cancelled tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withTestConfigurations([
                        TestConfiguration::create()->withState(Test::STATE_CANCELLED),
                    ]),
                'expectedCurrentState' => ExecutionState::STATE_CANCELLED,
            ],
        ];
    }
}
