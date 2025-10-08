<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Crear únicamente el usuario de pruebas.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'everlazocastilo@gmail.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        echo "\n✅ Usuario de prueba listo:";
        echo "\n   📧 Email: everlazocastilo@gmail.com";
        echo "\n   🔑 Password: password\n";
    }
}