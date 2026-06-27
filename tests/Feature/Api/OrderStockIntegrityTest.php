<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Support\InventoryScenario;
use Tests\TestCase;

class OrderStockIntegrityTest extends TestCase
{
    use InventoryScenario;
    use LazilyRefreshDatabase;

    public function test_rapid_orders_via_api_never_oversell(): void
    {
        $client = $this->client();
        ['product' => $product] = $this->stock(100);

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/orders', [
                'client_id' => $client->id,
                'products' => [['id' => $product->id, 'qty' => 10]],
            ])->assertCreated();
        }

        $this->assertSame(0, $this->totalRemaining());

        $this->postJson('/api/orders', [
            'client_id' => $client->id,
            'products' => [['id' => $product->id, 'qty' => 1]],
        ])->assertUnprocessable();
    }
}
