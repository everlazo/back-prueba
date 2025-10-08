<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Sembrar solo el usuario de pruebas; no crear favoritos
        $this->call([
            TestUserSeeder::class,
        ]);
    }
}
