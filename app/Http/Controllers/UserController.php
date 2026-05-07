<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Contracts\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class UserController extends Controller
{
    private UserServiceInterface $userService;
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());
            return response()->json($user, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findUser($id);

        if (! $user) {
            return response()->json(['error' => 'Usuário não encontrado.'], 404);
        }

        return response()->json($user);
    }
}
