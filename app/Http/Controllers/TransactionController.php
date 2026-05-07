<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
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
    public function store(StoreTransactionRequest $request, int $id): JsonResponse
    {
        try 
        {
            $transaction = $this->transaction->saveTransaction($request->validated());
                return response()->json($transaction, 201);
            } 
            catch (InvalidArgumentException $e) {
                return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function index(int $id): JsonResponse
    {
        $transactions = $this->transaction->listTransactions($id);

        return response()->json($transactions);
    }
}
