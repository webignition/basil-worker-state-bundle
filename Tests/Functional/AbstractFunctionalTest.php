<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class AbstractFunctionalTest extends TestCase
{
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createContainer();
    }

    private function createContainer(): ContainerInterface
    {
        $kernel = new StateBundleTestingKernel('test', true);
        $kernel->boot();

        return $kernel->getContainer();
    }
}
