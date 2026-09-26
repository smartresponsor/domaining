<?php

declare(strict_types=1);

namespace App\Domaining;

use App\Domaining\DependencyInjection\DomainingExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class CustomDomainBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new DomainingExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
}
