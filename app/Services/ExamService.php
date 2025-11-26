<?php

namespace App\Services;

use App\Models\Exam;
use App\Repositories\ExamRepository;
use App\Repositories\PatientRepository;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ExamService
{
    private ExamRepository $repository;
    private PatientRepository $patientRepository;

    public function __construct(ExamRepository $repository, PatientRepository $patientRepository)
    {
        $this->repository = $repository;
        $this->patientRepository = $patientRepository;
    }

    public function getAll(int $userId)
    {
        $patient = $this->patientRepository->getPatientByUserId($userId);
        return $this->repository->getAll($patient->id);
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function getUserExam(int $id, $userId): ?Exam
    {
        $patient = $this->patientRepository->getPatientByUserId($userId);
        $exam = $this->repository->findByIdAndPatient($id, $patient->id);
        if (!$exam) {
            return null;
        }
        return $exam;
    }

    public function schedule(array $data, $file)
    {
        if ($data['role'] == 'patient') {
            $patient = $this->patientRepository->getPatientByUserId($data['user_id']);
            if ($patient->id != $data['patient_id']) {
                throw new Exception('Um paciente só pode marcar um exame para si mesmo');
            }
        }

        $path = $file->store('requirements', 's3');
        $data['requirement_url'] = url(Storage::url($path));
        $data['status'] = 'pending';
        $exam = $this->repository->create($data);
        $this->sendCreatedExamEmail($exam);
        $exam = $exam->toArray();
        unset($exam['doctor']);
        unset($exam['hospital']);
        unset($exam['patient']);
        return $exam;
    }

    public function setStatus(int $id, string $status)
    {
        $exam = $this->repository->findById($id);
        if (empty($exam)) {
            return null;
        }
        $exam->status = $status;
        $exam->save();
        $this->sendApprovedExamEmail($exam);
        return $exam;
    }

    public function cancel(int $id)
    {
        $exam = $this->repository->findById($id);
        if (empty($exam)) {
            return null;
        }
        $this->sendCancelAppointmentEmail($exam);
        return $this->repository->cancel($id);
    }

    public function addReport(int $id, string $report)
    {
        return $this->repository->update($id, [
            'report' => $report,
            'status' => 'finished'
        ]);
    }

    public function addResultFile(int $id, $file)
    {
        $path = $file->store('results', 's3');
        $url = url(Storage::url($path));

        return $this->repository->update($id, [
            'result_file_url' => $url
        ]);
    }

    private function sendCreatedExamEmail(Exam $exam)
    {
        $messageText = "Olá, sua solicitação de exame foi enviada. Aguarde a confirmação!\n\n";
        $messageText .= "Data/Hora: {$exam->scheduled_at}\n";
        $doctor = $exam->doctor;
        $doctorName = $doctor->user->name ?? '';

        $hospitalName = $exam->hospital->name ?? 'Não definido';

        $messageText .= "Médico: {$doctorName}\n";
        $messageText .= "Exame: {$exam->name}\n";
        $messageText .= "Local: {$hospitalName}\n";


        $user = $exam->patient->user ?? null;
        if ($user) {
            Mail::raw($messageText, function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('SGHSS: EXAME AGENDADO');
            });
        }
    }

    private function sendApprovedExamEmail(Exam $exam)
    {
        $messageText = "Olá, sua solicitação de exame foi confirmada! \n\n";
        $messageText .= "Data/Hora: {$exam->scheduled_at}\n";
        $doctor = $exam->doctor;
        $doctorName = $doctor->user->name ?? '';

        $hospitalName = $exam->hospital->name ?? 'Não definido';

        $messageText .= "Médico: {$doctorName}\n";
        $messageText .= "Exame: {$exam->name}\n";
        $messageText .= "Local: {$hospitalName}\n";

        $user = $exam->patient->user ?? null;
        if ($user) {
            Mail::raw($messageText, function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('SGHSS: EXAME CONFIRMADO');
            });
        }
    }

    private function sendCancelAppointmentEmail(Exam $exam)
    {
        $doctor = $exam->doctor;
        $messageText = "Olá, o seu exame de {$exam->name} que estava agendada para às {$exam->scheduled_at} foi cancelado!\n\n";

        $user = $exam->patient->user ?? null;
        if ($user) {
            Mail::raw($messageText, function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('SGHSS: EXAME CANCELADO');
            });
        }
    }
}
