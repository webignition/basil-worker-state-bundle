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

        self::assertSame($expectedState, $this->compilationState->get());
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
                        SourceConfiguration::create()
                            ->withPath('Test/test1.yml'),
                        SourceConfiguration::create()
                            ->withPath('Test/test2.yml'),
                    ]),
                'expectedState' => CompilationState::STATE_RUNNING,
            ],
            'failed: has job, has sources, has more than zero compile-failure callbacks' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::create()
                            ->withPath('Test/test1.yml'),
                        SourceConfiguration::create()
                            ->withPath('Test/test2.yml'),
                    ])
                    ->withCallbackConfigurations([
                        CallbackConfiguration::create()
                            ->withType(CallbackInterface::TYPE_COMPILE_FAILURE)
                    ]),
                'expectedState' => CompilationState::STATE_FAILED,
            ],
            'complete: has job, has sources, no next source' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::create()
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
}
