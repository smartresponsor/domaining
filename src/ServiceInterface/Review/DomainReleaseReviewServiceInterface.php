<?php

declare(strict_types=1);

namespace App\Domaining\ServiceInterface\Review;

use App\Domaining\Dto\DomainReleaseReviewReport;

interface DomainReleaseReviewServiceInterface
{
    public function buildReport(): DomainReleaseReviewReport;
}
