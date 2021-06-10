<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Services;

use webignition\BasilWorker\PersistenceBundle\Entity\Source;
use webignition\BasilWorker\PersistenceBundle\Services\Repository\TestRepository;
use webignition\BasilWorker\PersistenceBundle\Services\Store\SourceStore;
use webignition\StringPrefixRemover\DefinedStringPrefixRemover;

class SourcePathFinder
{
    private DefinedStringPrefixRemover $compilerSourcePathPrefixRemover;

    public function __construct(
        private TestRepository $testRepository,
        private SourceStore $sourceStore
    ) {
    }

    public function setCompilerSourcePathPrefixRemover(
        DefinedStringPrefixRemover $compilerSourcePathPrefixRemover
    ): void {
        $this->compilerSourcePathPrefixRemover = $compilerSourcePathPrefixRemover;
    }

    /**
     * @return string[]
     */
    public function findCompiledPaths(): array
    {
        $sources = $this->testRepository->findAllSources();

        return $this->removeCompilerSourceDirectoryPrefixFromPaths($sources);
    }

    public function findNextNonCompiledPath(): ?string
    {
        $sourcePaths = $this->sourceStore->findAllPaths(Source::TYPE_TEST);
        $testPaths = $this->testRepository->findAllSources();
        $testPaths = $this->removeCompilerSourceDirectoryPrefixFromPaths($testPaths);

        foreach ($sourcePaths as $sourcePath) {
            if (!in_array($sourcePath, $testPaths)) {
                return $sourcePath;
            }
        }

        return null;
    }

    /**
     * @param string[] $paths
     *
     * @return string[]
     */
    private function removeCompilerSourceDirectoryPrefixFromPaths(array $paths): array
    {
        $strippedPaths = [];

        foreach ($paths as $path) {
            if (is_string($path)) {
                $strippedPaths[] = $this->compilerSourcePathPrefixRemover->remove($path);
            }
        }

        return $strippedPaths;
    }
}
