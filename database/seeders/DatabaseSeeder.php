<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Phải có dòng này để nó kích hoạt ProductSeeder
        $this->call([
            AdminUserSeeder::class,
            ProductSeeder::class,
        ]);
    }
}