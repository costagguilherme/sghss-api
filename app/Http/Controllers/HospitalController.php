<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HospitalService;

class HospitalController extends Controller
{
    private HospitalService $service;

    public function __construct(HospitalService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $hospitals = $this->service->getAll();
        return $this->sendSucess($hospitals, 'Hospitais listados com sucesso');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $hospital = $this->service->create($request->only('name'));
        return $this->sendSucess($hospital->toArray(), 'Hospital criado com sucesso', 201);
    }

    public function update(Request $request, int $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $hospital = $this->service->update($id, $request->only('name'));
        return $this->sendSucess($hospital->toArray(), 'Hospital atualizado com sucesso');
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);
        return $this->sendSucess([], 'Hospital deletado com sucesso');
    }
}
