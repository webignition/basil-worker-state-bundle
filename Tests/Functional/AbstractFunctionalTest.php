<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use webignition\BasilWorker\PersistenceBundle\Tests\Services\DatabaseSchemaCreator;

abstract class AbstractFunctionalTest extends TestCase
{
    protected ContainerInterface $container;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createContainer();
        $this->createDatabaseSchema();

        $entityManager = $this->container->get(EntityManagerInterface::class);
        if ($entityManager instanceof EntityManagerInterface) {
            $this->entityManager = $entityManager;
        }
    }

    private function createContainer(): ContainerInterface
    {
        $kernel = new StateBundleTestingKernel('test', true);
        $kernel->boot();

        return $kernel->getContainer();
    }

    private function createDatabaseSchema(): void
    {
        $databaseSchemaCreator = $this->container->get(DatabaseSchemaCreator::class);
        if ($databaseSchemaCreator instanceof DatabaseSchemaCreator) {
            $databaseSchemaCreator->create();
        }
    }
}
