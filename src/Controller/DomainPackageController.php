<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Package\DomainReleasePackageServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/domain/release', name: 'domain_release_package_')]
final class DomainPackageController extends AbstractController
{
    #[Route('/package', name: 'package', methods: ['GET'])]
    public function package(DomainReleasePackageServiceInterface $packageService): JsonResponse
    {
        return $this->json(['domainReleasePackage' => $packageService->buildPackage()->toArray()]);
    }
}

