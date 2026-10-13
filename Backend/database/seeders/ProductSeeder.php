<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
        'name' => 'Baby Blossom',
        'category' => 'Babies',
        'age_group' => 0,
        'price' => 120.00,
        'description' => 'A gentle fragrance specially made for babies.',
        'image' => 'baby-blossom.jpg',
        'stock' => 20,
    ]);

    Product::create([
        'name' => 'Princess Bloom',
        'category' => 'Children',
        'age_group' => 8,
        'price' => 180.00,
        'description' => 'A sweet floral scent for young girls.',
        'image' => 'princess-bloom.jpg',
        'stock' => 15,
    ]);

    Product::create([
        'name' => 'Dream Spark',
        'category' => 'Teens',
        'age_group' => 16,
        'price' => 250.00,
        'description' => 'Fresh and elegant fragrance for teenagers.',
        'image' => 'dream-spark.jpg',
        'stock' => 10,
    ]);

    Product::create([
        'name' => 'Royal Aura',
        'category' => 'Young Adults',
        'age_group' => 20,
        'price' => 350.00,
        'description' => 'A luxurious perfume for young women.',
        'image' => 'royal-aura.jpg',
        'stock' => 12,
    ]);
}
    }
