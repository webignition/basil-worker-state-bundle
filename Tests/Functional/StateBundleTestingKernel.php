<?php

declare(strict_types=1);

namespace webignition\BasilWorker\StateBundle\Tests\Functional;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use webignition\BasilWorker\PersistenceBundle\PersistenceBundle;
use webignition\BasilWorker\StateBundle\StateBundle;

class StateBundleTestingKernel extends Kernel
{
    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [
            new StateBundle(),
            new PersistenceBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }
}
