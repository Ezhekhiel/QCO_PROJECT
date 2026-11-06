<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WhatsappGroup;
use App\Models\WhatsappLog;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    // 1. Tampilkan Halaman Daftar Grup & Log Pesan
    public function index()
    {
        $groups = WhatsappGroup::all();
        $logs = WhatsappLog::latest()->paginate(10); // Data log pesan untuk tracking

        return view('whatsapp.index', compact('groups', 'logs'));
    }

    // 2. Sync / Ambil Grup dari Node.js WA Gateway
    public function syncGroups()
    {
        try {
            $gatewayUrl = env('WA_GATEWAY_URL', 'http://localhost:50521');

            // Tambahkan x-api-key di headers agar lolos middleware Node.js
            $response = Http::withHeaders([
                'x-api-key' => env('WA_GATEWAY_KEY', 'my-secret-key')
            ])->get($gatewayUrl . '/groups');

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['data'])) {
                    foreach ($result['data'] as $group) {
                        WhatsappGroup::updateOrCreate(
                            ['code_group' => $group['id']],
                            ['name' => $group['name']]
                        );
                    }
                }
                return back()->with('success', 'Berhasil menyinkronkan grup WhatsApp!');
            }

            return back()->with('error', 'Gagal mengambil data dari Node.js Gateway. (Respon: ' . $response->body() . ')');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan koneksi: ' . $e->getMessage());
        }
    }

    // 3. Endpoint yang ditangkap oleh Node.js saat user ketik "daftar group | Nama Grup"
    public function saveGroupWebhook(Request $request)
    {
        // Sesuaikan dengan env WA_GATEWAY_KEY yang Anda miliki
        $apiKey = env('WA_GATEWAY_KEY', 'my-secret-key');

        if ($request->header('x-api-key') !== $apiKey) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'code_group' => 'required',
            'name' => 'required',
        ]);

        WhatsappGroup::updateOrCreate(
            ['code_group' => $request->code_group],
            ['name' => $request->name]
        );

        return response()->json(['success' => true, 'message' => 'Grup berhasil disimpan ke database Laravel!']);
    }
}
