<?php

namespace App\Http\Controllers;

use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class StatementController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $service = new FinanceService();

        try {
            $statement = $service->getStatement($id);
            return response()->json($statement);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
