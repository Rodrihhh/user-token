<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // 1. Obtener una lista de los 10 primeros usuarios
    public function index()
    {
        try {
            $users = User::take(10)->get();

            return response()->json([
                'success' => true,
                'message' => 'Lista de los primeros 10 usuarios obtenida con éxito',
                'data'    => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de usuarios: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    // 2. Crear usuario hasheando la contraseña
    public function create(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']), // Contraseña hasheada
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'data'    => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario: ' . $e->getMessage(),
                'data'    => null
            ], 400);
        }
    }

    // 3. Iniciar sesión generando y devolviendo un token de sesión
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas',
                    'data'    => null
                ], 401);
            }

            // Generar cadena única de token
            $tokenString = Str::random(60);

            // Guardar token en el modelo Token
            $tokenRecord = Token::create([
                'user_id' => $user->id,
                'token'   => $tokenString,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'data'    => [
                    'user'  => $user,
                    'token' => $tokenRecord->token
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error durante el inicio de sesión: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }

    // 4. Actualizar el campo name pasándole el token y el nuevo name
    public function updateName(Request $request)
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
                'name'  => 'required|string|max:255',
            ]);

            // Buscar la instancia del token en la base de datos
            $tokenRecord = Token::where('token', $validated['token'])->first();

            if (!$tokenRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token no válido o no encontrado',
                    'data'    => null
                ], 401);
            }

            // Obtener el usuario asociado y actualizar su nombre
            $user = $tokenRecord->user;
            $user->name = $validated['name'];
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Nombre actualizado correctamente',
                'data'    => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el nombre: ' . $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }
}