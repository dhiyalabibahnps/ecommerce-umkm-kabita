<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureBuyer
{
  public function handle(Request $request, Closure $next): Response
  {
    if (!Auth::check()) {
      return response()->json([
        'success' => false,
        'message' => 'Unauthenticated.',
      ], 401);
    }

    if (Auth::user()->role !== UserRole::BUYER) {
      return response()->json([
        'success' => false,
        'message' => 'Akses ditolak. Hanya buyer yang dapat mengakses endpoint ini.'
      ], 403);
    }

    return $next($request);
  }
}
