<?php

use App\Exceptions\InsufficientStockException;
use App\Services\OrderService;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$clientId = (int) ($argv[1] ?? 0);
$productId = (int) ($argv[2] ?? 0);
$quantity = (int) ($argv[3] ?? 0);

try {
    app(OrderService::class)->create($clientId, [
        ['id' => $productId, 'qty' => $quantity],
    ]);

    fwrite(STDOUT, 'success');
    exit(0);
} catch (InsufficientStockException) {
    fwrite(STDOUT, 'insufficient');
    exit(2);
} catch (\Throwable $exception) {
    fwrite(STDERR, $exception->getMessage());
    exit(1);
}
