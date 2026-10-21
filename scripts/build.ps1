$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$pluginRoot = Join-Path $projectRoot 'ubigi-plans-demo'
$outputRoot = Join-Path $projectRoot 'dist'
New-Item -ItemType Directory -Force $outputRoot | Out-Null
$zipPath = Join-Path $outputRoot 'ubigi-plans-demo.zip'
Compress-Archive -LiteralPath $pluginRoot -DestinationPath $zipPath -Force
Get-FileHash -LiteralPath $zipPath -Algorithm SHA256
