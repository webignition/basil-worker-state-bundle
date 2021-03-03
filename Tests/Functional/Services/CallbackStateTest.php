<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Functional\Services;

use webignition\BasilWorker\PersistenceBundle\Entity\Callback\CallbackEntity;
use webignition\BasilWorker\PersistenceBundle\Entity\Callback\CallbackInterface;
use webignition\BasilWorker\StateBundle\Services\CallbackState;
use webignition\BasilWorker\StateBundle\Tests\Functional\AbstractFunctionalTest;
use webignition\SymfonyTestServiceInjectorTrait\TestClassServicePropertyInjectorTrait;

class CallbackStateTest extends AbstractFunctionalTest
{
    use TestClassServicePropertyInjectorTrait;

    private CallbackState $callbackState;

    protected function setUp(): void
    {
        parent::setUp();

        $this->injectContainerServicesIntoClassProperties();
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param array<CallbackInterface::STATE_*> $callbackStates
     */
    public function testGet(array $callbackStates, string $expectedState): void
    {
        foreach ($callbackStates as $callbackState) {
            $this->createCallbackEntity($callbackState);
        }

        self:self::assertSame($expectedState, $this->callbackState->get());
    }

    /**
     * @return array<mixed>
     */
    public function getDataProvider(): array
    {
        return [
            'no callbacks' => [
                'callbackStates' => [],
                'expectedState' => CallbackState::STATE_AWAITING,
            ],
            'awaiting, sending, queued' => [
                'callbackStates' => [
                    CallbackInterface::STATE_AWAITING,
                    CallbackInterface::STATE_QUEUED,
                    CallbackInterface::STATE_SENDING,
                ],
                'expectedState' => CallbackState::STATE_RUNNING,
            ],
            'awaiting, sending, queued, complete' => [
                'callbackStates' => [
                    CallbackInterface::STATE_AWAITING,
                    CallbackInterface::STATE_QUEUED,
                    CallbackInterface::STATE_SENDING,
                    CallbackInterface::STATE_COMPLETE,
                ],
                'expectedState' => CallbackState::STATE_RUNNING,
            ],
            'awaiting, sending, queued, failed' => [
                'callbackStates' => [
                    CallbackInterface::STATE_AWAITING,
                    CallbackInterface::STATE_QUEUED,
                    CallbackInterface::STATE_SENDING,
                    CallbackInterface::STATE_FAILED,
                ],
                'expectedState' => CallbackState::STATE_RUNNING,
            ],
            'two complete, three failed' => [
                'callbackStates' => [
                    CallbackInterface::STATE_COMPLETE,
                    CallbackInterface::STATE_COMPLETE,
                    CallbackInterface::STATE_FAILED,
                    CallbackInterface::STATE_FAILED,
                    CallbackInterface::STATE_FAILED,
                ],
                'expectedState' => CallbackState::STATE_COMPLETE,
            ],
        ];
    }

    /**
     * @param CallbackEntity::STATE_* $state
     */
    private function createCallbackEntity(string $state): CallbackEntity
    {
        $entity = CallbackEntity::create(CallbackInterface::TYPE_STEP_PASSED, []);
        $entity->setState($state);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }
}
