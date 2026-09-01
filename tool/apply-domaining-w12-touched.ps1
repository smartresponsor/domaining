param(
    [Parameter(Mandatory = $true)]
    [string] $TargetRoot,

    [Parameter(Mandatory = $true)]
    [string] $TouchedZip
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path -LiteralPath $TargetRoot)) {
    throw "Target root was not found: $TargetRoot"
}

if (-not (Test-Path -LiteralPath $TouchedZip)) {
    throw "Touched zip was not found: $TouchedZip"
}

$tempRoot = Join-Path ([System.IO.Path]::GetTempPath()) ("domaining-w12-" + [System.Guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $tempRoot | Out-Null
try {
    Expand-Archive -LiteralPath $TouchedZip -DestinationPath $tempRoot -Force
    $payloadRoot = Join-Path $tempRoot 'Domaining'
    if (-not (Test-Path -LiteralPath $payloadRoot)) {
        throw "Payload root inside ZIP path was not found: $payloadRoot"
    }

    Get-ChildItem -LiteralPath $payloadRoot -Recurse -File | ForEach-Object {
        $relative = $_.FullName.Substring($payloadRoot.Length).TrimStart('\','/')
        $destination = Join-Path $TargetRoot $relative
        $destinationDirectory = Split-Path -Parent $destination
        if (-not (Test-Path -LiteralPath $destinationDirectory)) {
            New-Item -ItemType Directory -Path $destinationDirectory | Out-Null
        }
        Copy-Item -LiteralPath $_.FullName -Destination $destination -Force
    }
}
finally {
    if (Test-Path -LiteralPath $tempRoot) {
        Remove-Item -LiteralPath $tempRoot -Recurse -Force
    }
}

Write-Host 'Domaining W12 touched files were overlaid successfully. No repository cleanup or destructive delete was performed.'
