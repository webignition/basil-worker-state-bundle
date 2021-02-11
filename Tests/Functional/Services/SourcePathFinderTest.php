<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Functional\Services;

use webignition\BasilWorker\PersistenceBundle\Services\Factory\JobFactory;
use webignition\BasilWorker\PersistenceBundle\Services\Factory\SourceFactory;
use webignition\BasilWorker\PersistenceBundle\Services\Factory\TestFactory;
use webignition\BasilWorker\StateBundle\Services\SourcePathFinder;
use webignition\BasilWorker\StateBundle\Tests\Functional\AbstractFunctionalTest;
use webignition\BasilWorker\StateBundle\Tests\Model\JobConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\SourceConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\TestConfiguration;
use webignition\SymfonyTestServiceInjectorTrait\TestClassServicePropertyInjectorTrait;

class SourcePathFinderTest extends AbstractFunctionalTest
{
    use TestClassServicePropertyInjectorTrait;

    private JobFactory $jobFactory;
    private SourceFactory $sourceFactory;
    private TestFactory $testFactory;
    private SourcePathFinder $sourcePathFinder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->injectContainerServicesIntoClassProperties();
    }

    /**
     * @dataProvider findNextNonCompiledPathDataProvider
     *
     * @param SourceConfiguration[] $sourceConfigurations
     * @param TestConfiguration[] $testConfigurations
     */
    public function testFindNextNonCompiledPath(
        ?JobConfiguration $jobConfiguration,
        array $sourceConfigurations,
        array $testConfigurations,
        ?string $expectedNextNonCompiledSource
    ): void {
        $this->setupTest($jobConfiguration, $sourceConfigurations, $testConfigurations);

        self::assertSame($expectedNextNonCompiledSource, $this->sourcePathFinder->findNextNonCompiledPath());
    }

    /**
     * @return array<mixed>
     */
    public function findNextNonCompiledPathDataProvider(): array
    {
        $sources = [
            'Test/testZebra.yml',
            'Test/testApple.yml',
            'Test/testBat.yml',
        ];

        $sourceConfigurations = [
            SourceConfiguration::create()->withPath($sources[0]),
            SourceConfiguration::create()->withPath($sources[1]),
            SourceConfiguration::create()->withPath($sources[2]),
        ];

        return [
            'no job' => [
                'jobConfiguration' => null,
                'sourceConfigurations' => [],
                'testConfigurations' => [],
                'expectedNextNonCompiledSource' => null,
            ],
            'has job, no sources' => [
                'jobConfiguration' => JobConfiguration::create(),
                'sourceConfigurations' => [],
                'testConfigurations' => [],
                'expectedNextNonCompiledSource' => null,
            ],
            'has job, has sources, no tests' => [
                'jobConfiguration' => JobConfiguration::create(),
                'sourceConfigurations' => $sourceConfigurations,
                'testConfigurations' => [],
                'expectedNextNonCompiledSource' => $sources[0],
            ],
            'test exists for first source' => [
                'jobConfiguration' => JobConfiguration::create(),
                'sourceConfigurations' => $sourceConfigurations,
                'testConfigurations' => [
                    TestConfiguration::create()
                        ->withSource('/app/source/' . $sources[0]),
                ],
                'expectedNextNonCompiledSource' => $sources[1],
            ],
            'test exists for first and second sources' => [
                'jobConfiguration' => JobConfiguration::create(),
                'sourceConfigurations' => $sourceConfigurations,
                'testConfigurations' => [
                    TestConfiguration::create()
                        ->withSource('/app/source/' . $sources[0]),
                    TestConfiguration::create()
                        ->withSource('/app/source/' . $sources[1]),
                ],
                'expectedNextNonCompiledSource' => $sources[2],
            ],
            'tests exist for all sources' => [
                'jobConfiguration' => JobConfiguration::create(),
                'sourceConfigurations' => $sourceConfigurations,
                'testConfigurations' => [
                    TestConfiguration::create()
                        ->withSource('/app/source/' . $sources[0]),
                    TestConfiguration::create()
                        ->withSource('/app/source/' . $sources[1]),
                    TestConfiguration::create()
                        ->withSource('/app/source/' . $sources[2]),
                ],
                'expectedNextNonCompiledSource' => null,
            ],
        ];
    }

    /**
     * @dataProvider findCompiledPathsDataProvider
     *
     * @param SourceConfiguration[] $sourceConfigurations
     * @param TestConfiguration[] $testConfigurations
     * @param string[] $expectedCompiledSources
     */
    public function testFindCompiledPaths(
        ?JobConfiguration $jobConfiguration,
        array $sourceConfigurations,
        array $testConfigurations,
        array $expectedCompiledSources
    ): void {
        $this->setupTest($jobConfiguration, $sourceConfigurations, $testConfigurations);

        self::assertSame($expectedCompiledSources, $this->sourcePathFinder->findCompiledPaths());
    }

    /**
     * @return array<mixed>
     */
    public function findCompiledPathsDataProvider(): array
    {
        $sources = [
            'Test/testZebra.yml',
            'Test/testApple.yml',
            'Test/testBat.yml',
        ];

        $sourceConfigurations = [
            SourceConfiguration::create()->withPath($sources[0]),
            SourceConfiguration::create()->withPath($sources[1]),
            SourceConfiguration::create()->withPath($sources[2]),
        ];

        return [
            'no job' => [
                'jobConfiguration' => null,
                'sourceConfigurations' => [],
                'testConfigurations' => [],
                'expectedCompiledSources' => [],
            ],
            'has job, no sources' => [
                'jobConfiguration' => JobConfiguration::create(),
                'sourceConfigurations' => [],
                'testConfigurations' => [],
                'expectedCompiledSources' => [],
            ],
            'has job, has sources, no tests' => [
                'jobConfiguration' => JobConfiguration::create(),
                'sourceConfigurations' => $sourceConfigurations,
                'testConfigurations' => [],
                'expectedCompiledSources' => [],
            ],
            'test exists for first source' => [
                'jobConfiguration' => JobConfiguration::create(),
                'sourceConfigurations' => $sourceConfigurations,
                'testConfigurations' => [
                    TestConfiguration::create()
                        ->withSource('/app/source/' . $sources[0]),
                ],
                'expectedCompiledSources' => [
                    'Test/testZebra.yml',
                ],
            ],
            'test exists for first and second sources' => [
                'jobConfiguration' => JobConfiguration::create(),
                'sourceConfigurations' => $sourceConfigurations,
                'testConfigurations' => [
                    TestConfiguration::create()
                        ->withSource('/app/source/' . $sources[0]),
                    TestConfiguration::create()
                        ->withSource('/app/source/' . $sources[1]),
                ],
                'expectedCompiledSources' => [
                    'Test/testZebra.yml',
                    'Test/testApple.yml',
                ],
            ],
            'tests exist for all sources' => [
                'jobConfiguration' => JobConfiguration::create(),
                'sourceConfigurations' => $sourceConfigurations,
                'testConfigurations' => [
                    TestConfiguration::create()
                        ->withSource('/app/source/' . $sources[0]),
                    TestConfiguration::create()
                        ->withSource('/app/source/' . $sources[1]),
                    TestConfiguration::create()
                        ->withSource('/app/source/' . $sources[2]),
                ],
                'expectedCompiledSources' => [
                    'Test/testZebra.yml',
                    'Test/testApple.yml',
                    'Test/testBat.yml',
                ],
            ],
        ];
    }

    /**
     * @param SourceConfiguration[] $sourceConfigurations
     * @param TestConfiguration[] $testConfigurations
     */
    private function setupTest(
        ?JobConfiguration $jobConfiguration,
        array $sourceConfigurations,
        array $testConfigurations
    ): void {
        if ($jobConfiguration instanceof JobConfiguration) {
            $this->jobFactory->create(
                $jobConfiguration->getLabel(),
                $jobConfiguration->getCallbackUrl(),
                $jobConfiguration->getMaximumDurationInSeconds()
            );
        }

        foreach ($sourceConfigurations as $sourceConfiguration) {
            $this->sourceFactory->create($sourceConfiguration->getType(), $sourceConfiguration->getPath());
        }

        foreach ($testConfigurations as $testConfiguration) {
            $this->testFactory->create(
                $testConfiguration->getTestConfigurationEntity(),
                $testConfiguration->getSource(),
                $testConfiguration->getTarget(),
                $testConfiguration->getStepCount()
            );
        }
    }
}
