<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Review;

use App\Domaining\DTO\DomainReleaseReviewReportDTO;

interface DomainReleaseReviewServiceInterface
{
    public function buildReport(): DomainReleaseReviewReportDTO;
}
