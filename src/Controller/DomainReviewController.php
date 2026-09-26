<?php

declare(strict_types=1);

namespace App\Domaining\Controller;

use App\Domaining\ServiceInterface\Review\DomainReleaseReviewServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DomainReviewController extends AbstractController
{
    #[Route('/domain/release/review', name: 'domain_release_review_review', methods: ['GET'])]
    public function review(DomainReleaseReviewServiceInterface $reviewService): JsonResponse
    {
        return $this->json(['domainReleaseReview' => $reviewService->buildReport()->toArray()]);
    }
}
