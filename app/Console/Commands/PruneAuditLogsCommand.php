<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneAuditLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jadwalin:prune-audit-logs {--days=7 : Retensi log aktif dalam satuan hari}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft delete audit logs older than the retention period (default: 7 days) to keep dashboard clean.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $this->info("Menjalankan soft delete untuk audit log yang lebih lama dari {$days} hari (sebelum {$cutoff->toDateTimeString()})...");

        $deletedCount = AuditLog::where('created_at', '<', $cutoff)->delete();

        $message = sprintf('Berhasil me-soft delete %d entri audit log (lebih tua dari %d hari).', $deletedCount, $days);
        $this->info($message);
        Log::info("jadwalin:prune-audit-logs: {$message}");

        return self::SUCCESS;
    }
}
