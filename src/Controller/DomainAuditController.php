<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Audit\DomainAuditTrailReadServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class DomainAuditController extends AbstractController
{
    #[Route('/domain/audit/{domainName}', name: 'domaining_audit_recent_for_domain', methods: ['GET'])]
    public function recent(string $domainName, Request $request, DomainAuditTrailReadServiceInterface $auditTrailReadService): JsonResponse
    {
        $limit = max(1, min(250, (int) $request->query->get('limit', '50')));
        $entries = array_map(static fn ($entry): array => $entry->toArray(), $auditTrailReadService->recentForDomain($domainName, $limit));

        return $this->json([
            'domain_name' => $domainName,
            'limit' => $limit,
            'items' => $entries,
        ]);
    }
}
