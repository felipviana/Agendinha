<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;


class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email'),
            function($user, $token) {
                $frontendUrl = env('FRONTEND_URL', 'http://127.0.0.1:5501');
                $resetUrl = $frontendUrl . '/auth/reset-password.html?token=' . $token . '&email=' . urlencode($user->email);

                $user->sendPasswordResetNotification($token);
            }
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Enviamos o link de recuperação para o e-mail informado.'
            ], 200);
        }

        return response()->json([
            'errors' => [
                'email' => ['Não foi possível enviar o link de recuperação.']
            ]
        ], 422);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
            }

        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Senha redefinida com sucesso.'
            ], 200);
        }

        return response()->json([
            'errors' => [
                'email' => ['Token inválido ou expirado.']
            ]
        ], 422);
    }
}
