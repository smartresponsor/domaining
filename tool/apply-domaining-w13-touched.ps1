param(
    [Parameter(Mandatory = $true)]
    [string]$RepositoryRoot
)

$ErrorActionPreference = 'Stop'

function Assert-PathExists {
    param([string]$Path, [string]$Kind)
    if (-not (Test-Path -LiteralPath $Path)) {
        throw "$Kind path was not found: $Path"
    }
}

$repo = Resolve-Path -LiteralPath $RepositoryRoot
$scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$payloadRoot = Resolve-Path -LiteralPath (Join-Path $scriptRoot '..')

Assert-PathExists -Path $repo -Kind 'Repository root'
Assert-PathExists -Path (Join-Path $payloadRoot 'src') -Kind 'Payload src'

$relativeFiles = @(
    'src/Dto/DomainReleaseReviewIssue.php',
    'src/Dto/DomainReleaseReviewReport.php',
    'src/ServiceInterface/Review/DomainReleaseReviewServiceInterface.php',
    'src/Service/Review/DomainReleaseReviewService.php',
    'src/Controller/DomainReviewController.php',
    'src/Command/DomainReleaseReviewCommand.php',
    'config/services.yaml',
    'docs/api/domaining-endpoint-index.adoc',
    'docs/api/domaining-openapi.adoc',
    'docs/contract/domain-contract-governance.adoc',
    'docs/manifest/domain-release-manifest.adoc',
    'docs/release/release-review.adoc',
    'docs/operation/w13-release-review.adoc',
    'MANIFEST.json',
    'README.adoc'
)

foreach ($relative in $relativeFiles) {
    $source = Join-Path $payloadRoot $relative
    $target = Join-Path $repo $relative
    Assert-PathExists -Path $source -Kind "Payload file $relative"
    $targetDir = Split-Path -Parent $target
    if (-not (Test-Path -LiteralPath $targetDir)) {
        New-Item -ItemType Directory -Path $targetDir | Out-Null
    }
    Copy-Item -LiteralPath $source -Destination $target -Force
}

Write-Host 'Domaining W13 touched files applied without deleting repository content.'
