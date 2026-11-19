<?php

namespace App\Http\Controllers;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Login- en registratiescherm (één pagina met twee panelen).
     */
    public function showLogin(): View
    {
        return view('auth.login', [
            'authMessage' => session('auth_message', ''),
            'authMessageType' => session('auth_message_type', 'error'),
            'activeAuthMode' => session('auth_mode', 'login'),
        ]);
    }

    /**
     * Inloggen met e-mailadres óf gebruikersnaam.
     */
    public function login(Request $request): RedirectResponse
    {
        $identifier = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            return $this->redirectWithMessage('E-mailadres/gebruikersnaam of wachtwoord is onjuist.', 'error', 'login')
                ->withInput($request->only('email'));
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function register(Request $request): RedirectResponse
    {
        $passwordBytes = function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) || strlen($value) < 8 || strlen($value) > 72) {
                $fail('Kies een wachtwoord van minimaal 8 en maximaal 72 bytes.');
            }
        };

        $birthDate = function (string $attribute, mixed $value, Closure $fail) use ($request): void {
            $day = filter_var($request->input('day'), FILTER_VALIDATE_INT);
            $month = filter_var($request->input('month'), FILTER_VALIDATE_INT);
            $year = filter_var($request->input('year'), FILTER_VALIDATE_INT);

            if (
                $day === false || $month === false || $year === false
                || $year < (int) now()->format('Y') - 110
                || ! checkdate($month, $day, $year)
                || sprintf('%04d-%02d-%02d', $year, $month, $day) > now()->toDateString()
            ) {
                $fail('Kies een geldige geboortedatum.');
            }
        };

        $validator = Validator::make($request->all(), [
            'email' => ['bail', 'required', 'string', 'email', 'max:254', 'unique:users,email'],
            'username' => ['bail', 'required', 'string', 'regex:/\A[A-Za-z0-9_.-]{3,30}\z/', 'unique:users,username'],
            'fullName' => ['required', 'string', 'max:150'],
            'password' => ['bail', 'required', $passwordBytes],
            'confirmPassword' => ['same:password'],
            'day' => ['bail', 'required', $birthDate],
        ], [
            'email.unique' => 'Dit e-mailadres of deze gebruikersnaam is al in gebruik.',
            'email.*' => 'Vul een geldig e-mailadres in.',
            'username.unique' => 'Dit e-mailadres of deze gebruikersnaam is al in gebruik.',
            'username.*' => 'Je gebruikersnaam moet 3 tot 30 tekens bevatten (letters, cijfers, punt, streepje of underscore).',
            'fullName.*' => 'Vul een naam van maximaal 150 tekens in.',
            'password.required' => 'Kies een wachtwoord van minimaal 8 en maximaal 72 bytes.',
            'confirmPassword.same' => 'Wachtwoorden komen niet overeen.',
            'day.required' => 'Kies een geldige geboortedatum.',
        ]);

        if ($validator->fails()) {
            return $this->redirectWithMessage($validator->errors()->first(), 'error', 'register')
                ->withInput($request->except('password', 'confirmPassword'));
        }

        $data = $validator->validated();

        $user = User::query()->create([
            'email' => trim($data['email']),
            'username' => trim($data['username']),
            'full_name' => trim($data['fullName']),
            'password' => $data['password'],
            'birth_date' => sprintf('%04d-%02d-%02d', $request->integer('year'), $request->integer('month'), $request->integer('day')),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->redirectWithMessage('Je bent uitgelogd.', 'success', 'login');
    }

    private function redirectWithMessage(string $message, string $type, string $mode): RedirectResponse
    {
        return redirect()->route('login')->with([
            'auth_message' => $message,
            'auth_message_type' => $type,
            'auth_mode' => $mode,
        ]);
    }
}
