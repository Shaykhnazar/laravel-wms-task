<?php

namespace Tests\Stress;

use App\Exceptions\InsufficientStockException;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\Process\Process;
use Tests\Support\InventoryScenario;
use Tests\TestCase;

/**
 * Stock integrity under concurrent load — requires MySQL (see phpunit.stress.xml).
 *
 * php artisan test --configuration=phpunit.stress.xml
 *
 * @group stress
 */
class ConcurrentOrderStressTest extends TestCase
{
    use DatabaseMigrations;
    use InventoryScenario;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'mysql') {
            $this->markTestSkipped('Stress tests require MySQL (phpunit.stress.xml).');
        }
    }

    public function test_burst_orders_via_service_never_oversell(): void
    {
        $client = $this->client();
        ['product' => $product] = $this->stock(100);
        $service = app(OrderService::class);

        $successes = 0;
        $rejections = 0;

        for ($i = 0; $i < 50; $i++) {
            try {
                $service->create($client->id, [['id' => $product->id, 'qty' => 3]]);
                $successes++;
            } catch (InsufficientStockException) {
                $rejections++;
            }
        }

        $this->assertSame(33, $successes);
        $this->assertSame(17, $rejections);
        $this->assertSame(1, $this->totalRemaining());
    }

    public function test_two_processes_competing_for_same_stock(): void
    {
        $clientId = $this->client()->id;
        ['product' => $product] = $this->stock(100);
        $worker = base_path('tests/Stress/concurrent_order_worker.php');

        $first = $this->workerProcess($worker, $clientId, $product->id, 60);
        $second = $this->workerProcess($worker, $clientId, $product->id, 60);

        $first->start();
        $second->start();
        $first->wait();
        $second->wait();

        $exitCodes = [$first->getExitCode(), $second->getExitCode()];
        sort($exitCodes);

        $this->assertSame([0, 2], $exitCodes);
        $this->assertSame(40, $this->totalRemaining());
    }

    protected function workerProcess(string $script, int $clientId, int $productId, int $qty): Process
    {
        return new Process([
            PHP_BINARY,
            $script,
            (string) $clientId,
            (string) $productId,
            (string) $qty,
        ], base_path(), $this->workerEnvironment());
    }

    /**
     * @return array<string, string>
     */
    protected function workerEnvironment(): array
    {
        return array_merge($_ENV, [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => config('database.default'),
            'DB_HOST' => config('database.connections.mysql.host'),
            'DB_PORT' => (string) config('database.connections.mysql.port'),
            'DB_DATABASE' => config('database.connections.mysql.database'),
            'DB_USERNAME' => config('database.connections.mysql.username'),
            'DB_PASSWORD' => (string) config('database.connections.mysql.password'),
        ]);
    }
}
