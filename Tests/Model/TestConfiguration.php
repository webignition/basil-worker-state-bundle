<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Model;

use webignition\BasilWorker\PersistenceBundle\Entity\TestConfiguration as TestConfigurationEntity;

class TestConfiguration
{
    private TestConfigurationEntity $testConfigurationEntity;
    private string $source;
    private string $target;
    private int $stepCount;

    public function __construct(
        TestConfigurationEntity $testConfigurationEntity,
        string $source,
        string $target,
        int $stepCount
    ) {
        $this->testConfigurationEntity = $testConfigurationEntity;
        $this->source = $source;
        $this->target = $target;
        $this->stepCount = $stepCount;
    }

    public static function create(): TestConfiguration
    {
        return new TestConfiguration(
            TestConfigurationEntity::create('chrome', 'http://example.com'),
            '/app/source/test.yml',
            '/app/target/GeneratedTest.php',
            1
        );
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

    public function withSource(string $source): TestConfiguration
    {
        $new = clone $this;
        $new->source = $source;

        return $new;
    }
}
