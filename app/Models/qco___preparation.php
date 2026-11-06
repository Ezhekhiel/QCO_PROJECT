<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth; // 1. Import facade Auth

class qco___preparation extends Model
{
    use HasFactory;

    protected $table = 'qco___preparation'; // Sesuaikan dengan nama tabel di database
    protected $fillable = ['id_qco', 'id_list','user_update'];

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

    // Relasi ke qco___preparation_list
    public function preparation_list(): BelongsTo
    {
        return $this->belongsTo(qco___preparation_list::class, 'id_list', 'id');
    }
    public function preparation_file(): HasMany
    {
        return $this->hasMany(qco___preparation_files::class, 'preparation_id', 'id');
    }

    // Relasi ke qco
    public function qco(): BelongsTo
    {
        return $this->belongsTo(qco::class, 'id_qco', 'id');
    }
}

