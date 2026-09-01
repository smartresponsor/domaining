param(
    [Parameter(Mandatory=$true)]
    [string]$TargetRoot
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path $TargetRoot)) {
    New-Item -ItemType Directory -Path $TargetRoot | Out-Null
}

$SourceRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
Write-Host "Applying Domaining wave 1 overlay from $SourceRoot to $TargetRoot"

Get-ChildItem -Path $SourceRoot -Recurse -File | Where-Object {
    $_.FullName -notlike '*\.git\*' -and
    $_.FullName -notlike '*\tool\apply-domaining-w1.ps1'
} | ForEach-Object {
    $relative = $_.FullName.Substring($SourceRoot.Path.Length).TrimStart('\', '/')
    $destination = Join-Path $TargetRoot $relative
    $destinationDirectory = Split-Path $destination -Parent
    if (-not (Test-Path $destinationDirectory)) {
        New-Item -ItemType Directory -Path $destinationDirectory -Force | Out-Null
    }
    Copy-Item -Path $_.FullName -Destination $destination -Force
}

Write-Host 'Domaining wave 1 overlay applied. No repository cleanup or destructive delete was performed.'
