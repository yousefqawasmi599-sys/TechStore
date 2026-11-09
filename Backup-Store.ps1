$ErrorActionPreference = 'Stop'
$clientPath = Join-Path $PSScriptRoot '.runtime\admin-client.ini'
$backupPath = Join-Path $PSScriptRoot 'backups'
if (!(Test-Path $clientPath)) { throw 'Initialize the local database first.' }
New-Item -ItemType Directory -Path $backupPath -Force | Out-Null
$outputPath = Join-Path $backupPath ('tech-store-' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.sql')
& 'C:\xampp\mysql\bin\mysqldump.exe' "--defaults-file=$clientPath" --single-transaction "--result-file=$outputPath" tech_store
if ($LASTEXITCODE -ne 0) { throw 'Database backup failed.' }
Write-Output "Backup saved: $outputPath"
