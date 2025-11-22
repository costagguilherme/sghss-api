<?php

namespace App\Services;

use App\Repositories\AppointmentRepository;
use App\Models\Appointment;
use App\Repositories\HospitalRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;
use App\Services\Externals\ZoomApi;
use Exception;
use Illuminate\Support\Facades\Mail;

class AppointmentService
{
    private AppointmentRepository $repository;
    private PatientRepository $patientRepository;
    private HospitalRepository $hospitalRepository;
    private UserRepository $userRepository;
    private ZoomApi $zoomApi;
    public function __construct(
        AppointmentRepository $repository,
        PatientRepository $patientRepository,
        HospitalRepository $hospitalRepository,
        UserRepository $userRepository,

        ZoomApi $zoomApi
    ) {
        $this->repository = $repository;
        $this->patientRepository = $patientRepository;
        $this->hospitalRepository = $hospitalRepository;
        $this->userRepository = $userRepository;
        $this->zoomApi = $zoomApi;
    }

    public function schedule(array $data): array
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

        $appointment = $this->repository->create($data);
        $this->sendCreatedAppointmentEmail($appointment);

        $appointment = $appointment->toArray();
        unset($appointment['doctor']);
        unset($appointment['patient']);
        return $appointment;
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

        $this->sendCancelAppointmentEmail($appointment);
        $this->repository->delete($appointment);
    }


    private function sendCreatedAppointmentEmail(Appointment $appointment)
    {
        $messageText = "Olá, sua consulta foi agendada!\n\n";
        $messageText .= "Data/Hora: {$appointment->scheduled_at}\n";
        $doctor = $appointment->doctor;
        $doctorName = $doctor->user->name ?? '';
        $specialty = $doctor->specialty;

        $messageText .= "Médico: {$doctorName}\n";
        $messageText .= "Especialidade: {$specialty}\n";

        if ($appointment->type === 'online') {
            $messageText .= "Tipo: Online\n";
            $messageText .= "Link para participar: {$appointment->join_url}\n";
        } else {
            $hospitalName = $appointment->hospital->name ?? 'Não definido';
            $messageText .= "Tipo: Presencial\n";
            $messageText .= "Local: {$hospitalName}\n";
        }

        $user = $appointment->patient->user ?? null;
        if ($user) {
            Mail::raw($messageText, function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('SGHSS: CONSULTA AGENDADA');
            });
        }
    }

    private function sendCancelAppointmentEmail(Appointment $appointment)
    {
        $doctor = $appointment->doctor;
        $messageText = "Olá, sua consulta com o médico(a) {$doctor->user->name} ({$doctor->specialty}) que estava agenda para {$appointment->scheduled_at} foi cancelada!\n\n";

        $user = $appointment->patient->user ?? null;
        if ($user) {
            Mail::raw($messageText, function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('SGHSS: CONSULTA CANCELADA');
            });
        }
    }
}
