param(
    [Parameter(Mandatory=$true)]
    [string]$RepositoryRoot,
    [Parameter(Mandatory=$true)]
    [string]$TouchedZip
)

$ErrorActionPreference = 'Stop'

function Assert-PathExists([string]$Path, [string]$Kind) {
    if (-not (Test-Path -LiteralPath $Path)) {
        throw "$Kind path was not found: $Path"
    }
}

Assert-PathExists -Path $RepositoryRoot -Kind 'Repository root'
Assert-PathExists -Path $TouchedZip -Kind 'Touched ZIP'

$tempRoot = Join-Path ([System.IO.Path]::GetTempPath()) ('domaining-w6-' + [System.Guid]::NewGuid().ToString('N'))
$extractRoot = Join-Path $tempRoot 'extract'
New-Item -ItemType Directory -Path $extractRoot | Out-Null
Expand-Archive -LiteralPath $TouchedZip -DestinationPath $extractRoot -Force

$payloadRoot = Join-Path $extractRoot 'Domaining'
Assert-PathExists -Path $payloadRoot -Kind 'Payload root inside ZIP'

Get-ChildItem -LiteralPath $payloadRoot -Recurse -File | ForEach-Object {
    $relative = $_.FullName.Substring($payloadRoot.Length).TrimStart('\\','/')
    $target = Join-Path $RepositoryRoot $relative
    $targetDir = Split-Path -Parent $target
    if (-not (Test-Path -LiteralPath $targetDir)) {
        New-Item -ItemType Directory -Path $targetDir | Out-Null
    }
    Copy-Item -LiteralPath $_.FullName -Destination $target -Force
}

Write-Host 'Domaining W6 touched files applied without deleting repository content.'
