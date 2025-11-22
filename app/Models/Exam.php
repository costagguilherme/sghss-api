<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'hospital_id',
        'name',
        'description',
        'scheduled_at',
        'report',
        'result_file',
        'status',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
