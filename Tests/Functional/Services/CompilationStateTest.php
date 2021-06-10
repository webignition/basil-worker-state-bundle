<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Functional\Services;

use webignition\BasilWorker\PersistenceBundle\Entity\Callback\CallbackInterface;
use webignition\BasilWorker\StateBundle\Services\CompilationState;
use webignition\BasilWorker\StateBundle\Tests\Functional\AbstractFunctionalTest;
use webignition\BasilWorker\StateBundle\Tests\Model\CallbackConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\EntityConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\JobConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\SourceConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\TestConfiguration;

class CompilationStateTest extends AbstractFunctionalTest
{
    private CompilationState $compilationState;

    protected function setUp(): void
    {
        parent::setUp();

        $compilationState = self::$container->get(CompilationState::class);
        if ($compilationState instanceof CompilationState) {
            $this->compilationState = $compilationState;
        }
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGet(EntityConfiguration $entityConfiguration, string $expectedState): void
    {
        $this->entityCreator->create($entityConfiguration);

        self::assertSame($expectedState, (string) $this->compilationState);
    }

    /**
     * @return array<mixed>
     */
    public function getDataProvider(): array
    {
        return [
            'awaiting: no job' => [
                'entityConfiguration' => new EntityConfiguration(),
                'expectedState' => CompilationState::STATE_AWAITING,
            ],
            'awaiting: has job, no sources' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create()),
                'expectedState' => CompilationState::STATE_AWAITING,
            ],
            'running: has job, has sources, no sources compiled' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::createTest()
                            ->withPath('Test/test1.yml'),
                        SourceConfiguration::createTest()
                            ->withPath('Test/test2.yml'),
                    ]),
                'expectedState' => CompilationState::STATE_RUNNING,
            ],
            'failed: has job, has sources, has more than zero compile-failure callbacks' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::createTest()
                            ->withPath('Test/test1.yml'),
                        SourceConfiguration::createTest()
                            ->withPath('Test/test2.yml'),
                    ])
                    ->withCallbackConfigurations([
                        CallbackConfiguration::create()
                            ->withType(CallbackInterface::TYPE_COMPILATION_FAILED)
                    ]),
                'expectedState' => CompilationState::STATE_FAILED,
            ],
            'complete: has job, has sources, no next source' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::createTest()
                            ->withPath('Test/test1.yml'),
                    ])
                    ->withTestConfigurations([
                        TestConfiguration::create()
                            ->withSource('Test/test1.yml'),
                    ]),
                'expectedState' => CompilationState::STATE_COMPLETE,
            ],
        ];
    }

    /**
     * @dataProvider isDataProvider
     *
     * @param array<CompilationState::STATE_*> $expectedIsStates
     * @param array<CompilationState::STATE_*> $expectedIsNotStates
     */
    public function testIs(
        EntityConfiguration $entityConfiguration,
        array $expectedIsStates,
        array $expectedIsNotStates
    ): void {
        $this->entityCreator->create($entityConfiguration);

        self::assertTrue($this->compilationState->is(...$expectedIsStates));
        self::assertFalse($this->compilationState->is(...$expectedIsNotStates));
    }

    /**
     * @return array<mixed>
     */
    public function isDataProvider(): array
    {
        return [
            'awaiting: no job' => [
                'entityConfiguration' => new EntityConfiguration(),
                'expectedIsStates' => [
                    CompilationState::STATE_AWAITING,
                ],
                'expectedIsNotStates' => [
                    CompilationState::STATE_RUNNING,
                    CompilationState::STATE_FAILED,
                    CompilationState::STATE_COMPLETE,
                    CompilationState::STATE_UNKNOWN,
                ],
            ],
            'awaiting: has job, no sources' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create()),
                'expectedIsStates' => [
                    CompilationState::STATE_AWAITING,
                ],
                'expectedIsNotStates' => [
                    CompilationState::STATE_RUNNING,
                    CompilationState::STATE_FAILED,
                    CompilationState::STATE_COMPLETE,
                    CompilationState::STATE_UNKNOWN,
                ],
            ],
            'running: has job, has sources, no sources compiled' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::createTest()
                            ->withPath('Test/test1.yml'),
                        SourceConfiguration::createTest()
                            ->withPath('Test/test2.yml'),
                    ]),
                'expectedIsStates' => [
                    CompilationState::STATE_RUNNING,
                ],
                'expectedIsNotStates' => [
                    CompilationState::STATE_AWAITING,
                    CompilationState::STATE_FAILED,
                    CompilationState::STATE_COMPLETE,
                    CompilationState::STATE_UNKNOWN,
                ],
            ],
            'failed: has job, has sources, has more than zero compile-failure callbacks' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::createTest()
                            ->withPath('Test/test1.yml'),
                        SourceConfiguration::createTest()
                            ->withPath('Test/test2.yml'),
                    ])
                    ->withCallbackConfigurations([
                        CallbackConfiguration::create()
                            ->withType(CallbackInterface::TYPE_COMPILATION_FAILED)
                    ]),
                'expectedIsStates' => [
                    CompilationState::STATE_FAILED,
                ],
                'expectedIsNotStates' => [
                    CompilationState::STATE_AWAITING,
                    CompilationState::STATE_RUNNING,
                    CompilationState::STATE_COMPLETE,
                    CompilationState::STATE_UNKNOWN,
                ],
            ],
            'complete: has job, has sources, no next source' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::createTest()
                            ->withPath('Test/test1.yml'),
                    ])
                    ->withTestConfigurations([
                        TestConfiguration::create()
                            ->withSource('Test/test1.yml'),
                    ]),
                'expectedIsStates' => [
                    CompilationState::STATE_COMPLETE,
                ],
                'expectedIsNotStates' => [
                    CompilationState::STATE_AWAITING,
                    CompilationState::STATE_RUNNING,
                    CompilationState::STATE_FAILED,
                    CompilationState::STATE_UNKNOWN,
                ],
            ],
        ];
    }
}
