<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    protected AppointmentService $service;

    public function __construct(AppointmentService $service)
    {
        $this->service = $service;
    }

    public function getAll(): JsonResponse
    {
        $appointments = $this->service->getAll($this->getUserId());
        return $this->sendSucess($appointments->toArray(), 'Consultas encontradas');
    }

    public function getById(int $id): JsonResponse
    {
        $appointment = $this->service->getUserAppointment($id, $this->getUserId());
        if (empty($appointment)) {
            return $this->sendError('Consulta não encontrada', 404);
        }
        return $this->sendSucess($appointment->toArray(), 'Consulta encontrada');
    }

    public function schedule(Request $request): JsonResponse
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'patient_id' => 'required|exists:patients,id',
            'hospital_id' => 'required|exists:hospitals,id',
            'type' => 'required|in:in_person,online',
            'scheduled_at' => 'required|date',
        ]);

        try {
            $data = array_merge($request->all(), ['user_id' => $this->getUserId(), 'role' => $this->getRole()]);
            $appointment = $this->service->schedule($data);
            return $this->sendSucess($appointment, 'Consulta marcada com sucesso', 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function cancel(int $id): JsonResponse
    {
        $appointment = $this->service->getActiveUserAppointment($id, $this->getUserId());
        if (empty($appointment)) {
            return $this->sendError('Consulta não encontrada', 404);
        }
        $appointment = $this->service->cancel($id);
        return $this->sendSucess($appointment->toArray(), 'Consulta cancelada com sucesso');
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

    public function medicalInfo(Request $request, int $id): JsonResponse
    {
        $appointment = $this->service->medicalInfo($id, $request->all());
        if (empty($appointment)) {
            return $this->sendError('Consulta não encontrada', 404);
        }
        return $this->sendSucess($appointment->toArray(), 'Informações médicas gravadas com sucesso');
    }
}
