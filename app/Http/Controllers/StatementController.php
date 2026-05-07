<?php

namespace App\Http\Controllers;

use App\Contracts\StatementServiceInterface;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class StatementController extends Controller
{
    private StatementServiceInterface $statementService;

    public function __construct(StatementServiceInterface $statementService)
    {
        $this->statementService = $statementService;
    }

    public function show(int $userId): JsonResponse
    {
        try {
            $statement = $this->statementService->getStatement($userId);
            return response()->json($statement);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}