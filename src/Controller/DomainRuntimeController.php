<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Runtime\DomainRuntimeHandoffServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/domain/runtime', name: 'domain_runtime_')]
final class DomainRuntimeController extends AbstractController
{
    #[Route('/handoff', name: 'handoff', methods: ['GET'])]
    public function handoff(DomainRuntimeHandoffServiceInterface $handoffService): JsonResponse
    {
        return $this->json($handoffService->buildReport()->toArray());
    }
}

