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
    private ZoomApi $zoomApi;
    public function __construct(
        AppointmentRepository $repository,
        PatientRepository $patientRepository,

        ZoomApi $zoomApi
    ) {
        $this->repository = $repository;
        $this->patientRepository = $patientRepository;
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

    public function getAll(int $user_id)
    {
        $patient = $this->patientRepository->getPatientByUserId($user_id);
        return $this->repository->getAll($patient->id);
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

    public function getActiveUserAppointment(int $id, $user_id): ?Appointment
    {
        $patient = $this->patientRepository->getPatientByUserId($user_id);
        $appointment = $this->repository->findActiveByIdAndPatient($id, $patient->id);
        if (!$appointment) {
            return null;
        }
        return $appointment;
    }

    public function cancel(int $id): ?Appointment
    {
        $appointment = $this->repository->findById($id);
        if ($appointment->type == 'online') {
            $this->zoomApi->deleteMeeting($appointment->meeting_id);
        }

        $this->sendCancelAppointmentEmail($appointment);
        $appointment->status = 'canceled';
        $appointment->save();
        return $appointment;
    }

    public function medicalInfo(int $appointmentId, array $data): ?Appointment
    {
        $appointment = $this->repository->findById($appointmentId);
        if (empty($appointment)) {
            return null;
        }

        if (isset($data['medical_notes'])) {
            $appointment->medical_notes = $data['medical_notes'];
        }
        if (isset($data['prescription'])) {
            $appointment->prescription = $data['prescription'];
        }
        if (isset($data['recommendations'])) {
            $appointment->recommendations = $data['recommendations'];
        }

        if (isset($data['requested_exams'])) {
            $appointment->requested_exams = $data['requested_exams'];
        }

        if (isset($data['certificate'])) {
            $appointment->certificate = $data['certificate'];
        }

        $appointment->save();

        return $appointment;
    }

    public function setStatus(int $id, string $status)
    {
        $appointment = $this->repository->findById($id);
        if (empty($appointment)) {
            return null;
        }
        $appointment->status = $status;
        $appointment->save();
        $this->sendApprovedAppointmentEmail($appointment);
        return $appointment;
    }

    private function sendCreatedAppointmentEmail(Appointment $appointment)
    {
        $messageText = "Olá, sua solicitação de consulta foi enviada. Aguarde a confirmação!\n\n";
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

    private function sendApprovedAppointmentEmail(Appointment $appointment)
    {
        $messageText = "Olá, sua consulta foi confirmada!\n\n";
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
                    ->subject('SGHSS: CONSULTA CONFIRMADA');
            });
        }
    }

    private function sendCancelAppointmentEmail(Appointment $appointment)
    {
        $doctor = $appointment->doctor;
        $messageText = "Olá, sua consulta com o médico(a) {$doctor->user->name} ({$doctor->specialty}) que estava agendada para às {$appointment->scheduled_at} foi cancelada!\n\n";

        $user = $appointment->patient->user ?? null;
        if ($user) {
            Mail::raw($messageText, function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('SGHSS: CONSULTA CANCELADA');
            });
        }
    }
}
