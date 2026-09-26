<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Observability\DomainObservabilityServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DomainObservabilityController extends AbstractController
{
    #[Route('/domain/observability/readiness', name: 'domain_observability_readiness', methods: ['GET'])]
    public function readiness(DomainObservabilityServiceInterface $observabilityService): JsonResponse
    {
        $report = $observabilityService->readinessReport();

        return $this->json($report->toArray(), $report->ready ? JsonResponse::HTTP_OK : JsonResponse::HTTP_CONFLICT);
    }

    #[Route('/domain/observability/metric/snapshot', name: 'domain_observability_metric_snapshot', methods: ['GET'])]
    public function metricSnapshot(DomainObservabilityServiceInterface $observabilityService): JsonResponse
    {
        return $this->json($observabilityService->metricSnapshot());
    }
}
