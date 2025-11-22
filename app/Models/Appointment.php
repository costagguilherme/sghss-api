<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'hospital_id',
        'type',
        'meeting_id',
        'join_url',
        'start_url',
        'scheduled_at',
    ];

    protected $dates = ['scheduled_at'];
}
