<?php

declare(strict_types=1);

namespace App\Domaining\Enum;

enum DomainRecordType: string
{
    case Txt = 'TXT';
    case Cname = 'CNAME';
    case A = 'A';
    case Aaaa = 'AAAA';
}
