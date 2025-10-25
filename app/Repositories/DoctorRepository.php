<?php

namespace App\Repositories;

use App\Models\Doctor;

class DoctorRepository
{
    public function getAll()
    {
        return Doctor::all();
    }

    public function findById(int $id): ?Doctor
    {
        return Doctor::find($id);
    }

    public function create(array $data): Doctor
    {
        return Doctor::create($data);
    }

    public function update(Doctor $patitent, array $data): Doctor
    {
        $patitent->update($data);
        return $patitent;
    }

    public function delete(Doctor $patitent): bool
    {
        return $patitent->delete();
    }
}
