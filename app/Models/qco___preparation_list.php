<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class qco___preparation_list extends Model
{
    use HasFactory;

    protected $table = 'qco___preparation_list';
    protected $fillable = ['department_id', 'list'];

    // Relasi ke tabel departement
    public function department(): BelongsTo
    {
        return $this->belongsTo(department::class, 'department_id', 'id');
    }

    // Relasi ke tabel preparations
    public function preparations(): HasMany
    {
        return $this->hasMany(qco___preparation::class, 'id_list', 'id');
    }
}

