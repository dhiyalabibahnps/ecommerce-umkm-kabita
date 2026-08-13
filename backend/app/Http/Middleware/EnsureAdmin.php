<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
  public function handle(Request $request, Closure $next): Response
  {
    if (!Auth::check() || Auth::user()->role !== UserRole::ADMIN) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Hanya admin yang dapat mengakses endpoint ini.',
      ], 403);
    }

    return $next($request);
  }
}
