<?php

namespace App\Services;

use App\Repositories\AppointmentRepository;
use App\Models\Appointment;
use App\Repositories\PatientRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AppointmentService
{
    private AppointmentRepository $repository;
    private PatientRepository $patientRepository;

    public function __construct(AppointmentRepository $repository, PatientRepository $patientRepository)
    {
        $this->repository = $repository;
        $this->patientRepository = $patientRepository;
    }

    public function schedule(array $data): Appointment
    {
        if ($data['role'] == 'patient') {
            $patient = $this->patientRepository->getPatientByUserId($data['user_id']);
            if ($patient->id != $data['patient_id']) {
                throw new Exception('Um paciente só pode marcar uma consulta para si mesmo');
            }
        }
        return $this->repository->create($data);
    }

    public function getUserAppointment(int $id, $user_id): ?Appointment
    {
        $patient = $this->patientRepository->getPatientByUserId($user_id);
        $appointment = $this->repository->findByIdAndPatient($id, $patient->id);
        if (!$appointment) {
            return null;
        }
        return $appointment;
    }

    public function cancel(int $id): void
    {
        $appointment = $this->repository->findById($id);
        $this->repository->delete($appointment);
    }
}
