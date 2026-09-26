<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Configuration\DomainConfigurationToolMetadataServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DomainConfigurationController extends AbstractController
{
    #[Route('/domain/configuration/tool/metadata', name: 'domain_configuration_tool_metadata', methods: ['GET'])]
    public function toolMetadata(DomainConfigurationToolMetadataServiceInterface $metadataService): JsonResponse
    {
        return $this->json($metadataService->descriptor()->toArray());
    }
}
