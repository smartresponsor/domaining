<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\ServiceInterface\Publication\DomainPublicationReadServiceInterface;
use App\Domaining\ServiceInterface\Publication\DomainPublicationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/domain/publication', name: 'domain_publication_')]
final class DomainPublicationController extends AbstractController
{
    #[Route('/{bindingId}/snapshot', name: 'snapshot', methods: ['GET'])]
    public function snapshot(
        string $bindingId,
        DomainBindingRepository $bindingRepository,
        DomainPublicationReadServiceInterface $readService,
    ): JsonResponse {
        $binding = $bindingRepository->find(Uuid::fromString($bindingId));
        if (null === $binding) {
            return $this->json(['message' => 'Domain binding was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($readService->snapshot($binding)->toArray());
    }

    #[Route('/{bindingId}/ready', name: 'ready', methods: ['POST'])]
    public function ready(
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
            'publicationStatus' => 'ready',
            'declarationStatus' => $binding->declaration()?->status()->value,
            'runtimeActivated' => false,
        ]);
    }

    #[Route('/{bindingId}/published', name: 'published', methods: ['POST'])]
    public function published(
        string $bindingId,
        DomainBindingRepository $bindingRepository,
        DomainPublicationServiceInterface $publicationService,
    ): JsonResponse {
        $binding = $bindingRepository->find(Uuid::fromString($bindingId));
        if (null === $binding) {
            return $this->json(['message' => 'Domain binding was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($publicationService->markPublished($binding)->toArray());
    }

    #[Route('/{bindingId}/withdrawn', name: 'withdrawn', methods: ['POST'])]
    public function withdrawn(
        string $bindingId,
        DomainBindingRepository $bindingRepository,
        DomainPublicationServiceInterface $publicationService,
    ): JsonResponse {
        $binding = $bindingRepository->find(Uuid::fromString($bindingId));
        if (null === $binding) {
            return $this->json(['message' => 'Domain binding was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($publicationService->markWithdrawn($binding)->toArray());
    }
}

