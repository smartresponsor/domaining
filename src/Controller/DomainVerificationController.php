<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\Enum\DomainDnsProviderHint;
use App\Domaining\Repository\DomainVerificationChallengeRepository;
use App\Domaining\ServiceInterface\Verification\DomainDnsInstructionServiceInterface;
use App\Domaining\ServiceInterface\Verification\DomainDnsVerificationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/domain/verification', name: 'domain_verification_')]
final class DomainVerificationController extends AbstractController
{
    #[Route('/{challengeId}/instruction', name: 'instruction', methods: ['GET'])]
    public function instruction(
        string $challengeId,
        Request $request,
        DomainVerificationChallengeRepository $challengeRepository,
        DomainDnsInstructionServiceInterface $instructionService,
    ): JsonResponse {
        $challenge = $challengeRepository->find(Uuid::fromString($challengeId));
        if (null === $challenge) {
            return $this->json(['message' => 'Verification challenge was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $providerHint = DomainDnsProviderHint::tryFrom((string) $request->query->get('provider', 'unknown')) ?? DomainDnsProviderHint::Unknown;

        return $this->json($instructionService->buildInstructionSet($challenge, $providerHint)->toArray());
    }

    #[Route('/{challengeId}/check', name: 'check', methods: ['POST'])]
    public function check(
        string $challengeId,
        Request $request,
        DomainVerificationChallengeRepository $challengeRepository,
        DomainDnsVerificationServiceInterface $verificationService,
    ): JsonResponse {
        $challenge = $challengeRepository->find(Uuid::fromString($challengeId));
        if (null === $challenge) {
            return $this->json(['message' => 'Verification challenge was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $force = filter_var($request->query->get('force', false), FILTER_VALIDATE_BOOL);
        $result = $force ? $verificationService->recheck($challenge) : $verificationService->verify($challenge);

        return $this->json([
            'status' => $result->status->value,
            'message' => $result->message,
            'observedRecord' => $result->observedRecord,
        ]);
    }
}

