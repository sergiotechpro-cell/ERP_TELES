<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();
        
        // Redirigir según permisos del usuario
        if ($user->can('ver-dashboard')) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }
        
        // Si no tiene permiso para dashboard, redirigir al primer módulo disponible
        if ($user->can('ver-pedidos')) {
            return redirect()->intended(route('pedidos.index'));
        }
        if ($user->can('ver-inventario')) {
            return redirect()->intended(route('inventario.index'));
        }
        if ($user->can('ver-pos')) {
            return redirect()->intended(route('pos.index'));
        }
        
        // Fallback a dashboard (si no tiene permisos, mostrará error 403)
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
