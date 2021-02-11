<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Functional\Services;

use webignition\BasilWorker\PersistenceBundle\Entity\Callback\CallbackEntity;
use webignition\BasilWorker\PersistenceBundle\Entity\Callback\CallbackInterface;
use webignition\BasilWorker\StateBundle\Services\CallbackState;
use webignition\BasilWorker\StateBundle\Tests\Functional\AbstractFunctionalTest;

class CallbackStateTest extends AbstractFunctionalTest
{
    private CallbackState $callbackState;

    protected function setUp(): void
    {
        parent::setUp();

        $callbackState = $this->container->get(CallbackState::class);
        if ($callbackState instanceof CallbackState) {
            $this->callbackState = $callbackState;
        }
    }

    public function testFoo(): void
    {
        self::assertTrue(true);
    }


    /**
     * @dataProvider isDataProvider
     *
     * @param array<CallbackInterface::STATE_*> $callbackStates
     * @param array<CallbackState::STATE_*> $expectedIsStates
     * @param array<CallbackState::STATE_*> $expectedIsNotStates
     */
    public function testIs(array $callbackStates, array $expectedIsStates, array $expectedIsNotStates): void
    {
        foreach ($callbackStates as $callbackState) {
            $this->createCallbackEntity($callbackState);
        }

        self::assertTrue($this->callbackState->is(...$expectedIsStates));
        self::assertFalse($this->callbackState->is(...$expectedIsNotStates));
    }

    /**
     * @return array<mixed>
     */
    public function isDataProvider(): array
    {
        return [
            'no callbacks' => [
                'callbackStates' => [],
                'expectedIsStates' => [
                    CallbackState::STATE_AWAITING,
                ],
                'expectedIsNotStates' => [
                    CallbackState::STATE_RUNNING,
                    CallbackState::STATE_COMPLETE,
                ],
            ],
            'awaiting, sending, queued' => [
                'callbackStates' => [
                    CallbackInterface::STATE_AWAITING,
                    CallbackInterface::STATE_QUEUED,
                    CallbackInterface::STATE_SENDING,
                ],
                'expectedIsStates' => [
                    CallbackState::STATE_RUNNING,
                ],
                'expectedIsNotStates' => [
                    CallbackState::STATE_AWAITING,
                    CallbackState::STATE_COMPLETE,
                ],
            ],
            'awaiting, sending, queued, complete' => [
                'callbackStates' => [
                    CallbackInterface::STATE_AWAITING,
                    CallbackInterface::STATE_QUEUED,
                    CallbackInterface::STATE_SENDING,
                    CallbackInterface::STATE_COMPLETE,
                ],
                'expectedIsStates' => [
                    CallbackState::STATE_RUNNING,
                ],
                'expectedIsNotStates' => [
                    CallbackState::STATE_AWAITING,
                    CallbackState::STATE_COMPLETE,
                ],
            ],
            'awaiting, sending, queued, failed' => [
                'callbackStates' => [
                    CallbackInterface::STATE_AWAITING,
                    CallbackInterface::STATE_QUEUED,
                    CallbackInterface::STATE_SENDING,
                    CallbackInterface::STATE_FAILED,
                ],
                'expectedIsStates' => [
                    CallbackState::STATE_RUNNING,
                ],
                'expectedIsNotStates' => [
                    CallbackState::STATE_AWAITING,
                    CallbackState::STATE_COMPLETE,
                ],
            ],
            'two complete, three failed' => [
                'callbackStates' => [
                    CallbackInterface::STATE_COMPLETE,
                    CallbackInterface::STATE_COMPLETE,
                    CallbackInterface::STATE_FAILED,
                    CallbackInterface::STATE_FAILED,
                    CallbackInterface::STATE_FAILED,
                ],
                'expectedIsStates' => [
                    CallbackState::STATE_COMPLETE,
                ],
                'expectedIsNotStates' => [
                    CallbackState::STATE_AWAITING,
                    CallbackState::STATE_RUNNING,
                ],
            ],
        ];
    }

