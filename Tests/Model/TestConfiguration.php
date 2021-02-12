<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Model;

use webignition\BasilWorker\PersistenceBundle\Entity\Test;
use webignition\BasilWorker\PersistenceBundle\Entity\TestConfiguration as TestConfigurationEntity;

class TestConfiguration
{
    private const DEFAULT_SOURCE = '/app/source/test.yml';
    private const DEFAULT_TARGET = '/app/target/GeneratedTest.php';
    private const DEFAULT_STEP_COUNT = 1;

    private TestConfigurationEntity $testConfigurationEntity;
    private string $source = self::DEFAULT_SOURCE;
    private string $target = self::DEFAULT_TARGET;
    private int $stepCount = self::DEFAULT_STEP_COUNT;

    /**
     * @var Test::STATE_*
     */
    private string $state = Test::STATE_AWAITING;

    public static function create(): TestConfiguration
    {
        $testConfiguration = new TestConfiguration();
        $testConfiguration->testConfigurationEntity = TestConfigurationEntity::create('chrome', 'http://example.com');

        return $testConfiguration;
    }

    public function getTestConfigurationEntity(): TestConfigurationEntity
    {
        return $this->testConfigurationEntity;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function getStepCount(): int
    {
        return $this->stepCount;
    }

    /**
     * @return Test::STATE_*
     */
    public function getState(): string
    {
        return $this->state;
    }

    public function withSource(string $source): TestConfiguration
    {
        $new = clone $this;
        $new->source = $source;

        return $new;
    }

    /**
     * @param Test::STATE_* $state
     */
    public function withState(string $state): TestConfiguration
    {
        $new = clone $this;
        $new->state = $state;

        return $new;
    }
}
