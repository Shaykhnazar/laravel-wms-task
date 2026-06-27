<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Storage;
use Illuminate\Database\Seeder;

class DemoInventorySeeder extends Seeder
{
    public function run(): void
    {
        $provider = Provider::query()->create(['name' => 'Ahmad Tea Ltd']);
        $storage = Storage::query()->create(['name' => 'Main Warehouse']);
        Client::query()->create(['name' => 'Demo Supermarket']);

        $root = Category::query()->create([
            'name' => 'Ahmad Tea',
            'provider_id' => $provider->id,
        ]);

        $blackTea = Category::query()->create([
            'name' => 'Black Tea',
            'parent_id' => $root->id,
        ]);

        $greenTea = Category::query()->create([
            'name' => 'Green Tea',
            'parent_id' => $root->id,
        ]);

        Product::query()->create([
            'category_id' => $blackTea->id,
            'name' => 'Ahmad Tea Earl Grey, 500g',
            'price' => 19.99,
        ]);

        Product::query()->create([
            'category_id' => $greenTea->id,
            'name' => 'Ahmad Tea Green Tea, 500g',
            'price' => 17.50,
        ]);
    }
}
