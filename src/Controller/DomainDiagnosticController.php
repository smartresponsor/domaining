<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Diagnostic\DomainDiagnosticServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/domain/diagnostic', name: 'domain_diagnostic_')]
final class DomainDiagnosticController extends AbstractController
{
    #[Route('/report', name: 'report', methods: ['GET'])]
    public function report(DomainDiagnosticServiceInterface $diagnosticService): JsonResponse
    {
        return $this->json($diagnosticService->buildReport()->toArray());
    }
}

