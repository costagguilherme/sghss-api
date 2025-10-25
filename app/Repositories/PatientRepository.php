<?php

namespace App\Repositories;

use App\Models\Patient;

class PatientRepository
{
    public function getAll()
    {
        return Patient::all();
    }

    public function findById(int $id): ?Patient
    {
        return Patient::find($id);
    }

    public function create(array $data): Patient
    {
        return Patient::create($data);
    }

    public function update(Patient $patitent, array $data): Patient
    {
        $patitent->update($data);
        return $patitent;
    }

    public function delete(Patient $patitent): bool
    {
        return $patitent->delete();
    }
}
