<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth; // 1. Import facade Auth

class qco___status extends Model
{
     protected $table = 'qco___status';
    protected $fillable = ['id_qco', 'actual_change_over', 'status','user_update'];
     /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        // Otomatis isi saat data baru dibuat (Create)
        static::creating(function ($model) {
            if (Auth::check()) {
                // Sesuaikan 'nik' dengan nama kolom NIK pada tabel users Anda
                $model->user_update = Auth::user()->nik;
            }
        });

        // (Opsional) Jika ingin otomatis terupdate juga saat data diubah (Update)
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->user_update = Auth::user()->nik;
            }
        });
    }
    // Relasi ke qco
    public function qco(): BelongsTo
    {
        return $this->belongsTo(qco::class, 'id_qco', 'id');
    }
}
