<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class qco extends Model
{
   use HasFactory;

    protected $table = 'qco';
    protected $fillable = ['nik', 'change_over_date', 'completion_date', 'cell', 'description','model', 'stage', 'completion', 'status'];

    // Relasi ke User berdasarkan NIK
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nik', 'nik');
    }
    public function cell_info(): BelongsTo
    {
        return $this->belongsTo(cell_info::class, 'cell', 'id');
    }

    // Relasi ke qco___preparation
    public function preparations(): HasMany
    {
        return $this->hasMany(qco___preparation::class, 'id_qco', 'id');
    }
    public function qco_status()
    {
        return $this->hasOne(qco___status::class, 'id_qco', 'id');
    }
}
