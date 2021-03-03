<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Model;

class JobConfiguration
{
    public function __construct(
        private string $label,
        private string $callbackUrl,
        private int $maximumDurationInSeconds
    ) {
    }

    public static function create(): JobConfiguration
    {
        return new JobConfiguration(
            'label content',
            'http://example.com/callback',
            600
        );
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    public function getMaximumDurationInSeconds(): int
    {
        return $this->maximumDurationInSeconds;
    }
}
