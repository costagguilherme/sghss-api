<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ExamService;

class ExamController extends Controller
{
    protected ExamService $service;

    public function __construct(ExamService $service)
    {
        $this->service = $service;
    }

    public function getAll(): JsonResponse
    {
        $exams = $this->service->getAll($this->getUserId());
        return $this->sendSucess($exams->toArray(), 'Exames encontrados');
    }

    public function getById(int $id): JsonResponse
    {
        $exam = $this->service->getUserExam($id, $this->getUserId());
        if (empty($exam)) {
            return $this->sendError('Exame não encontrado', 404);
        }
        return $this->sendSucess($exam->toArray(), 'Exame encontrado');
    }

    public function schedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hospital_id' => 'required|exists:hospitals,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'patient_id' => 'required|exists:patients,id',
            'scheduled_at' => 'nullable|date',
            'doctor_id' => 'required|integer'
        ]);

        try {
            $data = array_merge($validated, ['user_id' => $this->getUserId(), 'role' => $this->getRole()]);
            $exam = $this->service->schedule($data);
            return $this->sendSucess($exam->toArray(), 'Exame criado com sucesso', 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function status(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,rejected,canceled,finished'
        ]);

        $appointment = $this->service->setStatus($id, $validated['status']);
        if (empty($appointment)) {
            return $this->sendError('Consulta não encontrada', 404);
        }
        return $this->sendSucess($appointment->toArray(), 'Status definido com sucesso');
    }

    public function cancel(int $id): JsonResponse
    {
        $exam = $this->service->findById($id);
        if (empty($exam)) {
            return $this->sendError('Exame não encontrado', 404);
        }

        $exam = $this->service->cancel($id);
        return $this->sendSucess($exam->toArray(), 'Exame cancelado com sucesso');
    }

    public function addReport(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'report' => 'required|string'
        ]);

        $exam = $this->service->addReport($id, $validated['report']);
        if (empty($exam)) {
            return $this->sendError('Exame não encontrado', 404);
        }

        return $this->sendSucess($exam->toArray(), 'Laudo adicionado com sucesso');
    }

    public function addResult(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'result_file' => 'required|file'
        ]);

        $exam = $this->service->addResultFile($id, $validated['result_file']);
        if (empty($exam)) {
            return $this->sendError('Exame não encontrado', 404);
        }

        return $this->sendSucess($exam->toArray(), 'Resultado adicionado com sucesso');
    }
}
