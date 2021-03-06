<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Functional\Services;

use webignition\BasilWorker\StateBundle\Services\SourcePathFinder;
use webignition\BasilWorker\StateBundle\Tests\Functional\AbstractFunctionalTest;
use webignition\BasilWorker\StateBundle\Tests\Model\EntityConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\JobConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\SourceConfiguration;
use webignition\BasilWorker\StateBundle\Tests\Model\TestConfiguration;

class SourcePathFinderTest extends AbstractFunctionalTest
{
    private SourcePathFinder $sourcePathFinder;

    protected function setUp(): void
    {
        parent::setUp();

        $sourcePathFinder = self::$container->get(SourcePathFinder::class);
        if ($sourcePathFinder instanceof SourcePathFinder) {
            $this->sourcePathFinder = $sourcePathFinder;
        }
    }

    /**
     * @dataProvider findNextNonCompiledPathDataProvider
     */
    public function testFindNextNonCompiledPath(
        EntityConfiguration $entityConfiguration,
        ?string $expectedNextNonCompiledSource
    ): void {
        $this->entityCreator->create($entityConfiguration);

        self::assertSame($expectedNextNonCompiledSource, $this->sourcePathFinder->findNextNonCompiledPath());
    }

    /**
     * @return array<mixed>
     */
    public function findNextNonCompiledPathDataProvider(): array
    {
        $sources = [
            'Page/page1.yml',
            'Test/testApple.yml',
            'Page/page2.yml',
            'Test/testBat.yml',
            'Page/page3.yml',
            'Test/testZebra.yml',
        ];

        $sourceConfigurations = [
            SourceConfiguration::createResource()->withPath($sources[0]),
            SourceConfiguration::createTest()->withPath($sources[1]),
            SourceConfiguration::createResource()->withPath($sources[2]),
            SourceConfiguration::createTest()->withPath($sources[3]),
            SourceConfiguration::createResource()->withPath($sources[4]),
            SourceConfiguration::createTest()->withPath($sources[5]),
        ];

        return [
            'no job' => [
                'entityConfiguration' => new EntityConfiguration(),
                'expectedNextNonCompiledSource' => null,
            ],
            'has job, no sources' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create()),
                'expectedNextNonCompiledSource' => null,
            ],
            'has job, has resource-only sources, no tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations([
                        $sourceConfigurations[0],
                        $sourceConfigurations[2],
                        $sourceConfigurations[4],
                    ]),
                'expectedNextNonCompiledSource' => null,
            ],
            'has job, has sources, no tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations($sourceConfigurations),
                'expectedNextNonCompiledSource' => $sources[1],
            ],
            'test exists for first test source' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations($sourceConfigurations)
                    ->withTestConfigurations([
                        TestConfiguration::create()
                            ->withSource('/app/source/' . $sources[1]),
                    ]),
                'expectedNextNonCompiledSource' => $sources[3],
            ],
            'test exists for first and second test sources' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations($sourceConfigurations)
                    ->withTestConfigurations([
                        TestConfiguration::create()
                            ->withSource('/app/source/' . $sources[1]),
                        TestConfiguration::create()
                            ->withSource('/app/source/' . $sources[3]),
                    ]),
                'expectedNextNonCompiledSource' => $sources[5],
            ],
            'tests exist for all test sources' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations($sourceConfigurations)
                    ->withTestConfigurations([
                        TestConfiguration::create()
                            ->withSource('/app/source/' . $sources[1]),
                        TestConfiguration::create()
                            ->withSource('/app/source/' . $sources[3]),
                        TestConfiguration::create()
                            ->withSource('/app/source/' . $sources[5]),
                    ]),
                'expectedNextNonCompiledSource' => null,
            ],
        ];
    }

    /**
     * @dataProvider findCompiledPathsDataProvider
     *
     * @param string[] $expectedCompiledSources
     */
    public function testFindCompiledPaths(
        EntityConfiguration $entityConfiguration,
        array $expectedCompiledSources
    ): void {
        $this->entityCreator->create($entityConfiguration);

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
            SourceConfiguration::createTest()->withPath($sources[0]),
            SourceConfiguration::createTest()->withPath($sources[1]),
            SourceConfiguration::createTest()->withPath($sources[2]),
        ];

        return [
            'no job' => [
                'entityConfiguration' => new EntityConfiguration(),
                'expectedCompiledSources' => [],
            ],
            'has job, no sources' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create()),
                'expectedCompiledSources' => [],
            ],
            'has job, has sources, no tests' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations($sourceConfigurations),
                'expectedCompiledSources' => [],
            ],
            'test exists for first source' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations($sourceConfigurations)
                    ->withTestConfigurations([
                        TestConfiguration::create()
                            ->withSource('/app/source/' . $sources[0]),
                    ]),
                'expectedCompiledSources' => [
                    'Test/testZebra.yml',
                ],
            ],
            'test exists for first and second sources' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations($sourceConfigurations)
                    ->withTestConfigurations([
                        TestConfiguration::create()
                            ->withSource('/app/source/' . $sources[0]),
                        TestConfiguration::create()
                            ->withSource('/app/source/' . $sources[1]),
                    ]),
                'expectedCompiledSources' => [
                    'Test/testZebra.yml',
                    'Test/testApple.yml',
                ],
            ],
            'tests exist for all sources' => [
                'entityConfiguration' => (new EntityConfiguration())
                    ->withJobConfiguration(JobConfiguration::create())
                    ->withSourceConfigurations($sourceConfigurations)
                    ->withTestConfigurations([
                        TestConfiguration::create()
                            ->withSource('/app/source/' . $sources[0]),
                        TestConfiguration::create()
                            ->withSource('/app/source/' . $sources[1]),
                        TestConfiguration::create()
                            ->withSource('/app/source/' . $sources[2]),
                    ]),
                'expectedCompiledSources' => [
                    'Test/testZebra.yml',
                    'Test/testApple.yml',
                    'Test/testBat.yml',
                ],
            ],
        ];
    }
}
