<?php

namespace App\Http\Controllers;

use App\Services\CreateTransactionService;
use App\Contracts\TransactionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TransactionController extends Controller
{
    public function store(Request $request, int $id, CreateTransactionService $service): JsonResponse
    {
        $payload = $request->all();
        $payload['user_id'] = $id;

        try {
            $transaction = $service->saveTransaction($payload);
            return response()->json($transaction, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function index(int $id, TransactionRepositoryInterface $transactions): JsonResponse
    {
        return response()->json($transactions->listTransactions($id));
    }
}
