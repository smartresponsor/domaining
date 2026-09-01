<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\Repository\DomainBindingRepository;
use App\Domaining\ServiceInterface\Interfacing\DomainInterfacingPayloadServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/domain/surface', name: 'domain_surface_')]
final class DomainSurfaceController extends AbstractController
{
    #[Route('/{bindingId}', name: 'binding', methods: ['GET'])]
    public function binding(
        string $bindingId,
        DomainBindingRepository $bindingRepository,
        DomainInterfacingPayloadServiceInterface $payloadService,
    ): JsonResponse {
        $binding = $bindingRepository->find(Uuid::fromString($bindingId));
        if (null === $binding) {
            return $this->json(['message' => 'Domain binding was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($payloadService->payloadForBinding($binding)->toArray());
    }

    #[Route('/{bindingId}/render', name: 'binding_render', methods: ['GET'])]
    public function renderBinding(
        string $bindingId,
        DomainBindingRepository $bindingRepository,
        DomainInterfacingPayloadServiceInterface $payloadService,
    ): JsonResponse|array {
        $binding = $bindingRepository->find(Uuid::fromString($bindingId));
        if (null === $binding) {
            return $this->json(['message' => 'Domain binding was not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return [
            '_view' => [
                'surface' => 'domain',
                'operation' => 'binding',
                'component' => 'Domaining',
                'intent' => 'surface',
            ],
            'locations' => $payloadService->payloadForBinding($binding)->locations,
            'data' => [
                'binding' => $payloadService->payloadForBinding($binding)->toArray(),
            ],
            'meta' => [
                'source_controller' => self::class,
            ],
        ];
    }

}

