<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Contract\DomainContractGovernanceServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/domain/contract', name: 'domain_contract_')]
final class DomainContractController extends AbstractController
{
    #[Route('/governance', name: 'governance', methods: ['GET'])]
    public function governance(DomainContractGovernanceServiceInterface $governanceService): JsonResponse
    {
        return $this->json(['domainContractGovernance' => $governanceService->buildReport()->toArray()]);
    }
}

