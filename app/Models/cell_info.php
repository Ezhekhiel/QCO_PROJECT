<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class cell_info extends Model
{
    use HasFactory;

    protected $table = 'cell_info';
    protected $fillable = ['building', 'area','eol','mp'];

    public function qcos(): HasMany
    {
        return $this->hasMany(qco::class, 'cell', 'id');
    }
}
