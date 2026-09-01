param(
    [Parameter(Mandatory = $false)]
    [string] $RepositoryRoot = (Get-Location).Path,

    [Parameter(Mandatory = $false)]
    [string] $PayloadRoot = (Join-Path $PSScriptRoot '..')
)

$ErrorActionPreference = 'Stop'

function Assert-Directory([string] $Path, [string] $Label) {
    if (-not (Test-Path -LiteralPath $Path -PathType Container)) {
        throw "$Label path was not found: $Path"
    }
}

Assert-Directory $RepositoryRoot 'Repository root'
Assert-Directory $PayloadRoot 'Payload root'

$relativeFiles = @(
    'MANIFEST.json',
    'README.adoc',
    'config/services.yaml',
    'docs/api/domaining-endpoint-index.adoc',
    'docs/api/domaining-openapi.adoc',
    'docs/export/domain-state-export.adoc',
    'src/Command/DomainStateExportCommand.php',
    'src/Controller/DomainExportController.php',
    'src/Dto/DomainStateExportBinding.php',
    'src/Dto/DomainStateExportReport.php',
    'src/Service/Export/DomainStateExportService.php',
    'src/ServiceInterface/Export/DomainStateExportServiceInterface.php',
    'tool/apply-domaining-w10-touched.ps1'
)

foreach ($relativeFile in $relativeFiles) {
    $source = Join-Path $PayloadRoot $relativeFile
    $target = Join-Path $RepositoryRoot $relativeFile

    if (-not (Test-Path -LiteralPath $source -PathType Leaf)) {
        throw "Payload file was not found: $source"
    }

    $targetDirectory = Split-Path -Parent $target
    if (-not (Test-Path -LiteralPath $targetDirectory -PathType Container)) {
        New-Item -ItemType Directory -Path $targetDirectory -Force | Out-Null
    }

    Copy-Item -LiteralPath $source -Destination $target -Force
}

Write-Host 'Domaining W10 touched files were applied without repository cleanup.'
