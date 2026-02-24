<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly LoginService $loginService) {}

    public function show(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $this->loginService->attempt(
                login: $request->input('login'),
                password: $request->input('password'),
                remember: $request->boolean('remember'),
            );

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        } catch (AuthenticationException $e) {
            return back()
                ->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => $e->getMessage()]);
        }
    }
}
