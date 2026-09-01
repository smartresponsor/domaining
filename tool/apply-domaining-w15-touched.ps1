param(
    [Parameter(Mandatory = $true)]
    [string] $RepositoryRoot,

    [Parameter(Mandatory = $true)]
    [string] $TouchedArchive
)

$ErrorActionPreference = 'Stop'

function Assert-PathExists {
    param([string] $Path, [string] $Kind)
    if (-not (Test-Path -LiteralPath $Path)) {
        throw "$Kind path was not found: $Path"
    }
}

Assert-PathExists -Path $RepositoryRoot -Kind 'Repository root'
Assert-PathExists -Path $TouchedArchive -Kind 'Touched archive'

$tempRoot = Join-Path ([System.IO.Path]::GetTempPath()) ('domaining-w15-' + [System.Guid]::NewGuid().ToString('N'))
$extractRoot = Join-Path $tempRoot 'extract'
New-Item -ItemType Directory -Path $extractRoot | Out-Null

try {
    Expand-Archive -LiteralPath $TouchedArchive -DestinationPath $extractRoot -Force

    Get-ChildItem -LiteralPath $extractRoot -Recurse -File | ForEach-Object {
        $relativePath = $_.FullName.Substring($extractRoot.Length).TrimStart([System.IO.Path]::DirectorySeparatorChar, [System.IO.Path]::AltDirectorySeparatorChar)
        $targetPath = Join-Path $RepositoryRoot $relativePath
        $targetDirectory = Split-Path -Parent $targetPath

        if (-not (Test-Path -LiteralPath $targetDirectory)) {
            New-Item -ItemType Directory -Path $targetDirectory -Force | Out-Null
        }

        Copy-Item -LiteralPath $_.FullName -Destination $targetPath -Force
    }
}
finally {
    if (Test-Path -LiteralPath $tempRoot) {
        Remove-Item -LiteralPath $tempRoot -Recurse -Force
    }
}

Write-Host 'Domaining W15 touched files were overlaid successfully.'
