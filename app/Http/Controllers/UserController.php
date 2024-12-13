<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    // Lista todos os usuários com cache
    public function index()
    {
        try {
            $users = Cache::remember('users', 60, function () {
                Log::info('Consulta ao banco de dados realizada.');
                return User::all();
            });

            return response()->json($users);
        } catch (Exception $e) {
            return response()->json(['error' => 'Unable to fetch users', 'message' => $e->getMessage()], 500);
        }
    }

    // Exibe um usuário específico com cache
    public function show($id)
    {
        try {
            $user = Cache::remember("user:$id", 60, function () use ($id) {
                return User::findOrFail($id);
            });

            return response()->json($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Unable to fetch user', 'message' => $e->getMessage()], 500);
        }
    }

    // Cria um novo usuário e limpa o cache
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $validated['password'] = bcrypt($validated['password']);
            $user = User::create($validated);

            Cache::forget('users');

            return response()->json($user, 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation error', 'messages' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'Unable to create user', 'message' => $e->getMessage()], 500);
        }
    }

    // Atualiza um usuário e limpa o cache
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
            ]);

            $user->update($validated);

            Cache::forget("user:$id");
            Cache::forget('users');

            return response()->json($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation error', 'messages' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'Unable to update user', 'message' => $e->getMessage()], 500);
        }
    }

    // Exclui um usuário e limpa o cache
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            Cache::forget("user:$id");
            Cache::forget('users');

            return response()->json(['message' => 'User deleted']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Unable to delete user', 'message' => $e->getMessage()], 500);
        }
    }
}
