$ErrorActionPreference = 'Stop'
$phpPath = 'C:\xampp\php\php.exe'
$runtimePath = Join-Path $PSScriptRoot '.runtime'
& $phpPath (Join-Path $PSScriptRoot 'tools\prepare-local.php')
if ($LASTEXITCODE -ne 0) { throw 'Could not prepare local credentials.' }
$settings = Get-Content (Join-Path $runtimePath 'admin.json') -Raw | ConvertFrom-Json
$dataPath = Join-Path $runtimePath 'mysql'
if (!(Test-Path (Join-Path $dataPath 'my.ini'))) {
    & 'C:\xampp\mysql\bin\mysql_install_db.exe' "--datadir=$dataPath" "--password=$($settings.password)" --port=3310 --silent
    if ($LASTEXITCODE -ne 0) { throw 'Database initialization failed.' }
}
$client = [Net.Sockets.TcpClient]::new()
try { $task=$client.ConnectAsync('127.0.0.1',3310); $running=$task.Wait(500) -and $client.Connected } catch { $running=$false } finally { $client.Dispose() }
if (!$running) {
    $iniPath=Join-Path $dataPath 'my.ini'
    Start-Process 'C:\xampp\mysql\bin\mysqld.exe' -ArgumentList ('--defaults-file="'+$iniPath+'"'),'--standalone','--bind-address=127.0.0.1','--skip-name-resolve' -WorkingDirectory $PSScriptRoot -WindowStyle Hidden
}
$deadline=(Get-Date).AddSeconds(20)
do {
    & $phpPath (Join-Path $PSScriptRoot 'tools\import-local.php') 2>$null
    if($LASTEXITCODE -eq 0) { break }
    if((Get-Date) -gt $deadline) { throw 'Database import failed. Check the database log and backup file.' }
    Start-Sleep -Milliseconds 500
} while ($true)
& $phpPath (Join-Path $PSScriptRoot 'tools\migrate-local.php')
if ($LASTEXITCODE -ne 0) { throw 'Database migration failed.' }
& (Join-Path $PSScriptRoot 'Start-Store.ps1')
