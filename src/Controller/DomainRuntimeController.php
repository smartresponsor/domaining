<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Runtime\DomainRuntimeHandoffServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DomainRuntimeController extends AbstractController
{
    #[Route('/domain/runtime/handoff', name: 'domain_runtime_handoff', methods: ['GET'])]
    public function handoff(DomainRuntimeHandoffServiceInterface $handoffService): JsonResponse
    {
        return $this->json($handoffService->buildReport()->toArray());
    }
}
