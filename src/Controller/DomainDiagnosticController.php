<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Diagnostic\DomainDiagnosticServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DomainDiagnosticController extends AbstractController
{
    #[Route('/domain/diagnostic/report', name: 'domain_diagnostic_report', methods: ['GET'])]
    public function report(DomainDiagnosticServiceInterface $diagnosticService): JsonResponse
    {
        return $this->json($diagnosticService->buildReport()->toArray());
    }
}
