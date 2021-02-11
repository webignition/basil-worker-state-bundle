<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Services;

use webignition\BasilWorker\PersistenceBundle\Services\Factory\CallbackFactory;
use webignition\BasilWorker\PersistenceBundle\Services\Factory\JobFactory;
use webignition\BasilWorker\PersistenceBundle\Services\Factory\SourceFactory;
use webignition\BasilWorker\PersistenceBundle\Services\Factory\TestFactory;
use webignition\BasilWorker\StateBundle\Tests\Model\EntityConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\JobConfiguration;

class EntityCreator
{
    private JobFactory $jobFactory;
    private SourceFactory $sourceFactory;
    private TestFactory $testFactory;
    private CallbackFactory $callbackFactory;

    public function __construct(
        JobFactory $jobFactory,
        SourceFactory $sourceFactory,
        TestFactory $testFactory,
        CallbackFactory $callbackFactory
    ) {
        $this->jobFactory = $jobFactory;
        $this->sourceFactory = $sourceFactory;
        $this->testFactory = $testFactory;
        $this->callbackFactory = $callbackFactory;
    }

    public function create(EntityConfiguration $entityConfiguration): void
    {
        $jobConfiguration = $entityConfiguration->getJobConfiguration();

        if ($jobConfiguration instanceof JobConfiguration) {
            $this->jobFactory->create(
                $jobConfiguration->getLabel(),
                $jobConfiguration->getCallbackUrl(),
                $jobConfiguration->getMaximumDurationInSeconds()
            );
        }

        $sourceConfigurations = $entityConfiguration->getSourceConfigurations();
        foreach ($sourceConfigurations as $sourceConfiguration) {
            $this->sourceFactory->create($sourceConfiguration->getType(), $sourceConfiguration->getPath());
        }

        $testConfigurations = $entityConfiguration->getTestConfigurations();
        foreach ($testConfigurations as $testConfiguration) {
            $this->testFactory->create(
                $testConfiguration->getTestConfigurationEntity(),
                $testConfiguration->getSource(),
                $testConfiguration->getTarget(),
                $testConfiguration->getStepCount()
            );
        }

        $callbackConfigurations = $entityConfiguration->getCallbackConfigurations();
        foreach ($callbackConfigurations as $callbackConfiguration) {
            $this->callbackFactory->create($callbackConfiguration->getType(), $callbackConfiguration->getPayload());
        }
    }
}
