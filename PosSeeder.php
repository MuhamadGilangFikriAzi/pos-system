<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class PosSeeder extends Seeder
{
    public function run(): void
    {
        // Kategori
        $categories = [
            ['name' => 'Makanan', 'description' => 'Makanan ringan dan berat'],
            ['name' => 'Minuman', 'description' => 'Minuman kemasan dan botol'],
            ['name' => 'Snack', 'description' => 'Camilan dan kudapan'],
        ];

        foreach ($categories as $c) {
            Category::create($c);
        }

        $this->command->info('Categories created: ' . Category::count());

        // Produk
        $products = [
            ['code' => 'BRG001', 'name' => 'Nasi Goreng', 'category_id' => 1, 'purchase_price' => 10000, 'selling_price' => 15000, 'stock' => 50, 'unit' => 'pcs'],
            ['code' => 'BRG002', 'name' => 'Mie Ayam', 'category_id' => 1, 'purchase_price' => 8000, 'selling_price' => 12000, 'stock' => 40, 'unit' => 'pcs'],
            ['code' => 'BRG003', 'name' => 'Air Mineral 600ml', 'category_id' => 2, 'purchase_price' => 3000, 'selling_price' => 5000, 'stock' => 100, 'unit' => 'botol'],
            ['code' => 'BRG004', 'name' => 'Teh Manis', 'category_id' => 2, 'purchase_price' => 3000, 'selling_price' => 5000, 'stock' => 60, 'unit' => 'gelas'],
            ['code' => 'BRG005', 'name' => 'Keripik Singkong', 'category_id' => 3, 'purchase_price' => 5000, 'selling_price' => 8000, 'stock' => 3, 'unit' => 'pcs'],
            ['code' => 'BRG006', 'name' => 'Kue Cubit', 'category_id' => 3, 'purchase_price' => 6000, 'selling_price' => 10000, 'stock' => 25, 'unit' => 'pcs'],
            ['code' => 'BRG007', 'name' => 'Kopi Hitam', 'category_id' => 2, 'purchase_price' => 4000, 'selling_price' => 7000, 'stock' => 45, 'unit' => 'gelas'],
            ['code' => 'BRG008', 'name' => 'Roti Bakar', 'category_id' => 1, 'purchase_price' => 7000, 'selling_price' => 12000, 'stock' => 20, 'unit' => 'pcs'],
        ];

        foreach ($products as $p) {
            Product::create($p);
        }

        $this->command->info('Products created: ' . Product::count());
    }
}
