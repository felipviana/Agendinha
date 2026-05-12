<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        return response()->json([
            'message' => 'Cadastro realizado com sucesso. Verifique seu e-mail antes de entrar.'
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['E-mail ou senha inválidos.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Confirme seu e-mail antes de fazer login.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso.',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json($request->user(), 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.'
        ], 200);
    }

    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect(env('FRONTEND_URL') . '/auth/verification-result.html?status=invalid');
        }

        if (! $request->hasValidSignature()) {
            return redirect(env('FRONTEND_URL') . '/auth/verification-result.html?status=invalid');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(env('FRONTEND_URL') . '/auth/verification-result.html?status=already-verified');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect(env('FRONTEND_URL') . '/auth/verification-result.html?status=success');
    }

    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'errors' => [
                    'email' => ['Nenhum usuário encontrado com esse e-mail.']
                ]
            ], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Este e-mail já foi verificado.'
            ], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Novo link de verificação enviado com sucesso.'
        ], 200);
    }
}
