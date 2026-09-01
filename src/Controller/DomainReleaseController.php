<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Release\DomainReleaseGateServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/domain/release')]
final class DomainReleaseController extends AbstractController
{
    #[Route('/gate', name: 'domaining_release_gate', methods: ['GET'])]
    public function gate(DomainReleaseGateServiceInterface $releaseGateService): JsonResponse
    {
        return $this->json($releaseGateService->evaluate()->toArray());
    }
}

