<?php

namespace App\Extensions;

use Dedoc\Scramble\Contracts\DocumentTransformer;
use Dedoc\Scramble\OpenApiContext;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Tag;

/**
 * AutoGroupTransformer
 *
 * Automatically assigns descriptions to OpenAPI tags based on controller namespaces.
 * This ensures consistent and readable API documentation in Scramble/Swagger UI.
 *
 * Usage: Registered via ScrambleExtensionProvider::boot()
 */
class AutoGroupTransformer implements DocumentTransformer
{
  /**
   * Descriptions for each API tag/group.
   *
   * These descriptions appear in the Swagger UI sidebar under each tag.
   */
  protected array $tagDescriptions = [
    'Auth' => 'User authentication & registration - Login, register, and email verification',
    'Shop' => 'Shop management for sellers - Create, update, and view shop profiles',
    'Category' => 'Product categories management - Manage product categories',
    'Product' => 'Product catalog & management - Browse, verify, and manage products',
    'Cart' => 'Shopping cart operations - Add, update, and manage cart items',
    'Checkout' => 'Checkout process - Create orders from cart items',
    'Order' => 'Order management - View, process, and track orders',
    'Payment' => 'Payment operations - Upload proof and verify payments',
    'Shipping' => 'Shipping options & calculation - Get shipping rates and methods',
    'Location' => 'Manage shipping locations',
    'Analytics' => 'Business analytics & reports - Sales and platform statistics',
    'User' => 'User management - Admin user operations',
  ];

  public function handle(OpenApi $document, OpenApiContext $context): void
  {
    // Add descriptions to existing tags
    $tags = [];
    foreach ($document->tags as $tag) {
      $tagName = $tag->name;
      $tags[$tagName] = new Tag(
        $tagName,
        $this->tagDescriptions[$tagName] ?? null
      );
    }

    // Also add tags for controllers that might not have tags yet
    foreach ($document->paths as $path) {
      foreach ($path->operations as $operation) {
        foreach ($operation->tags as $tagName) {
          if (!isset($tags[$tagName])) {
            $tags[$tagName] = new Tag(
              $tagName,
              $this->tagDescriptions[$tagName] ?? null
            );
          }

          // Always generate clean summaries - skip ugly URL-style or awkward phrases
          $currentSummary = $operation->summary ?? '';
          $operationId = $operation->operationId ?? '';
          $shouldOverride = empty($currentSummary)
            || preg_match('/^(GET|POST|PUT|PATCH|DELETE)\s+\/api/', $currentSummary)
            || preg_match('/^(Create|List|Get|Update|Delete|View|Ship)\s+\w/', $currentSummary)
            || strlen($currentSummary) > 60;

          if ($shouldOverride) {
            $operation->summary = $this->generateSummary($operation, $path, $operationId);
          }
        }
      }
    }

    $document->tags = array_values($tags);
  }

  /**
   * Generate a human-readable summary for an operation.
   */
  protected function generateSummary($operation, $path, string $operationId = ''): string
  {
    $method = strtolower($operation->method);
    $pathString = $path->path;

    // Try to extract meaningful info from the path
    $pathParts = explode('/', trim($pathString, '/'));

    // Map common patterns to readable titles (check both path and operationId)
    $patterns = [
      'index' => 'List all',
      'show' => 'Get details',
      'store' => 'Create new',
      'update' => 'Update',
      'destroy' => 'Delete',
      'confirm' => 'Confirm',
      'cancel' => 'Cancel',
      'approve' => 'Approve',
      'reject' => 'Reject',
      'verify' => 'Verify',
      'suspend' => 'Suspend',
      'activate' => 'Activate',
      'process' => 'Process',
      'ship' => 'Ship order',
      'upload' => 'Upload payment proof',
      'options' => 'Get shipping options',
      'calculate' => 'Calculate shipping',
      'pending' => 'List pending',
      'my-shop' => 'Get my shop',
      'public' => 'View public',
      'overview' => 'Get overview',
      'top-sellers' => 'Get top sellers',
      'top-products' => 'Get top products',
      'low-stock' => 'Get low stock products',
      'clear' => 'Clear cart',
      'logout' => 'Logout',
      'me' => 'Get current user',
      'login' => 'Login',
      'register' => 'Register',
      'verify-email' => 'Verify email',
      'resend-code' => 'Resend verification code',
      'cod-confirm' => 'Confirm COD payment',
    ];

    // Check both path and operationId for keywords
    $combined = $pathString . ' ' . $operationId;

    foreach ($patterns as $keyword => $titlePrefix) {
      if (str_contains($combined, $keyword)) {
        $resource = $this->getResourceName($pathParts, $operationId);
        return $titlePrefix . ' ' . $resource;
      }
    }

    // Fallback: generate from method and path
    $resource = $this->getResourceName($pathParts, $operationId);
    $actionMap = [
      'get' => 'List',
      'post' => 'Create',
      'put' => 'Update',
      'patch' => 'Update',
      'delete' => 'Delete',
    ];

    return ($actionMap[$method] ?? 'Get') . ' ' . $resource;
  }

  /**
   * Extract resource name from path parts.
   */
  protected function getResourceName(array $pathParts, string $operationId = ''): string
  {
    // Filter out version prefix, empty segments, and parameter placeholders
    $filtered = array_filter($pathParts, function ($part) {
      return $part !== '' && $part !== 'v1' && !str_starts_with($part, '{');
    });

    $parts = array_values($filtered);

    // If operationId contains a resource hint, use it
    if ($operationId) {
      // Extract resource from operationId like "cart.index", "admin.payments.show"
      $idParts = explode('.', $operationId);
      if (count($idParts) > 0) {
        $resourceHint = $idParts[0];
        // Skip common prefixes
        if (!in_array($resourceHint, ['auth', 'admin', 'seller', 'public', 'api'])) {
          return ucwords(str_replace('-', ' ', $resourceHint));
        }
      }
    }

    // Return the last meaningful part
    if (count($parts) > 0) {
      $resource = end($parts);
      // Remove trailing slashes or special chars
      $resource = preg_replace('/[^a-zA-Z0-9\-]/', '', $resource);
      return ucwords(str_replace('-', ' ', $resource));
    }

    return 'Resource';
  }
}
