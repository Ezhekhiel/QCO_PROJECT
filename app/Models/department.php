<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class department extends Model
{
    use HasFactory;

    // Tambahkan baris ini agar Laravel tahu nama tabel yang benar di database
    protected $table = 'departments';

    protected $fillable = ['nama'];

    // Relasi ke User
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'department_id', 'id');
    }

    // Relasi ke Role
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'department_id', 'id');
    }
}
