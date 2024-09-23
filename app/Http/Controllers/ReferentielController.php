<?php

namespace App\Http\Controllers;

use App\Policies\UserPolicy;
use App\Services\ReferentielService;
use Illuminate\Http\Request;

class ReferentielController extends Controller
{
    protected $referentielService;

    public function __construct(ReferentielService $referentielService)
    {
        $this->referentielService = $referentielService;
    }

    public function create(Request $request)
    {
        
        
        $data = $request->validate([
            'code' => 'required|string|unique:referentiels',
            'libelle' => 'required|string|unique:referentiels',
            'description' => 'required|string',
            'photo' => 'nullable|string',
            'competences' => 'required|array',
        ]);

        $referentielId = $this->referentielService->createReferentiel($data);
        return response()->json(['id' => $referentielId], 201);
    }

    public function index()
    {
        $referentiels = $this->referentielService->getReferentiels();
        return response()->json($referentiels);
    }

    public function show($id)
    {
        try {
            $referentiel = $this->referentielService->getReferentielById($id);
            return response()->json($referentiel);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'libelle' => 'nullable|string',
            'description' => 'nullable|string',
            'photo' => 'nullable|string',
            'competences' => 'nullable|array',
        ]);

        try {
            $this->referentielService->updateReferentiel($id, $data);
            return response()->json(['message' => 'Referentiel updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $this->referentielService->deleteReferentiel($id);
            return response()->json(['message' => 'Referentiel deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function listArchived()
    {
        $archivedReferentiels = $this->referentielService->listArchivedReferentiels();
        return response()->json($archivedReferentiels);
    }
}
