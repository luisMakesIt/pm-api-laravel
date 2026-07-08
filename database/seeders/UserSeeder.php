<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'name' => 'Admin User',
                'email' => 'admin@pmapi.local',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'github_username' => 'admin',
            ],
            [
                'name' => 'Carlos Developer',
                'email' => 'carlos@pmapi.local',
                'password' => Hash::make('password'),
                'role' => 'developer',
                'github_username' => 'carlosdev',
            ],
            [
                'name' => 'Ana Designer',
                'email' => 'ana@pmapi.local',
                'password' => Hash::make('password'),
                'role' => 'designer',
                'github_username' => 'anadesigner',
            ],
            [
                'name' => 'Luis Tester',
                'email' => 'luis@pmapi.local',
                'password' => Hash::make('password'),
                'role' => 'tester',
                'github_username' => 'luisqa',
            ],
            [
                'name' => 'Maria Tech Lead',
                'email' => 'maria@pmapi.local',
                'password' => Hash::make('password'),
                'role' => 'tech_lead',
                'github_username' => 'marialead',
            ],
            [
                'name' => 'Pedro Developer',
                'email' => 'pedro@pmapi.local',
                'password' => Hash::make('password'),
                'role' => 'developer',
                'github_username' => 'pedrodev',
            ],
            [
                'name' => 'Sofia Junior Dev',
                'email' => 'sofia@pmapi.local',
                'password' => Hash::make('password'),
                'role' => 'developer',
                'github_username' => 'sofiadev',
            ],
        ];

        foreach ($defaults as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }

        // Create additional random users
        User::factory()->count(5)->create();
    }
}
