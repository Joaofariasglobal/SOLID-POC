<?php

namespace App\Http\Controllers;

use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class UserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $service = new FinanceService();

        try {
            $user = $service->createUser($request->all());
            return response()->json($user, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $service = new FinanceService();
        $user = $service->findUser($id);

        if (! $user) {
            return response()->json(['error' => 'Usuário não encontrado.'], 404);
        }

        return response()->json($user);
    }
}
