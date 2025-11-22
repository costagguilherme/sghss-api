<?php

namespace App\Services;

use App\Models\Exam;
use App\Repositories\ExamRepository;
use App\Repositories\PatientRepository;
use Exception;
use Illuminate\Support\Facades\Mail;

class ExamService
{
    private ExamRepository $repository;
    private PatientRepository $patientRepository;

    public function __construct(ExamRepository $repository, PatientRepository $patientRepository)
    {
        $this->repository = $repository;
        $this->patientRepository = $patientRepository;
    }

    public function getAll(int $hospital_id)
    {
        return $this->repository->getAll($hospital_id);
    }

    public function findById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function getUserExam(int $id, $user_id): ?Exam
    {
        $patient = $this->patientRepository->getPatientByUserId($user_id);
        $exam = $this->repository->findByIdAndPatient($id, $patient->id);
        if (!$exam) {
            return null;
        }
        return $exam;
    }

    public function schedule(array $data)
    {
        if ($data['role'] == 'patient') {
            $patient = $this->patientRepository->getPatientByUserId($data['user_id']);
            if ($patient->id != $data['patient_id']) {
                throw new Exception('Um paciente só pode marcar um exame para si mesmo');
            }
        }

        $data['status'] = 'pending';
        $exam = $this->repository->create($data);
        $this->sendCreatedExamEmail($exam);
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
        return $this->repository->cancel($id);
    }

    public function addReport(int $id, string $report)
    {
        return $this->repository->update($id, [
            'report' => $report,
            'status' => 'completed'
        ]);
    }

    public function addResultFile(int $id, string $filePath)
    {
        return $this->repository->update($id, [
            'result_file' => $filePath
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
}
