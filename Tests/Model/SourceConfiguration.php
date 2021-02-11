<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Model;

use webignition\BasilWorker\PersistenceBundle\Entity\Source;

class SourceConfiguration
{
    /**
     * @var Source::TYPE_* $type
     */
    private string $type;
    private string $path;

    /**
     * @param Source::TYPE_* $type
     */
    public function __construct(string $type, string $path)
    {
        $this->type = $type;
        $this->path = $path;
    }

    public static function create(): SourceConfiguration
    {
        return new SourceConfiguration(Source::TYPE_TEST, 'test.yml');
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
