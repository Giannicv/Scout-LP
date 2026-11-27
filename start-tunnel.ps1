<#
.SYNOPSIS
    Expone el servidor local de Laravel (127.0.0.1:8000) en un URL público de Cloudflare.
.DESCRIPTION
    Crea un "quick tunnel" (no requiere cuenta de Cloudflare). Reutiliza el túnel si ya
    está activo, espera ante un error 429 de aprovisionamiento y verifica la respuesta.
.EXAMPLE
    .\start-tunnel.ps1
    .\start-tunnel.ps1 -Restart   # detiene el túnel anterior y pide uno nuevo
#>
param(
    [int]$Port = 8000,
    [switch]$Restart
)

$ErrorActionPreference = 'Stop'
$project = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $project

$cloudflared = (Get-Command cloudflared -ErrorAction SilentlyContinue).Source
if (-not $cloudflared) {
    $cloudflared = 'C:\Program Files (x86)\cloudflared\cloudflared.exe'
}
if (-not (Test-Path $cloudflared)) {
    throw "No se encontró cloudflared.exe. Instálalo desde https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/"
}

$logDir = Join-Path $project 'storage\logs'
if (-not (Test-Path $logDir)) { New-Item -ItemType Directory -Path $logDir | Out-Null }
$log = Join-Path $logDir 'cloudflared.log'
$errLog = Join-Path $logDir 'cloudflared.err.log'

$tunnels = @(Get-CimInstance Win32_Process -Filter "Name = 'cloudflared.exe'" |
    Where-Object { $_.CommandLine -like "*--url http://127.0.0.1:$Port*" })

if ($tunnels.Count -gt 0 -and -not $Restart) {
    $url = Select-String -Path $errLog -Pattern 'https://[a-z0-9-]+\.trycloudflare\.com' -ErrorAction SilentlyContinue |
        Select-Object -Last 1
    Write-Host "Ya hay un túnel activo (PID $($tunnels[0].ProcessId))."
    if ($url) { Write-Host "URL: $($url.Matches.Value)" }
    Write-Host 'Usa -Restart para obtener una URL nueva.'
    exit 0
}

foreach ($tunnel in $tunnels) { Stop-Process -Id $tunnel.ProcessId -Force -ErrorAction SilentlyContinue }
Remove-Item $log, $errLog -ErrorAction SilentlyContinue

# El servidor de Laravel ya puede estar levantado (php artisan serve u otro).
$listener = Get-NetTCPConnection -State Listen -LocalAddress '127.0.0.1' -LocalPort $Port -ErrorAction SilentlyContinue
if (-not $listener) {
    $php = (Get-Command php -ErrorAction SilentlyContinue).Source
    if (-not $php) { $php = 'C:\xampp\php\php.exe' }
    if (-not (Test-Path $php)) { throw "No se encontró php.exe; no se puede iniciar el servidor en el puerto $Port." }
    Start-Process -FilePath $php -ArgumentList @('artisan', 'serve', '--host=127.0.0.1', "--port=$Port") `
        -WorkingDirectory $project -WindowStyle Hidden | Out-Null
    Start-Sleep -Seconds 3
}

$url = $null
for ($attempt = 1; $attempt -le 3 -and -not $url; $attempt++) {
    $process = Start-Process -FilePath $cloudflared `
        -ArgumentList @('tunnel', '--url', "http://127.0.0.1:$Port", '--loglevel', 'info') `
        -RedirectStandardOutput $log -RedirectStandardError $errLog -PassThru -WindowStyle Hidden

    for ($i = 1; $i -le 20; $i++) {
        Start-Sleep -Seconds 2
        $match = Select-String -Path $errLog -Pattern 'https://[a-z0-9-]+\.trycloudflare\.com' -ErrorAction SilentlyContinue |
            Select-Object -Last 1
        if ($match) { $url = $match.Matches.Value; break }

        # cloudflared limita cuántos túneles rápidos crea por minuto.
        if (Select-String -Path $errLog -Pattern '429|provisioning failed' -Quiet) { break }
        if ($process.HasExited) { break }
    }

    if (-not $url -and $attempt -lt 3) {
        Write-Host 'Cloudflare devolvió 429 (demasiados túneles rápidos). Esperando 30 s para reintentar...'
        Start-Sleep -Seconds 30
    }
}

if (-not $url) {
    if ($process -and -not $process.HasExited) { Stop-Process -Id $process.Id -Force }
    throw "No se pudo crear el túnel. Revisa $errLog y vuelve a intentar en unos minutos."
}

# El DNS del hostname recién creado puede tardar ~30 s en propagarse según el resolver local.
$status = 'sin verificar'
for ($i = 1; $i -le 12; $i++) {
    try {
        $response = Invoke-WebRequest -Uri $url -TimeoutSec 10 -UseBasicParsing
        $status = [int]$response.StatusCode
        break
    } catch {
        Start-Sleep -Seconds 5
    }
}

Write-Host ''
Write-Host '============================================================'
Write-Host "  URL pública: $url"
Write-Host "  HTTP:        $status"
Write-Host "  PID túnel:   $($process.Id)"
Write-Host "  Log:         $errLog"
Write-Host '  Detener:     Stop-Process -Id ' -NoNewline
Write-Host $process.Id
Write-Host '============================================================'
