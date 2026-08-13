<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    // Sanitize text inputs (XSS protection)
    if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
      $input = $request->all();
      $sanitized = $this->sanitizeArray($input);
      $request->merge($sanitized);
    }

    return $next($request);
  }

  /**
   * Recursively sanitize array inputs.
   */
  private function sanitizeArray(array $data): array
  {
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        $data[$key] = $this->sanitizeArray($value);
      } elseif (is_string($value)) {
        $data[$key] = $this->sanitizeString($value);
      }
    }

    return $data;
  }

  /**
   * Sanitize a single string value.
   */
  private function sanitizeString(string $value): string
  {
    // Remove HTML tags
    $value = strip_tags($value);

    // Convert special characters to HTML entities
    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

    // Trim whitespace
    $value = trim($value);

    return $value;
  }
}
