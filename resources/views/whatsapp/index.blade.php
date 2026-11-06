@extends('layouts.main')

@section('content')
    <div class="app-content-header py-3">
        <div class="container-fluid">
            <h3 class="mb-0">WhatsApp Management & Tracking Log</h3>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <!-- Kolom Kiri: Daftar Grup WhatsApp -->
                <div class="col-md-5">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Grup WhatsApp</h5>
                            <a href="{{ route('whatsapp.sync') }}" class="btn btn-sm btn-primary">Sync dari WA Gateway</a>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0 text-center">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Grup</th>
                                        <th>Group ID (JID)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($groups as $i => $group)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td class="text-start">{{ $group->name }}</td>
                                            <td><small class="text-muted">{{ $group->code_group }}</small></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">Belum ada grup tersimpan.
                                                Silakan Sync atau ketik <code>daftar group | Nama Grup</code> di WhatsApp.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Tabel Log Pesan Terkirim -->
                <div class="col-md-7">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Tracking Log Pesan Terkirim</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0 text-center"
                                    style="font-size: 0.9rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Target Grup</th>
                                            <th>Isi Pesan</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($logs as $log)
                                            <tr>
                                                <td><small>{{ $log->created_at->format('d/m/Y H:i') }}</small></td>
                                                <td><small>{{ $log->target_group }}</small></td>
                                                <td class="text-start" style="max-width: 200px; word-break: break-word;">
                                                    {{ Str::limit($log->message, 80) }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">{{ $log->status }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">Belum ada riwayat
                                                    pesan terkirim.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            {{ $logs->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
