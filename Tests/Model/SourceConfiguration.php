<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Model;

use webignition\BasilWorker\PersistenceBundle\Entity\Source;

class SourceConfiguration
{
    /**
     * @param Source::TYPE_* $type $type
     */
    public function __construct(private string $type, private string $path)
    {
    }

    public static function createTest(string $path = 'test.yml'): SourceConfiguration
    {
        return new SourceConfiguration(Source::TYPE_TEST, $path);
    }

    public static function createResource(string $path = 'page.yml'): SourceConfiguration
    {
        return new SourceConfiguration(Source::TYPE_RESOURCE, $path);
    }

    /**
     * @return Source::TYPE_* $type
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function withPath(string $path): SourceConfiguration
    {
        $new = clone $this;
        $new->path = $path;

        return $new;
    }
}
