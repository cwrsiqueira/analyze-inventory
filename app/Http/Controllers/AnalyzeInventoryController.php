<?php

namespace App\Http\Controllers;

use App\Services\InventoryAnalyzerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Validator;

class AnalyzeInventoryController extends Controller
{
    public function __construct(
        private readonly InventoryAnalyzerService $inventoryAnalyzer
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function analyzeInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inventory' => [
                'required',
                'file',
                File::types(['csv', 'txt'])
                    ->max(12 * 1024),  // máximo 12 MB (valores em KB)
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro ao validar arquivo',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $result = $this->inventoryAnalyzer->analyze($request->file('inventory'));
        } catch (\Throwable $th) {
            Log::error('Erro ao analisar inventário: ' . $th->getMessage());
            return response()->json(['error' => 'Arquivo inválido ou corrompido'], 422);
        }

        return response()->json($result, 200);
    }
}
