<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Model;

use webignition\BasilWorker\PersistenceBundle\Entity\Callback\CallbackInterface;

class CallbackConfiguration
{
    /**
     * @var CallbackInterface::TYPE_*
     */
    private string $type;

    /**
     * @var array<mixed>
     */
    private array $payload;

    /**
     *
     * @param array<mixed> $payload
     */

    /**
     * @param CallbackInterface::TYPE_* $type
     * @param array<mixed> $payload
     */
    public function __construct(string $type, array $payload)
    {
        $this->type = $type;
        $this->payload = $payload;
    }

    public static function create(): CallbackConfiguration
    {
        return new CallbackConfiguration(
            CallbackInterface::TYPE_EXECUTE_DOCUMENT_RECEIVED,
            []
        );
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
}
