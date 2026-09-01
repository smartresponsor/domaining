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

$tempRoot = Join-Path ([System.IO.Path]::GetTempPath()) ("domaining-w4-" + [System.Guid]::NewGuid().ToString('N'))
$extractRoot = Join-Path $tempRoot 'extract'
New-Item -ItemType Directory -Path $extractRoot | Out-Null

try {
    Expand-Archive -LiteralPath $TouchedArchive -DestinationPath $extractRoot -Force
    $payloadRoot = Join-Path $extractRoot 'Domaining'
    Assert-PathExists -Path $payloadRoot -Kind 'Payload root inside ZIP'

    $items = Get-ChildItem -LiteralPath $payloadRoot -Recurse -File
    foreach ($item in $items) {
        $relative = $item.FullName.Substring($payloadRoot.Length).TrimStart('\', '/')
        $target = Join-Path $RepositoryRoot $relative
        $targetDir = Split-Path -Parent $target
        if (-not (Test-Path -LiteralPath $targetDir)) {
            New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
        }
        Copy-Item -LiteralPath $item.FullName -Destination $target -Force
    }

    Write-Host "Domaining W4 touched files were overlaid successfully. No repository cleanup or deletion was performed."
}
finally {
    if (Test-Path -LiteralPath $tempRoot) {
        Remove-Item -LiteralPath $tempRoot -Recurse -Force
    }
}
