<?php

namespace App\Services;

use App\Models\WhatsappGroup;
use App\Models\WhatsappLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    /**
     * Kirim pesan teks ke WhatsApp Group
     * (TIDAK ADA PERUBAHAN)
     *
     * @param string $message
     * @param string|null $groupCode (Opsional, jika ingin mengirim ke grup spesifik)
     * @return bool
     */
    public function sendGroupMessage(string $message, ?string $groupCode = null): bool
    {
        // Ambil target grup WhatsApp dari database
        $targetGroup = null;

        if ($groupCode) {
            $targetGroup = WhatsappGroup::where('code_group', $groupCode)->first();
        }

        // Jika groupCode tidak ditemukan atau tidak dikirim, ambil default (misal: QCO Group atau first record)
        if (!$targetGroup) {
            $targetGroup = WhatsappGroup::where('code_group', '120363431380455863@g.us')->first() ?? WhatsappGroup::first();
        }

        if (!$targetGroup) {
            Log::error("Gagal mengirim WA: Belum ada data Grup WhatsApp yang tersimpan di database.");
            return false;
        }

        $waTargetGroup = $targetGroup->code_group;
        $gatewayUrl = env('WA_GATEWAY_URL', 'http://localhost:50521');

        try {
            $response = Http::withHeaders([
                'x-api-key' => env('WA_GATEWAY_KEY', 'my-secret-key')
            ])->post($gatewayUrl . '/send-text', [
                'target' => $waTargetGroup,
                'message' => $message
            ]);

            if ($response->successful()) {
                WhatsappLog::create([
                    'target_group' => $waTargetGroup,
                    'message' => $message,
                    'message_id' => $response->json('message_id') ?? null,
                    'status' => 'sent'
                ]);
                return true;
            } else {
                WhatsappLog::create([
                    'target_group' => $waTargetGroup,
                    'message' => $message,
                    'status' => 'failed'
                ]);
                Log::error("Gagal mengirim WA Gateway: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error Koneksi ke WA Gateway: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Menghapus pesan WhatsApp di aplikasi via Node.js berdasarkan message_id,
     * lalu membersihkan data log di database.
     */
    public function deleteDailyWhatsAppLogs()
    {
        $logs = WhatsappLog::all();

        if ($logs->isEmpty()) {
            Log::info("Tidak ada log WhatsApp yang perlu dibersihkan.");
            return;
        }

        $gatewayUrl = env('WA_GATEWAY_URL', 'http://localhost:50521');
        $apiKey = env('WA_GATEWAY_KEY', 'my-secret-key');

        foreach ($logs as $log) {
            if (!empty($log->message_id)) {
                try {
                    // Kirim request DELETE ke server Node.js untuk hapus pesan di WhatsApp
                    $response = Http::withHeaders([
                        'x-api-key' => $apiKey,
                    ])->delete($gatewayUrl . '/delete-message', [
                        'message_id' => $log->message_id,
                    ]);

                    if ($response->successful()) {
                        Log::info("Berhasil menghapus pesan WhatsApp ID: {$log->message_id}");
                        // Hapus baris log setelah sukses terhapus di WA
                        $log->delete();
                    } else {
                        Log::warning("Gagal menghapus pesan WhatsApp ID: {$log->message_id} - " . $response->body());
                    }
                } catch (\Exception $e) {
                    Log::error("Error koneksi saat menghapus pesan WA ID {$log->message_id}: " . $e->getMessage());
                }
            } else {
                // Jika message_id kosong, langsung hapus baris log-nya
                $log->delete();
            }
        }

        // Opsional: Jika ingin memastikan tabel benar-benar bersih total setelah perulangan
        // WhatsappLog::truncate();

        Log::info("Proses pembersihan pesan WhatsApp dan table whatsapp_logs selesai.");
    }
}
