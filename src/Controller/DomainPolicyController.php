<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\Enum\DomainSurfaceType;
use App\Domaining\ServiceInterface\Policy\DomainSurfacePolicyServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/domain/policy', name: 'domain_policy_')]
final class DomainPolicyController extends AbstractController
{
    #[Route('/surface/{ownerId}', name: 'surface_report', methods: ['GET'])]
    public function surfaceReport(string $ownerId, Request $request, DomainSurfacePolicyServiceInterface $policyService): JsonResponse
    {
        $surfaceTypeValue = $request->query->getString('surfaceType');
        $surfaceType = '' === $surfaceTypeValue ? null : DomainSurfaceType::from($surfaceTypeValue);
        $surfaceKey = $request->query->getString('surfaceKey');
        $surfaceKey = '' === $surfaceKey ? null : $surfaceKey;

        return $this->json($policyService->buildReport($ownerId, $surfaceType, $surfaceKey)->toArray());
    }
}

