<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth; // 1. Import facade Auth

class qco___preparation_files extends Model
{
    use HasFactory;

    protected $table = 'qco___preparation_files';
    protected $fillable = ['preparation_id', 'file', 'user_update'];

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

    // Relasi balik ke qco___preparation
    public function preparation(): BelongsTo
    {
        return $this->belongsTo(qco___preparation::class, 'preparation_id', 'id');
    }
}
