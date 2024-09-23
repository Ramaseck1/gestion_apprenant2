<?php

namespace App\Http\Controllers;

use App\Services\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    protected $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'libelle' => 'required|string|unique:promotions',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date',
            'duree' => 'nullable|integer',
            'referentiels' => 'array|nullable',
            'photo' => 'nullable|string',
        ]);

        $promotionId = $this->promotionService->createPromotion($data);
        return response()->json(['id' => $promotionId], 201);
    }

    public function index()
    {
        $promotions = $this->promotionService->listPromotions();
        return response()->json($promotions);
    }

    public function show($id)
    {
        $promotion = $this->promotionService->getPromotionById($id);
        return response()->json($promotion);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'libelle' => 'nullable|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'duree' => 'nullable|integer',
            'referentiels' => 'array|nullable',
            'photo' => 'nullable|string',
        ]);

        $this->promotionService->updatePromotion($id, $data);
        return response()->json(['message' => 'Promotion updated successfully.']);
    }

    public function patchReferentiel(Request $request, $id)
    {
        $request->validate([
            'referentiel_id' => 'required|string',
            'action' => 'required|in:add,remove',
        ]);

        $this->promotionService->updateReferentiel($id, $request->referentiel_id, $request->action);
        return response()->json(['message' => 'Referentiel updated successfully.']);
    }

    public function updateEtat(Request $request, $id)
    {
        $this->promotionService->changePromotionEtat($id);
        return response()->json(['message' => 'Promotion status updated successfully.']);
    }

    public function getPromotionEncours()
    {
        $promotion = $this->promotionService->getPromotionEncours();
        return response()->json($promotion);
    }

    public function cloturerPromotion($id)
    {
        $this->promotionService->cloturerPromotion($id);
        return response()->json(['message' => 'Promotion closed successfully.']);
    }

    public function getReferentiels($id)
    {
        $referentiels = $this->promotionService->listReferentiels($id);
        return response()->json($referentiels);
    }

    public function getPromotionStats($id)
    {
        $stats = $this->promotionService->getPromotionStats($id);
        return response()->json($stats);
    }
}
