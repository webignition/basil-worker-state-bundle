<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Functional\Services;

use webignition\BasilWorker\PersistenceBundle\Entity\Callback\CallbackInterface;
use webignition\BasilWorker\PersistenceBundle\Entity\Test;
use webignition\BasilWorker\StateBundle\Services\ApplicationState;
use webignition\BasilWorker\StateBundle\Tests\Functional\AbstractFunctionalTest;
use webignition\BasilWorker\StateBundle\Tests\Model\CallbackConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\EntityConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\JobConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\SourceConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\TestConfiguration;
use webignition\SymfonyTestServiceInjectorTrait\TestClassServicePropertyInjectorTrait;

class ApplicationStateTest extends AbstractFunctionalTest
{
    use TestClassServicePropertyInjectorTrait;

    private ApplicationState $applicationState;

    protected function setUp(): void
    {
        parent::setUp();
        $this->injectContainerServicesIntoClassProperties();
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testGet(EntityConfiguration $entityConfiguration, string $expectedState): void
    {
        $this->entityCreator->create($entityConfiguration);

        self::assertSame($expectedState, $this->applicationState->get());
    }

    /**
     * @return array<mixed>
     */
    public function getDataProvider(): array
    {
        return [
            'no job, is awaiting' => [
                'entityConfiguration' => new EntityConfiguration(),
                'expectedState' => ApplicationState::STATE_AWAITING_JOB,
            ],
            'has job, no sources' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create()),
                'expectedState' => ApplicationState::STATE_AWAITING_SOURCES,
            ],
            'no sources compiled' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::create()->withPath('Test/test1.yml'),
                        SourceConfiguration::create()->withPath('Test/test2.yml'),
                    ]),
                'expectedState' => ApplicationState::STATE_COMPILING,
            ],
            'first source compiled' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::create()->withPath('Test/test1.yml'),
                        SourceConfiguration::create()->withPath('Test/test2.yml'),
                    ])
                    ->withTestConfigurations([
                        TestConfiguration::create()->withSource('/app/source/Test/test1.yml'),
                    ]),
                'expectedState' => ApplicationState::STATE_COMPILING,
            ],
            'all sources compiled, no tests running' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::create()->withPath('Test/test1.yml'),
                        SourceConfiguration::create()->withPath('Test/test2.yml'),
                    ])
                    ->withTestConfigurations([
                        TestConfiguration::create()->withSource('/app/source/Test/test1.yml'),
                        TestConfiguration::create()->withSource('/app/source/Test/test2.yml'),
                    ]),
                'expectedState' => ApplicationState::STATE_EXECUTING,
            ],
            'first test complete, no callbacks' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::create()->withPath('Test/test1.yml'),
                        SourceConfiguration::create()->withPath('Test/test2.yml'),
                    ])
                    ->withTestConfigurations([
                        TestConfiguration::create()
                            ->withSource('/app/source/Test/test1.yml')
                            ->withState(Test::STATE_COMPLETE),
                        TestConfiguration::create()->withSource('/app/source/Test/test2.yml'),
                    ]),
                'expectedState' => ApplicationState::STATE_EXECUTING,
            ],
            'first test complete, callback for first test complete' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::create()->withPath('Test/test1.yml'),
                        SourceConfiguration::create()->withPath('Test/test2.yml'),
                    ])
                    ->withTestConfigurations([
                        TestConfiguration::create()
                            ->withSource('/app/source/Test/test1.yml')
                            ->withState(Test::STATE_COMPLETE),
                        TestConfiguration::create()->withSource('/app/source/Test/test2.yml'),
                    ])->withCallbackConfigurations([
                        CallbackConfiguration::create()->withState(CallbackInterface::STATE_COMPLETE),
                    ]),
                'expectedState' => ApplicationState::STATE_EXECUTING,
            ],
            'all tests complete, first callback complete, second callback running' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::create()->withPath('Test/test1.yml'),
                        SourceConfiguration::create()->withPath('Test/test2.yml'),
                    ])
                    ->withTestConfigurations([
                        TestConfiguration::create()
                            ->withSource('/app/source/Test/test1.yml')
                            ->withState(Test::STATE_COMPLETE),
                        TestConfiguration::create()
                            ->withSource('/app/source/Test/test2.yml')
                            ->withState(Test::STATE_COMPLETE),
                    ])->withCallbackConfigurations([
                        CallbackConfiguration::create()->withState(CallbackInterface::STATE_COMPLETE),
                        CallbackConfiguration::create()->withState(CallbackInterface::STATE_SENDING),
                    ]),
                'expectedState' => ApplicationState::STATE_COMPLETING_CALLBACKS,
            ],
            'all tests complete, all callbacks complete' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        SourceConfiguration::create()->withPath('Test/test1.yml'),
                        SourceConfiguration::create()->withPath('Test/test2.yml'),
                    ])
                    ->withTestConfigurations([
                        TestConfiguration::create()
                            ->withSource('/app/source/Test/test1.yml')
                            ->withState(Test::STATE_COMPLETE),
                        TestConfiguration::create()
                            ->withSource('/app/source/Test/test2.yml')
                            ->withState(Test::STATE_COMPLETE),
                    ])->withCallbackConfigurations([
                        CallbackConfiguration::create()->withState(CallbackInterface::STATE_COMPLETE),
                        CallbackConfiguration::create()->withState(CallbackInterface::STATE_COMPLETE),
                    ]),
                'expectedState' => ApplicationState::STATE_COMPLETE,
            ],
            'has a job-timeout callback via CallbackInterface::TYPE_JOB_TIMEOUT' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withCallbackConfigurations([
                        CallbackConfiguration::create()
                            ->withType(CallbackInterface::TYPE_JOB_TIME_OUT)
                            ->withState(CallbackInterface::STATE_COMPLETE),
                    ]),
                'expectedState' => ApplicationState::STATE_TIMED_OUT,
            ],
            'has a job-timeout callback via CallbackInterface::TYPE_JOB_TIME_OUT' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withCallbackConfigurations([
                        CallbackConfiguration::create()
                            ->withType(CallbackInterface::TYPE_JOB_TIME_OUT)
                            ->withState(CallbackInterface::STATE_COMPLETE),
                    ]),
                'expectedState' => ApplicationState::STATE_TIMED_OUT,
            ],
        ];
    }
}
