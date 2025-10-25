<?php

namespace App\Repositories;

use App\Models\Hospital;

class HospitalRepository
{
    public function all(): array
    {
        return Hospital::all()->toArray();
    }

    public function findById(int $id): ?Hospital
    {
        return Hospital::find($id);
    }

    public function create(array $data): Hospital
    {
        return Hospital::create($data);
    }

    public function update(Hospital $hospital, array $data): bool
    {
        return $hospital->update($data);
    }

    public function delete(Hospital $hospital): bool
    {
        return $hospital->delete();
    }
}
