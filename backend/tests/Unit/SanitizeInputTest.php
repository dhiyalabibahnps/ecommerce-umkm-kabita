<?php

namespace Tests\Unit;

use App\Http\Middleware\SanitizeInput;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;

class SanitizeInputTest extends TestCase
{
    public function test_it_strips_html_tags_without_encoding_ampersand(): void
    {
        $middleware = new SanitizeInput();
        $request = Request::create('/api/test', 'POST', [
            'courier' => '<b>J&T Express SUPER</b>',
            'notes' => 'Pengiriman <i>aman</i> & cepat',
        ]);

        $response = $middleware->handle($request, function ($req) {
            $this->assertEquals('J&T Express SUPER', $req->input('courier'));
            $this->assertEquals('Pengiriman aman & cepat', $req->input('notes'));
            return new Response('ok');
        });

        $this->assertNotNull($response);
    }
}
