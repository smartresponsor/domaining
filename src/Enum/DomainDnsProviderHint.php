<?php

declare(strict_types=1);

namespace App\Domaining\Enum;

enum DomainDnsProviderHint: string
{
    case Unknown = 'unknown';
    case Cloudflare = 'cloudflare';
    case Route53 = 'route53';
    case Namecheap = 'namecheap';
    case GoDaddy = 'godaddy';
    case GoogleCloudDns = 'google_cloud_dns';
    case AzureDns = 'azure_dns';
}
