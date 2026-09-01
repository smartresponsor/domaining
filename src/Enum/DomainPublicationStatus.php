<?php

declare(strict_types=1);

namespace App\Domaining\Enum;

enum DomainPublicationStatus: string
{
    case NotReady = 'not_ready';
    case Ready = 'ready';
    case Published = 'published';
    case Failed = 'failed';
    case Withdrawn = 'withdrawn';
}
