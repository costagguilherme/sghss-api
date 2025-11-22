<?php

namespace App\Services;

use App\Repositories\AppointmentRepository;
use App\Models\Appointment;
use App\Repositories\PatientRepository;
use App\Services\Externals\ZoomApi;
use Exception;

class AppointmentService
{
    private AppointmentRepository $repository;
    private PatientRepository $patientRepository;
    private ZoomApi $zoomApi;
    public function __construct(AppointmentRepository $repository, PatientRepository $patientRepository, ZoomApi $zoomApi)
    {
        $this->repository = $repository;
        $this->patientRepository = $patientRepository;
        $this->zoomApi = $zoomApi;
    }

    public function schedule(array $data): Appointment
    {
        if ($data['role'] == 'patient') {
            $patient = $this->patientRepository->getPatientByUserId($data['user_id']);
            if ($patient->id != $data['patient_id']) {
                throw new Exception('Um paciente só pode marcar uma consulta para si mesmo');
            }
        }

        if (isset($data['type']) && $data['type'] === 'online') {
            $meeting = $this->zoomApi->createMeeting('me', [
                'topic' => 'Consulta Médica (SGHSS API)',
                'type' => 2,
                'start_time' => now()->addHour()->toIso8601String(),
                'duration' => 40,
                'timezone' => 'America/Sao_Paulo',
                'settings' => [
                    'host_video' => true,
                    'participant_video' => false,
                ]
            ]);

            $data['meeting_id'] = $meeting['id'];
            $data['join_url'] = $meeting['join_url'];
            $data['start_url'] = $meeting['start_url'];
            $data['hospital_id'] = null;
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
        if ($appointment->type == 'online') {
            $this->zoomApi->deleteMeeting($appointment->meeting_id);
        }
        $this->repository->delete($appointment);
    }
}
