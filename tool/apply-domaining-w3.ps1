param(
    [Parameter(Mandatory = $true)]
    [string] $RepositoryRoot,

    [Parameter(Mandatory = $false)]
    [string] $PayloadRoot = (Join-Path $PSScriptRoot '..')
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path -LiteralPath $RepositoryRoot)) {
    throw "Repository root was not found: $RepositoryRoot"
}

$sourceRoot = (Resolve-Path -LiteralPath $PayloadRoot).Path
$targetRoot = (Resolve-Path -LiteralPath $RepositoryRoot).Path

$relativeFiles = @(
    'config/services.yaml',
    'docs/administering/configuration-tool-discovery.adoc',
    'docs/api/domaining-openapi.adoc',
    'docs/interfacing/domain-template-surface.adoc',
    'docs/security/domain-takeover-guard.adoc',
    'migration/Version20260529000300.php',
    'src/Controller/DomainConfigurationController.php',
    'src/Controller/DomainPublicationController.php',
    'src/Dto/DomainConfigurationToolDescriptor.php',
    'src/Dto/DomainConfigurationVariable.php',
    'src/Enum/DomainConfigurationTarget.php',
    'src/Service/Configuration/DomainConfigurationToolMetadataService.php',
    'src/Service/Publication/DomainPublicationService.php',
    'src/ServiceInterface/Configuration/DomainConfigurationToolMetadataServiceInterface.php',
    'src/ServiceInterface/Publication/DomainPublicationServiceInterface.php',
    'tool/apply-domaining-w3.ps1'
)

foreach ($relative in $relativeFiles) {
    $source = Join-Path $sourceRoot $relative
    $target = Join-Path $targetRoot $relative

    if (-not (Test-Path -LiteralPath $source)) {
        throw "Payload file is missing: $source"
    }

    $targetDirectory = Split-Path -Parent $target
    if (-not (Test-Path -LiteralPath $targetDirectory)) {
        New-Item -ItemType Directory -Path $targetDirectory | Out-Null
    }

    Copy-Item -LiteralPath $source -Destination $target -Force
}

Write-Host 'Domaining W3 touched files were applied without deleting unrelated repository content.'
