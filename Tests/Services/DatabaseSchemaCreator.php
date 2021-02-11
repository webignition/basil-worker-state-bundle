<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Services;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class DatabaseSchemaCreator
{
    private EntityManagerInterface $entityManager;

    /**
     * @var array<class-string>
     */
    private array $entityClasses;

    /**
     * @param EntityManagerInterface $entityManager
     * @param array<class-string> $entityClasses
     */
    public function __construct(EntityManagerInterface $entityManager, array $entityClasses)
    {
        $this->entityManager = $entityManager;
        $this->entityClasses = $entityClasses;
    }

    public function create(): void
    {
        $tool = new SchemaTool($this->entityManager);

        $classMetadataCollection = [];
        foreach ($this->entityClasses as $entityClass) {
            $classMetadataCollection[] = $this->entityManager->getClassMetadata($entityClass);
        }

        $tool->createSchema($classMetadataCollection);
    }
}
