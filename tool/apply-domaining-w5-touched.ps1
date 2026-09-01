param(
    [Parameter(Mandatory = $true)]
    [string] $RepositoryRoot,

    [Parameter(Mandatory = $true)]
    [string] $TouchedArchive
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path -LiteralPath $RepositoryRoot)) {
    throw "Repository root was not found: $RepositoryRoot"
}

if (-not (Test-Path -LiteralPath $TouchedArchive)) {
    throw "Touched archive was not found: $TouchedArchive"
}

$tempRoot = Join-Path ([System.IO.Path]::GetTempPath()) ("domaining-w5-touched-" + [System.Guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $tempRoot | Out-Null

try {
    Expand-Archive -LiteralPath $TouchedArchive -DestinationPath $tempRoot -Force
    $payloadRoot = Join-Path $tempRoot 'Domaining'

    if (-not (Test-Path -LiteralPath $payloadRoot)) {
        throw "Payload root inside ZIP path was not found: $payloadRoot"
    }

    Get-ChildItem -LiteralPath $payloadRoot -Recurse -File | ForEach-Object {
        $relative = $_.FullName.Substring($payloadRoot.Length).TrimStart('\', '/')
        $target = Join-Path $RepositoryRoot $relative
        $targetDirectory = Split-Path -Parent $target

        if (-not (Test-Path -LiteralPath $targetDirectory)) {
            New-Item -ItemType Directory -Path $targetDirectory | Out-Null
        }

        Copy-Item -LiteralPath $_.FullName -Destination $target -Force
    }
}
finally {
    if (Test-Path -LiteralPath $tempRoot) {
        Remove-Item -LiteralPath $tempRoot -Recurse -Force
    }
}

Write-Host 'Domaining W5 touched files were applied without destructive repository cleanup.'
