<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use webignition\BasilWorker\PersistenceBundle\Tests\Services\DatabaseSchemaCreator;

abstract class AbstractFunctionalTest extends TestCase
{
    protected static ContainerInterface $container;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        self::$container = $this->createContainer();
        $this->createDatabaseSchema();

        $entityManager = self::$container->get(EntityManagerInterface::class);
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
        $databaseSchemaCreator = self::$container->get(DatabaseSchemaCreator::class);
        if ($databaseSchemaCreator instanceof DatabaseSchemaCreator) {
            $databaseSchemaCreator->create();
        }
    }
}
