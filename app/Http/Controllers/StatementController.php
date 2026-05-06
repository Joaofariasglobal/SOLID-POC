<?php

namespace App\Http\Controllers;

use App\Services\StatementService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class StatementController extends Controller
{
    public function show(int $id, StatementService $service): JsonResponse
    {
        try {
            $statement = $service->getStatement($id);
            return response()->json($statement);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