    /**
     * @param CallbackEntity::STATE_* $state
     *
     * @return CallbackEntity
     */
    private function createCallbackEntity(string $state): CallbackEntity
    {
        $entity = CallbackEntity::create(
            CallbackInterface::TYPE_EXECUTE_DOCUMENT_RECEIVED,
            []
        );

        $entity->setState($state);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }

//    /**
//     * @dataProvider setQueuedDataProvider
//     *
//     * @param CallbackInterface::STATE_* $initialState
//     * @param CallbackInterface::STATE_* $expectedState
//     */
//    public function testSetQueued(string $initialState, string $expectedState)
//    {
//        foreach ($this->createCallbacks() as $callback) {
//            $this->doSetAsStateTest(
//                $callback,
//                $initialState,
//                $expectedState,
//                function (CallbackInterface $callback) {
//                    $this->callbackState->setQueued($callback);
//                }
//            );
//        }
//    }
//
//    public function setQueuedDataProvider(): array
//    {
//        return [
//            CallbackInterface::STATE_AWAITING => [
//                'initialState' => CallbackInterface::STATE_AWAITING,
//                'expectedState' => CallbackInterface::STATE_QUEUED,
//            ],
//            CallbackInterface::STATE_QUEUED => [
//                'initialState' => CallbackInterface::STATE_QUEUED,
//                'expectedState' => CallbackInterface::STATE_QUEUED,
//            ],
//            CallbackInterface::STATE_SENDING => [
//                'initialState' => CallbackInterface::STATE_SENDING,
//                'expectedState' => CallbackInterface::STATE_QUEUED,
//            ],
//            CallbackInterface::STATE_FAILED => [
//                'initialState' => CallbackInterface::STATE_FAILED,
//                'expectedState' => CallbackInterface::STATE_FAILED,
//            ],
//            CallbackInterface::STATE_COMPLETE => [
//                'initialState' => CallbackInterface::STATE_COMPLETE,
//                'expectedState' => CallbackInterface::STATE_COMPLETE,
//            ],
//        ];
//    }
//
//    /**
//     * @dataProvider setSendingDataProvider
//     *
//     * @param CallbackInterface::STATE_* $initialState
//     * @param CallbackInterface::STATE_* $expectedState
//     */
//    public function testSetSending(string $initialState, string $expectedState)
//    {
//        foreach ($this->createCallbacks() as $callback) {
//            $this->doSetAsStateTest(
//                $callback,
//                $initialState,
//                $expectedState,
//                function (CallbackInterface $callback) {
//                    $this->callbackState->setSending($callback);
//                }
//            );
//        }
//    }
//
//    public function setSendingDataProvider(): array
//    {
//        return [
//            CallbackInterface::STATE_AWAITING => [
//                'initialState' => CallbackInterface::STATE_AWAITING,
//                'expectedState' => CallbackInterface::STATE_AWAITING,
//            ],
//            CallbackInterface::STATE_QUEUED => [
//                'initialState' => CallbackInterface::STATE_QUEUED,
//                'expectedState' => CallbackInterface::STATE_SENDING,
//            ],
//            CallbackInterface::STATE_SENDING => [
//                'initialState' => CallbackInterface::STATE_SENDING,
//                'expectedState' => CallbackInterface::STATE_SENDING,
//            ],
//            CallbackInterface::STATE_FAILED => [
//                'initialState' => CallbackInterface::STATE_FAILED,
//                'expectedState' => CallbackInterface::STATE_FAILED,
//            ],
//            CallbackInterface::STATE_COMPLETE => [
//                'initialState' => CallbackInterface::STATE_COMPLETE,
//                'expectedState' => CallbackInterface::STATE_COMPLETE,
//            ],
//        ];
//    }
//
//    /**
//     * @dataProvider setFailedDataProvider
//     *
//     * @param CallbackInterface::STATE_* $initialState
//     * @param CallbackInterface::STATE_* $expectedState
//     */
//    public function testSetFailed(string $initialState, string $expectedState)
//    {
//        foreach ($this->createCallbacks() as $callback) {
//            $this->doSetAsStateTest(
//                $callback,
//                $initialState,
//                $expectedState,
//                function (CallbackInterface $callback) {
//                    $this->callbackState->setFailed($callback);
//                }
//            );
//        }
//    }
//
//    public function setFailedDataProvider(): array
//    {
//        return [
//            CallbackInterface::STATE_AWAITING => [
//                'initialState' => CallbackInterface::STATE_AWAITING,
//                'expectedState' => CallbackInterface::STATE_AWAITING,
//            ],
//            CallbackInterface::STATE_QUEUED => [
//                'initialState' => CallbackInterface::STATE_QUEUED,
//                'expectedState' => CallbackInterface::STATE_QUEUED,
//            ],
//            CallbackInterface::STATE_SENDING => [
//                'initialState' => CallbackInterface::STATE_SENDING,
//                'expectedState' => CallbackInterface::STATE_FAILED,
//            ],
//            CallbackInterface::STATE_FAILED => [
//                'initialState' => CallbackInterface::STATE_FAILED,
//                'expectedState' => CallbackInterface::STATE_FAILED,
//            ],
//            CallbackInterface::STATE_COMPLETE => [
//                'initialState' => CallbackInterface::STATE_COMPLETE,
//                'expectedState' => CallbackInterface::STATE_COMPLETE,
//            ],
//        ];
//    }
//
//    /**
//     * @dataProvider setCompleteDataProvider
//     *
//     * @param CallbackInterface::STATE_* $initialState
//     * @param CallbackInterface::STATE_* $expectedState
//     */
//    public function testSetComplete(string $initialState, string $expectedState)
//    {
//        foreach ($this->createCallbacks() as $callback) {
//            $this->doSetAsStateTest(
//                $callback,
//                $initialState,
//                $expectedState,
//                function (CallbackInterface $callback) {
//                    $this->callbackState->setComplete($callback);
//                }
//            );
//        }
//    }
//
//    public function setCompleteDataProvider(): array
//    {
//        return [
//            CallbackInterface::STATE_AWAITING => [
//                'initialState' => CallbackInterface::STATE_AWAITING,
//                'expectedState' => CallbackInterface::STATE_AWAITING,
//            ],
//            CallbackInterface::STATE_QUEUED => [
//                'initialState' => CallbackInterface::STATE_QUEUED,
//                'expectedState' => CallbackInterface::STATE_QUEUED,
//            ],
//            CallbackInterface::STATE_SENDING => [
//                'initialState' => CallbackInterface::STATE_SENDING,
//                'expectedState' => CallbackInterface::STATE_COMPLETE,
//            ],
//            CallbackInterface::STATE_FAILED => [
//                'initialState' => CallbackInterface::STATE_FAILED,
//                'expectedState' => CallbackInterface::STATE_FAILED,
//            ],
//            CallbackInterface::STATE_COMPLETE => [
//                'initialState' => CallbackInterface::STATE_COMPLETE,
//                'expectedState' => CallbackInterface::STATE_COMPLETE,
//            ],
//        ];
//    }
//
//    /**
//     * @dataProvider setSendingDataProvider
//     *
//     * @param CallbackInterface $callback
//     * @param CallbackInterface::STATE_* $initialState
//     * @param CallbackInterface::STATE_* $expectedState
//     * @param callable $setter
//     */
//    private function doSetAsStateTest(
//        CallbackInterface $callback,
//        string $initialState,
//        string $expectedState,
//        callable $setter
//    ): void {
//        $callback->setState($initialState);
//
//        $this->entityManager->persist($callback);
//        $this->entityManager->flush();
//
//        self::assertSame($initialState, $callback->getState());
//
//        $setter($callback);
//
//        self::assertSame($expectedState, $callback->getState());
//    }
//
//    /**
//     * @return CallbackInterface[]
//     */
//    private function createCallbacks(): array
//    {
//        return [
//            'default entity' => $this->createCallbackEntity(),
//        ];
//    }
//
//    private function createCallbackEntity(): CallbackEntity
//    {
//        return CallbackEntity::create(CallbackInterface::TYPE_COMPILE_FAILURE, []);
//    }
}
