<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Manifest\DomainReleaseManifestServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/domain/release', name: 'domain_release_manifest_')]
final class DomainManifestController extends AbstractController
{
    #[Route('/manifest', name: 'manifest', methods: ['GET'])]
    public function manifest(DomainReleaseManifestServiceInterface $manifestService): JsonResponse
    {
        return $this->json(['domainReleaseManifest' => $manifestService->buildManifest()->toArray()]);
    }
}

