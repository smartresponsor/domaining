<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\Repository\DomainClaimRepository;
use App\Domaining\ServiceInterface\Binding\DomainBindingServiceInterface;
use App\Domaining\ServiceInterface\Publication\DomainPublicationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

final class DomainBindingController extends AbstractController
{
    #[Route('/domain/binding/from/claim/{claimId}', name: 'domain_binding_create_from_claim', methods: ['POST'])]
    public function createFromClaim(string $claimId, DomainClaimRepository $claimRepository, DomainBindingServiceInterface $bindingService): JsonResponse
    {
        $claim = $claimRepository->find(Uuid::fromString($claimId));
        if (null === $claim) {
            return $this->json(['message' => 'Domain claim was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $binding = $bindingService->createFromVerifiedClaim($claim);

        return $this->json([
            'bindingId' => (string) $binding->id(),
            'domainName' => $binding->domainName(),
            'status' => $binding->status()->value,
            'declarationId' => $binding->declaration()?->id()->toRfc4122(),
            'applicationKey' => $binding->declaration()?->applicationKey(),
            'environment' => $binding->declaration()?->environment(),
            'declarationStatus' => $binding->declaration()?->status()->value,
            'runtimeActivated' => false,
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/domain/binding/{bindingId}/routing/intent', name: 'domain_binding_routing_intent', methods: ['POST'])]
    public function routingIntent(
        string $bindingId,
        Request $request,
        DomainBindingRepository $bindingRepository,
        DomainPublicationServiceInterface $publicationService,
    ): JsonResponse {
        $binding = $bindingRepository->find(Uuid::fromString($bindingId));
        if (null === $binding) {
            return $this->json(['message' => 'Domain binding was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $payload = $request->toArray();
        $intent = $publicationService->prepareRoutingIntent(
            $binding,
            (string) ($payload['targetHost'] ?? 'smart-responder.app'),
            (string) ($payload['targetPath'] ?? '/'),
        );

        return $this->json([
            'domainName' => $intent->domainName,
            'ownerId' => $intent->ownerId,
            'surfaceType' => $intent->surfaceType->value,
            'surfaceKey' => $intent->surfaceKey,
            'targetHost' => $intent->targetHost,
            'targetPath' => $intent->targetPath,
            'declarationStatus' => $binding->declaration()?->status()->value,
            'runtimeActivated' => false,
        ]);
    }
}
