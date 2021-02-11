<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Model;

class EntityConfiguration
{
    private ?JobConfiguration $jobConfiguration = null;

    /**
     * @var SourceConfiguration[]
     */
    private array $sourceConfigurations = [];

    /**
     * @var TestConfiguration[]
     */
    private array $testConfigurations = [];

    /**
     * @var CallbackConfiguration[]
     */
    private array $callbackConfigurations = [];

    public function withJobConfiguration(JobConfiguration $jobConfiguration): EntityConfiguration
    {
        $new = clone $this;
        $new->jobConfiguration = $jobConfiguration;

        return $new;
    }

    /**
     * @param SourceConfiguration[] $sourceConfigurations
     */
    public function withSourceConfigurations(array $sourceConfigurations): EntityConfiguration
    {
        $new = clone $this;
        $new->sourceConfigurations = $sourceConfigurations;

        return $new;
    }

    /**
     * @param TestConfiguration[] $testConfigurations
     */
    public function withTestConfigurations(array $testConfigurations): EntityConfiguration
    {
        $new = clone $this;
        $new->testConfigurations = $testConfigurations;

        return $new;
    }

    /**
     * @param CallbackConfiguration[] $callbackConfigurations
     */
    public function withCallbackConfigurations(array $callbackConfigurations): EntityConfiguration
    {
        $new = clone $this;
        $new->callbackConfigurations = $callbackConfigurations;

        return $new;
    }

    public function getJobConfiguration(): ?JobConfiguration
    {
        return $this->jobConfiguration;
    }

    /**
     * @return SourceConfiguration[]
     */
    public function getSourceConfigurations(): array
    {
        return $this->sourceConfigurations;
    }

    /**
     * @return TestConfiguration[]
     */
    public function getTestConfigurations(): array
    {
        return $this->testConfigurations;
    }

    /**
     * @return CallbackConfiguration[]
     */
    public function getCallbackConfigurations(): array
    {
        return $this->callbackConfigurations;
    }
}
