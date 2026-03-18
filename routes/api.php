<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyzeInventoryController;

Route::get('ping', function (Request $request) {
    return response('pong', 200);
});

Route::post('analyze-inventory', [AnalyzeInventoryController::class, 'analyzeInventory']);
