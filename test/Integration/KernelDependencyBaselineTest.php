<?php

declare(strict_types=1);

namespace App\Domaining\Test\Integration;

use App\Collectioning\CollectioningBundle;
use App\Domaining\Kernel;
use App\Tabling\TablingBundle;
use PHPUnit\Framework\TestCase;

final class KernelDependencyBaselineTest extends TestCase
{
    public function testStandaloneKernelRegistersAndBootsBaselineBundles(): void
    {
        $kernel = new Kernel('test', true);

        try {
            $kernel->boot();

            $bundles = $kernel->getBundles();

            self::assertInstanceOf(
                CollectioningBundle::class,
                $bundles['CollectioningBundle'] ?? null,
            );
            self::assertInstanceOf(
                TablingBundle::class,
                $bundles['TablingBundle'] ?? null,
            );
        } finally {
            $kernel->shutdown();
        }
    }
}
