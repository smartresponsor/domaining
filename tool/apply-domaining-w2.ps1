param(
    [Parameter(Mandatory = $true)]
    [string]$RepositoryRoot,

    [Parameter(Mandatory = $true)]
    [string]$ArchivePath
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path -LiteralPath $RepositoryRoot)) {
    throw "Repository root was not found: $RepositoryRoot"
}

if (-not (Test-Path -LiteralPath $ArchivePath)) {
    throw "Archive was not found: $ArchivePath"
}

$tempRoot = Join-Path ([System.IO.Path]::GetTempPath()) ('domaining-w2-' + [System.Guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $tempRoot | Out-Null
try {
    Expand-Archive -LiteralPath $ArchivePath -DestinationPath $tempRoot -Force
    $payloadRoot = Join-Path $tempRoot 'Domaining'
    if (-not (Test-Path -LiteralPath $payloadRoot)) {
        throw "Payload root inside ZIP path was not found: $payloadRoot"
    }

    Copy-Item -LiteralPath (Join-Path $payloadRoot '*') -Destination $RepositoryRoot -Recurse -Force
    Write-Host 'Domaining W2 touched-file overlay applied. Review git diff before commit.'
}
finally {
    Remove-Item -LiteralPath $tempRoot -Recurse -Force -ErrorAction SilentlyContinue
}
