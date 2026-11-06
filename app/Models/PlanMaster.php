<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanMaster extends Model
{
    use HasFactory;

    protected $table = 'plan_masters';

    protected $fillable = [
        'cell',
        'model',
        'style_no',
        'gen',
        'po',
        'qty',
        'assembly_start_date',
        'stitching_start_date',
    ];

    protected $casts = [
        'qty' => 'integer',
        'assembly_start_date' => 'date',
        'stitching_start_date' => 'date',
    ];
}
