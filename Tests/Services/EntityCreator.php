<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Services;

use Doctrine\ORM\EntityManagerInterface;
use webignition\BasilWorker\PersistenceBundle\Services\Factory\CallbackFactory;
use webignition\BasilWorker\PersistenceBundle\Services\Factory\JobFactory;
use webignition\BasilWorker\PersistenceBundle\Services\Factory\SourceFactory;
use webignition\BasilWorker\PersistenceBundle\Services\Factory\TestFactory;
use webignition\BasilWorker\StateBundle\Tests\Model\EntityConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\JobConfiguration;

class EntityCreator
{
    private EntityManagerInterface $entityManager;
    private JobFactory $jobFactory;
    private SourceFactory $sourceFactory;
    private TestFactory $testFactory;
    private CallbackFactory $callbackFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        JobFactory $jobFactory,
        SourceFactory $sourceFactory,
        TestFactory $testFactory,
        CallbackFactory $callbackFactory
    ) {
        $this->entityManager = $entityManager;
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
            $test = $this->testFactory->create(
                $testConfiguration->getTestConfigurationEntity(),
                $testConfiguration->getSource(),
                $testConfiguration->getTarget(),
                $testConfiguration->getStepCount()
            );

            $test->setState($testConfiguration->getState());

            $this->entityManager->persist($test);
            $this->entityManager->flush();
        }

        $callbackConfigurations = $entityConfiguration->getCallbackConfigurations();
        foreach ($callbackConfigurations as $callbackConfiguration) {
            $callback = $this->callbackFactory->create(
                $callbackConfiguration->getType(),
                $callbackConfiguration->getPayload()
            );
            $callback->setState($callbackConfiguration->getState());

            $this->entityManager->persist($callback);
            $this->entityManager->flush();
        }
    }
}
