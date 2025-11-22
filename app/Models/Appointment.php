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
        'medical_notes',
        'prescription',
        'recommendations',
        'certificate',
        'requested_exams',
        'status'
    ];

    protected $dates = ['scheduled_at'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    public function patientUser()
    {
        return $this->patient->user();
    }
}
