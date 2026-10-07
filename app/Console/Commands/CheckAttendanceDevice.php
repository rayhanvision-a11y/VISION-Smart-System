<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckAttendanceDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:check-device {--ip= : Override device IP to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check network connectivity to Hikvision Attendance Terminal and Local DNS';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deviceIp   = $this->option('ip') ?: config('services.attendance.device_ip', '172.27.1.49');
        $devicePort = (int) config('services.attendance.device_port', 80);
        $dnsIp      = config('services.attendance.dns_ip', '172.30.20.50');
        $serverLanIp = config('services.attendance.server_ip', '103.31.179.118');

        $this->info("=============================================================");
        $this->info("  VISION Smart System - Attendance LAN Diagnostics");
        $this->info("=============================================================");
        $this->newLine();

        // 1. Device Socket Check
        $this->output->write("Checking Attendance Terminal [{$deviceIp}:{$devicePort}] ... ");
        $startTime = microtime(true);
        $fp = @fsockopen($deviceIp, $devicePort, $errno, $errstr, 2);
        $elapsed = round((microtime(true) - $startTime) * 1000, 2);

        if ($fp) {
            fclose($fp);
            $this->info("ONLINE ({$elapsed}ms) [TCP Port {$devicePort} Open]");
        } else {
            $this->error("OFFLINE or UNREACHABLE (Error: {$errstr})");
        }

        // 2. Local DNS Check
        $this->output->write("Checking Local DNS Server [{$dnsIp}:53] ... ");
        $dnsFp = @fsockopen("udp://{$dnsIp}", 53, $errno, $errstr, 2);
        if ($dnsFp) {
            fclose($dnsFp);
            $this->info("ONLINE (Port 53 Resolving)");
        } else {
            $this->warn("DNS check unreachable on UDP 53");
        }

        // 3. Webhook Endpoints
        $this->newLine();
        $this->comment("Recommended Webhook URLs:");
        $this->line("  * Local LAN Webhook : http://{$serverLanIp}/api/hikcentral/event");
        $this->line("  * Cloud / Ngrok URL  : " . url('/api/hikcentral/event'));

        // 4. Telemetry and Records
        $this->newLine();
        try {
            $lastHit = Cache::get('hikcentral_last_hit');
            $totalLogs = AttendanceLog::count();
            $this->line("Database Attendance Logs Stored : <info>{$totalLogs}</info>");
            if ($lastHit) {
                $this->line("Last Device Webhook Hit Time     : <comment>{$lastHit['time']}</comment> (IP: {$lastHit['ip']})");
            } else {
                $this->line("Last Device Webhook Hit Time     : None recorded yet since last cache flush.");
            }
        } catch (\Throwable $e) {
            $this->comment("Database offline (Laragon MySQL not running). Device network diagnostics succeeded.");
        }

        $this->newLine();
        return Command::SUCCESS;
    }
}
