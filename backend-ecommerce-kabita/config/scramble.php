<?php

return [
    /*
     * Path to the directory where Scramble will look for the controllers
     */
    'controllers_path' => [
        app_path('Http/Controllers/Api'),
    ],

    /*
     * The route group to use for the API
     */
    'routes' => [
        'api', // Ini akan scan routes/api.php
    ],

    /*
     * Middleware to apply to the documentation routes
     */
    'middleware' => ['web'],

    /*
     * The path to the API documentation
     */
    'docs_path' => '/docs/api',

    /*
     * The title of the documentation
     */
    'title' => 'Kabita E-Commerce API',

    /*
     * The version of the API
     */
    'version' => '1.0.0',

    /*
     * The description of the API
     */
    'description' => 'API Documentation for Kabita UMKM E-Commerce Platform',


    'extensions' => [
        // Mengizinkan Scramble membaca relasi dan kolom database langsung dari Model Anda
        'Dedoc\\Scramble\\Extensions\\ModelExtension',
    ],

];
