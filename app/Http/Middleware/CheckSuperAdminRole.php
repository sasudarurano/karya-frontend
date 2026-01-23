<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login
        if (!Session::has('api_token')) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Ambil user role dari session
        $userRole = Session::get('user.role');

        // Cek apakah user role adalah superadmin
        if ($userRole !== 'superadmin') {
            abort(403, 'You do not have permission to perform this action');
        }

        return $next($request);
    }
}
