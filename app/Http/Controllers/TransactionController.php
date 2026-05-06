<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\TransactionServiceInterface;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class TransactionController extends Controller
{
    private TransactionServiceInterface $transaction;

    public function __construct(TransactionServiceInterface $transaction)
    {
        $this->transaction = $transaction;
    }
    public function store(Request $request, int $id): JsonResponse
    {
        $service = $this->transaction;

        $payload = $request->all();
        $payload['user_id'] = $id;

        try {
            $transaction = $service->saveTransaction($payload);
            return response()->json($transaction, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function index(int $id): JsonResponse
    {
        $service = $this->transaction;
        $transactions = $service->listTransactions($id);

        return response()->json($transactions);
    }
}
