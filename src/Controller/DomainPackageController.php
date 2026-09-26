<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Package\DomainReleasePackageServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DomainPackageController extends AbstractController
{
    #[Route('/domain/release/package', name: 'domain_release_package_package', methods: ['GET'])]
    public function package(DomainReleasePackageServiceInterface $packageService): JsonResponse
    {
        return $this->json(['domainReleasePackage' => $packageService->buildPackage()->toArray()]);
    }
}
