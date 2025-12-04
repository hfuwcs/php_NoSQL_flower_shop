<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Tests\RefreshMongoDB;

class CsrfDebugTest extends TestCase
{
    use RefreshMongoDB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRefreshMongoDB();
    }

    public function test_csrf_with_explicit_withoutMiddleware(): void
    {
        // Explicitly disable CSRF middleware for this specific test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Test Product',
            'price' => 10,
            'stock_quantity' => 100,
        ]);

        $response = $this->actingAs($user)
            ->post(route('cart.add', $product), [
                'quantity' => 1
            ]);

        // Should not be 419
        $this->assertNotEquals(419, $response->status(), 'CSRF should be disabled');
        $response->assertRedirect();
    }

    public function test_csrf_with_token_in_session(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Test Product 2',
            'price' => 10,
            'stock_quantity' => 100,
        ]);

        // Get session token first
        $this->actingAs($user)->get(route('cart.index'));
        
        $response = $this->actingAs($user)
            ->post(route('cart.add', $product), [
                'quantity' => 1,
                '_token' => csrf_token(),
            ]);

        $this->assertNotEquals(419, $response->status(), 'CSRF token should be valid');
    }
}
