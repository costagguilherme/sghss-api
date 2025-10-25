<?php

namespace App\Http\Controllers;

use App\Services\DoctorService;
use App\Http\Requests\DoctorRequest;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    protected DoctorService $service;

    public function __construct(DoctorService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        $doctors = $this->service->getAll();
        return $this->sendSucess($doctors->toArray(), 'Médicos retornados com successo');
    }

    public function show(int $id): JsonResponse
    {
        $doctor = $this->service->getById($id);
        if (empty($doctor)) {
            return $this->sendError('Doctor not found', 404);
        }
        return $this->sendSucess($doctor->toArray(), 'Médico retornado com successo');
    }

    public function store(DoctorRequest $request): JsonResponse
    {
        $doctor = $this->service->create($request->validated());
        return $this->sendSucess($doctor->toArray(), 'Médico criado com successo', 201);
    }

    public function update(DoctorRequest $request, int $id): JsonResponse
    {
        $doctor = $this->service->getById($id);
        if (empty($doctor)) {
            return $this->sendError('Doctor not found', 404);
        }
        $doctor = $this->service->update($id, $request->validated());
        return $this->sendSucess($doctor->toArray(), 'Médico atualizado com successo');
    }

    public function destroy(int $id): JsonResponse
    {
        $doctor = $this->service->getById($id);
        if (empty($doctor)) {
            return $this->sendError('Doctor not found', 404);
        }
        $doctor = $this->service->delete($id);
        return $this->sendSucess($doctor->toArray(), 'Médico removido com successo');

    }
}
