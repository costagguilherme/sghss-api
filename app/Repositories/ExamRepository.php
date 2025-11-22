<?php

namespace App\Repositories;

use App\Models\Exam;

class ExamRepository
{
    public function getAll(int $patientId)
    {
        return Exam::where('patient_id', $patientId)->orderBy('id', 'desc')->get();
    }

    public function findById(int $id): Exam
    {
        return Exam::find($id);
    }

    public function findByIdAndPatient(int $id, int $patientId): ?Exam
    {
        $query = Exam::where('id', $id)->where('patient_id', $patientId);
        return $query->first();
    }

    public function create(array $data): Exam
    {
        return Exam::create($data);
    }

    public function update(int $id, array $data): ?Exam
    {
        $exam = Exam::find($id);
        if (!$exam) {
            return null;
        }

        $exam->update($data);
        return $exam;
    }

    public function cancel(int $id): ?Exam
    {
        $exam = Exam::find($id);
        if (!$exam) {
            return null;
        }

        $exam->status = 'canceled';
        $exam->save();
        return $exam;
    }
}
