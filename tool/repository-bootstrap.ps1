[CmdletBinding()]
param(
    [switch] $Apply
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$workspace = Split-Path -Parent $PSScriptRoot
$remote = 'git@github.com:smartresponsor/domaining.git'

Push-Location $workspace
try {
    $previous = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    $remoteOutput = & git ls-remote $remote 2>&1
    $remoteExit = $LASTEXITCODE
    $ErrorActionPreference = $previous

    Write-Host "Remote: $remote"
    Write-Host "Remote reachable: $($remoteExit -eq 0)"

    if (-not $Apply) {
        if ($remoteOutput) { $remoteOutput | Select-Object -First 8 | Write-Host }
        if ($remoteExit -ne 0) { exit 2 }
        exit 0
    }

    if (-not (Test-Path '.git')) {
        & git init -b master
        if ($LASTEXITCODE -ne 0) { throw 'git init failed.' }
    }

    $previous = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    $origin = & git remote get-url origin 2>$null
    $originExit = $LASTEXITCODE
    $ErrorActionPreference = $previous
    if ($originExit -ne 0 -or [string]::IsNullOrWhiteSpace([string] $origin)) {
        & git remote add origin $remote
        if ($LASTEXITCODE -ne 0) { throw 'Unable to add origin remote.' }
    }

    & git status --short --branch
    if ($LASTEXITCODE -ne 0) { throw 'git status failed.' }
}
finally {
    Pop-Location
}
