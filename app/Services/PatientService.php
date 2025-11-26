<?php

namespace App\Services;

use App\Repositories\PatientRepository;
use App\Models\Patient;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Types\Enums\RoleEnum;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class PatientService
{
    private PatientRepository $repository;
    private UserRepository $userRepository;

    public function __construct(PatientRepository $repository, UserRepository $userRepository)
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function getPatientByUserId(int $userId): ?Patient
    {
        return $this->repository->getPatientByUserId($userId);
    }

    public function getById(int $id): ?Patient
    {
        $patient = $this->repository->findById($id);
        if (!$patient) {
            return null;
        }
        return $patient;
    }

    public function create(array $data): array
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => RoleEnum::PATIENT,
            'phone' => $data['phone'],
        ];

        $user = $this->userRepository->create($userData);
        $data['user_id'] = $user->id;
        $response = $this->repository->create($data);
        $response = $response->toArray();
        $response['email'] = $user->email;
        $response['name'] = $user->name;
        return $response;
    }

    public function update(int $id, array $data): Patient
    {
        $patient = $this->getById($id);
        return $this->repository->update($patient, $data);
    }

    public function delete(int $id): User
    {
        $patient = $this->getById($id);
        $user = $this->userRepository->findById($patient->user_id);
        $this->userRepository->delete($user);
        return $user;
    }
}
