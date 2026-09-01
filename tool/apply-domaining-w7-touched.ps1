param(
    [Parameter(Mandatory=$true)]
    [string]$RepositoryRoot,

    [Parameter(Mandatory=$true)]
    [string]$TouchedArchive
)

$ErrorActionPreference = 'Stop'

function Assert-PathExists {
    param([string]$Path, [string]$Kind)
    if (-not (Test-Path -LiteralPath $Path)) {
        throw "$Kind path was not found: $Path"
    }
}

Assert-PathExists -Path $RepositoryRoot -Kind 'Repository root'
Assert-PathExists -Path $TouchedArchive -Kind 'Touched archive'

$tempRoot = Join-Path ([System.IO.Path]::GetTempPath()) ('domaining-w7-' + [System.Guid]::NewGuid().ToString('N'))
$extractRoot = Join-Path $tempRoot 'extract'
New-Item -ItemType Directory -Path $extractRoot | Out-Null

try {
    Expand-Archive -LiteralPath $TouchedArchive -DestinationPath $extractRoot -Force
    $payloadRoot = Join-Path $extractRoot 'Domaining'
    Assert-PathExists -Path $payloadRoot -Kind 'Payload root inside ZIP'

    Get-ChildItem -LiteralPath $payloadRoot -Recurse -File | ForEach-Object {
        $relative = $_.FullName.Substring($payloadRoot.Length).TrimStart('\', '/')
        $target = Join-Path $RepositoryRoot $relative
        $targetDirectory = Split-Path -Parent $target
        if (-not (Test-Path -LiteralPath $targetDirectory)) {
            New-Item -ItemType Directory -Path $targetDirectory -Force | Out-Null
        }
        Copy-Item -LiteralPath $_.FullName -Destination $target -Force
    }

    Write-Host 'Domaining W7 touched files applied without repository cleanup.'
}
finally {
    if (Test-Path -LiteralPath $tempRoot) {
        Remove-Item -LiteralPath $tempRoot -Recurse -Force
    }
}
