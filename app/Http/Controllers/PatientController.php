<?php

namespace App\Http\Controllers;

use App\Services\PatientService;
use App\Http\Requests\PatientRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    protected PatientService $service;

    public function __construct(PatientService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        $patients = $this->service->getAll();
        return $this->sendSucess($patients->toArray(), 'Pacientes retornados com successo');
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->getRole();
        if ($role == 'admin') {
            $patient = $this->service->getById($id);
        } else {
            $patient = $this->service->getPatientByUserId($this->getUserId());
            if ($id != $patient->id) {
                $patient = null;
            }
        }

        if (empty($patient)) {
            return $this->sendError('Patient not found', 404);
        }
        return $this->sendSucess($patient->toArray(), 'Paciente retornado com successo');
    }

    public function store(PatientRequest $request): JsonResponse
    {
        $patient = $this->service->create($request->validated());
        return $this->sendSucess($patient, 'Paciente criado com successo', 201);
    }

    public function update(PatientRequest $request, int $id): JsonResponse
    {
        $patient = $this->service->getById($id);
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->id !== $patient->user_id) {
            return $this->sendError('Acesso negado: você não pode atualizar este paciente', 403);
        }

        if (empty($patient)) {
            return $this->sendError('Patient not found', 404);
        }
        $patient = $this->service->update($id, $request->validated());
        return $this->sendSucess($patient->toArray(), 'Paciente atualizado com successo');
    }

    public function destroy(int $id): JsonResponse
    {
        $patient = $this->service->getById($id);
        if (empty($patient)) {
            return $this->sendError('Patient not found', 404);
        }
        $patient = $this->service->delete($id);
        return $this->sendSucess($patient->toArray(), 'Paciente removido com successo');
    }
}
