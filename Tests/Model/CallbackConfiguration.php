<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Model;

use webignition\BasilWorker\PersistenceBundle\Entity\Callback\CallbackInterface;

class CallbackConfiguration
{
    private const DEFAULT_TYPE = CallbackInterface::TYPE_STEP_PASSED;
    private const DEFAULT_PAYLOAD = [];
    private const DEFAULT_STATE = CallbackInterface::STATE_AWAITING;

    /**
     * @var CallbackInterface::TYPE_*
     */
    private string $type = self::DEFAULT_TYPE;

    /**
     * @var array<mixed>
     */
    private array $payload = self::DEFAULT_PAYLOAD;

    /**
     * @var CallbackInterface::STATE_*
     */
    private string $state = self::DEFAULT_STATE;


    public static function create(): CallbackConfiguration
    {
        return new CallbackConfiguration();
    }

    /**
     * @param CallbackInterface::TYPE_* $type
     */
    public function withType(string $type): CallbackConfiguration
    {
        $new = clone $this;
        $new->type = $type;

        return $new;
    }

    /**
     * @param array<mixed> $payload
     */
    public function withPayload(array $payload): CallbackConfiguration
    {
        $new = clone $this;
        $new->payload = $payload;

        return $new;
    }

    /**
     * @param CallbackInterface::STATE_* $state
     */
    public function withState(string $state): CallbackConfiguration
    {
        $new = clone $this;
        $new->state = $state;

        return $new;
    }

    /**
     * @return CallbackInterface::TYPE_*
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return CallbackInterface::STATE_*
     */
    public function getState(): string
    {
        return $this->state;
    }
}
