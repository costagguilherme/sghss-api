<?php

namespace App\Services;

use App\Repositories\HospitalRepository;
use App\Models\Hospital;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class HospitalService
{
    protected HospitalRepository $repository;

    public function __construct(HospitalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): array
    {
        return $this->repository->all();
    }

    public function create(array $data): Hospital
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Hospital
    {
        $hospital = $this->repository->findById($id);
        $this->repository->update($hospital, $data);
        return $hospital;
    }

    public function delete(int $id): void
    {
        $hospital = $this->repository->findById($id);
        $this->repository->delete($hospital);
    }
}
