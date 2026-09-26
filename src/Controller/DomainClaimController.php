<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\DTO\DomainClaimRequestDTO;
use App\Domaining\Enum\DomainSurfaceType;
use App\Domaining\Repository\DomainDeclarationRepository;
use App\Domaining\ServiceInterface\Claim\DomainClaimCreationServiceInterface;
use App\Domaining\ServiceInterface\Verification\DomainVerificationChallengeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class DomainClaimController extends AbstractController
{
    #[Route('/domain/claim', name: 'domain_claim_create', methods: ['POST'])]
    public function create(
        Request $request,
        DomainClaimCreationServiceInterface $claimService,
        DomainVerificationChallengeServiceInterface $challengeService,
    ): JsonResponse {
        $payload = $request->toArray();
        $claim = $claimService->createClaim(new DomainClaimRequestDTO(
            domainName: (string) ($payload['domainName'] ?? ''),
            ownerId: (string) ($payload['ownerId'] ?? ''),
            surfaceType: DomainSurfaceType::tryFrom((string) ($payload['surfaceType'] ?? 'tenant')) ?? DomainSurfaceType::Tenant,
            surfaceKey: (string) ($payload['surfaceKey'] ?? 'default'),
        ));
        $challenge = $challengeService->issueTxtChallenge($claim);

        return $this->json([
            'claimId' => (string) $claim->id(),
            'domainName' => $claim->domainName(),
            'status' => $claim->status()->value,
            'dnsInstruction' => [
                'type' => $challenge->recordType()->value,
                'nameEntity' => $challenge->recordName(),
                'value' => $challenge->recordValue(),
                'expiresAt' => $challenge->expiresAt()->format(DATE_ATOM),
            ],
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/domain/claim/declaration/{id}', name: 'domain_claim_create_from_declaration', methods: ['POST'])]
    public function createFromDeclaration(
        string $id,
        Request $request,
        DomainDeclarationRepository $declarationRepository,
        DomainClaimCreationServiceInterface $claimService,
        DomainVerificationChallengeServiceInterface $challengeService,
    ): JsonResponse {
        $declaration = $declarationRepository->findOneById($id);
        if (null === $declaration) {
            throw $this->createNotFoundException(sprintf('Domain declaration "%s" was not found.', $id));
        }

        $payload = $request->toArray();
        $claim = $claimService->createForDeclaration(
            $declaration,
            (string) ($payload['ownerId'] ?? ''),
        );
        $challenge = $challengeService->issueTxtChallenge($claim);

        return $this->json([
            'declarationId' => $declaration->id()->toRfc4122(),
            'declarationStatus' => $declaration->status()->value,
            'applicationKey' => $declaration->applicationKey(),
            'environment' => $declaration->environment(),
            'claimId' => $claim->id()->toRfc4122(),
            'claimStatus' => $claim->status()->value,
            'domainName' => $claim->domainName(),
            'bindingCreated' => false,
            'runtimeActivated' => false,
            'dnsInstruction' => [
                'type' => $challenge->recordType()->value,
                'nameEntity' => $challenge->recordName(),
                'value' => $challenge->recordValue(),
                'expiresAt' => $challenge->expiresAt()->format(DATE_ATOM),
            ],
        ], JsonResponse::HTTP_CREATED);
    }
}
