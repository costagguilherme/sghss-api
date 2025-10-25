<?php

namespace App\Http\Controllers;

use App\Services\PatientService;
use App\Http\Requests\PatientRequest;
use Illuminate\Http\JsonResponse;

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
        $patient = $this->service->getById($id);
        if (empty($patient)) {
            return $this->sendError('Patient not found', 404);
        }
        return $this->sendSucess($patient->toArray(), 'Paciente retornado com successo');
    }

    public function store(PatientRequest $request): JsonResponse
    {
        $patient = $this->service->create($request->validated());
        return $this->sendSucess($patient->toArray(), 'Paciente criado com successo', 201);
    }

    public function update(PatientRequest $request, int $id): JsonResponse
    {
        $patient = $this->service->getById($id);
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
