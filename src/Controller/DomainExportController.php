<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Export\DomainStateExportServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/domain/export', name: 'domain_export_')]
final class DomainExportController extends AbstractController
{
    #[Route('/state', name: 'state', methods: ['GET'])]
    public function state(DomainStateExportServiceInterface $exportService): JsonResponse
    {
        return $this->json(['domainStateExport' => $exportService->buildExport()->toArray()]);
    }
}

