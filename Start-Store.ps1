$ErrorActionPreference = 'Stop'
$phpPath = 'C:\xampp\php\php.exe'
$mysqlPath = 'C:\xampp\mysql\bin\mysqld.exe'
if (!(Test-Path -LiteralPath $phpPath)) { throw 'XAMPP PHP was not found in C:\xampp\php.' }
function Test-LocalPort([int]$Port) {
    $client = [Net.Sockets.TcpClient]::new()
    try { $task=$client.ConnectAsync('127.0.0.1',$Port); return $task.Wait(500) -and $client.Connected } catch { return $false } finally { $client.Dispose() }
}
$iniPath = Join-Path $PSScriptRoot '.runtime\mysql\my.ini'
if (!(Test-Path -LiteralPath $iniPath)) { throw 'Local database is not initialized. See README.md.' }
if (!(Test-LocalPort 3310)) {
    Start-Process -FilePath $mysqlPath -ArgumentList ('--defaults-file="'+$iniPath+'"'),'--standalone','--bind-address=127.0.0.1','--skip-name-resolve' -WorkingDirectory $PSScriptRoot -WindowStyle Hidden
}
$deadline = (Get-Date).AddSeconds(20)
while (!(Test-LocalPort 3310)) {
    if ((Get-Date) -gt $deadline) { throw 'Database startup timed out. Check .runtime/mysql/*.err.' }
    Start-Sleep -Milliseconds 250
}
if (!(Test-LocalPort 8000)) {
    $documentRoot = Join-Path $PSScriptRoot 'public'
    Start-Process -FilePath $phpPath -ArgumentList '-S','127.0.0.1:8000','-t',('"' + $documentRoot + '"') -WorkingDirectory $PSScriptRoot -WindowStyle Hidden -RedirectStandardOutput (Join-Path $PSScriptRoot '.runtime/php.log') -RedirectStandardError (Join-Path $PSScriptRoot '.runtime/php-error.log')
}
$deadline = (Get-Date).AddSeconds(10)
do {
    try { $response=Invoke-WebRequest 'http://127.0.0.1:8000/' -UseBasicParsing -TimeoutSec 2; if($response.StatusCode -eq 200 -and $response.Content.Contains('TechStore')) { break } } catch {}
    if ((Get-Date) -gt $deadline) { throw 'Store did not respond. Check the local PHP log and port 8000.' }
    Start-Sleep -Milliseconds 250
} while ($true)
Write-Output 'TechStore: http://127.0.0.1:8000/'
