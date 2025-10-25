<?php

namespace App\Services;

use App\Repositories\DoctorRepository;
use App\Models\Doctor;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Types\Enums\RoleEnum;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DoctorService
{
    private DoctorRepository $repository;
    private UserRepository $userRepository;

    public function __construct(DoctorRepository $repository, UserRepository $userRepository)
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): ?Doctor
    {
        $doctor = $this->repository->findById($id);
        if (!$doctor) {
            return null;
        }
        return $doctor;
    }

    public function create(array $data): Doctor
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => RoleEnum::DOCTOR,
            'phone' => $data['phone'],
        ];

        $user = $this->userRepository->create($userData);
        $data['user_id'] = $user->id;
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Doctor
    {
        $doctor = $this->getById($id);
        return $this->repository->update($doctor, $data);
    }

    public function delete(int $id): User
    {
        $doctor = $this->getById($id);
        $user = $this->userRepository->findById($doctor->user_id);
        $this->userRepository->delete($user);
        return $user;
    }
}
