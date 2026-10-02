<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * Recuperación de contraseña (P1-10). Usa el broker nativo de Laravel; con
 * `MAIL_MAILER=log` el enlace se escribe en el log en desarrollo.
 */
class PasswordResetController extends Controller
{
    public function send(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return response()->json(['data' => ['message' => __($status)]]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => $password])->save();

            // Un cambio de contraseña invalida los tokens de los dispositivos.
            $user->tokens()->delete();

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return response()->json(['data' => ['message' => __($status)]]);
    }
}
