<?php

namespace App\Providers;

use App\Extensions\AutoGroupTransformer;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\ServiceProvider;

/**
 * ScrambleExtensionProvider
 *
 * This service provider configures Scramble (OpenAPI documentation generator)
 * for the Kabita E-Commerce API. It adds custom tag descriptions and
 * configures Bearer token authentication documentation.
 *
 * @see https://scramble.dedoc.co
 */
class ScrambleExtensionProvider extends ServiceProvider
{
    public function boot(): void
    {
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi, \Dedoc\Scramble\OpenApiContext $context) {
            // Add descriptions to each tag group
            $transformer = new AutoGroupTransformer();
            $transformer->handle($openApi, $context);

            // Configure Bearer Token Security Scheme
            $openApi->secure(
                SecurityScheme::http('bearer')
                    ->setDescription('Enter your Bearer token in the format: Bearer {token}')
            );
        });
    }

    public function register(): void
    {
        //
    }
}
