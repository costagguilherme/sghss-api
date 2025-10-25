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
        'conference_link',
        'scheduled_at',
    ];

    protected $dates = ['scheduled_at']; 
}
