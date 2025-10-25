<?php

namespace App\Repositories;

use App\Models\Appointment;

class AppointmentRepository
{
    public function findById(int $id): ?Appointment
    {
        return Appointment::find($id);
    }

    public function findByIdAndPatient(int $id, int $patientId): ?Appointment
    {
        $query = Appointment::where('id', $id)->where('patient_id', $patientId);
        return $query->first();
    }

    public function create(array $data): Appointment
    {
        return Appointment::create($data);
    }

    public function delete(Appointment $appointment): bool
    {
        return $appointment->delete();
    }
}
