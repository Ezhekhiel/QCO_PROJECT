<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsappService; // Sesuaikan dengan namespace service Anda

class DeleteWhatsAppLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:delete-daily-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus pesan WhatsApp dan membersihkan log harian';

    /**
     * Execute the console command.
     */
    public function handle(WhatsappService $whatsappService)
    {
        $this->info('Memulai proses pembersihan log WhatsApp...');

        try {
            $whatsappService->deleteDailyWhatsAppLogs();
            $this->info('Proses pembersihan log WhatsApp berhasil diselesaikan.');
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
