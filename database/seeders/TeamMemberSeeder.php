<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeamMember;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        // Project 1 - User Management (has 5 requirements, multiple team)
        $defaults = [
            // Carlos - lead developer
            ['project_id' => 1, 'name' => 'Carlos Developer', 'email' => 'carlos@pmapi.local', 'role' => 'developer', 'nivel_experiencia' => 'senior', 'estado' => 'en_tarea', 'joined_date' => '2024-06-01', 'git_username' => 'carlosdev', 'github_url' => 'https://github.com/carlosdev', 'dev_id' => 2],
            // Ana - UI designer
            ['project_id' => 1, 'name' => 'Ana Designer', 'email' => 'ana@pmapi.local', 'role' => 'designer', 'nivel_experiencia' => 'middle', 'estado' => 'ocupado', 'joined_date' => '2024-08-15', 'git_username' => 'anadesigner', 'github_url' => 'https://github.com/anadesigner', 'dev_id' => 3],
            // Maria - tech lead
            ['project_id' => 1, 'name' => 'Maria Tech Lead', 'email' => 'maria@pmapi.local', 'role' => 'tech_lead', 'nivel_experiencia' => 'lead', 'estado' => 'disponible', 'joined_date' => '2024-01-15', 'git_username' => 'marialead', 'github_url' => 'https://github.com/marialead', 'dev_id' => 5],
            // Luis - QA
            ['project_id' => 1, 'name' => 'Luis Tester', 'email' => 'luis@pmapi.local', 'role' => 'tester', 'nivel_experiencia' => 'middle', 'estado' => 'disponible', 'joined_date' => '2024-09-01', 'git_username' => 'luisqa', 'github_url' => 'https://github.com/luisqa', 'dev_id' => 4],
            // Sofia - junior
            ['project_id' => 1, 'name' => 'Sofia Junior Dev', 'email' => 'sofia@pmapi.local', 'role' => 'developer', 'nivel_experiencia' => 'junior', 'estado' => 'disponible', 'joined_date' => '2025-01-06', 'git_username' => 'sofiadev', 'github_url' => 'https://github.com/sofiadev', 'dev_id' => 7],

            // Project 2 - Payment API
            ['project_id' => 2, 'name' => 'Carlos Developer', 'email' => 'carlos@pmapi.local', 'role' => 'developer', 'nivel_experiencia' => 'senior', 'estado' => 'en_tarea', 'joined_date' => '2024-06-01', 'git_username' => 'carlosdev', 'github_url' => 'https://github.com/carlosdev', 'dev_id' => 2],
            ['project_id' => 2, 'name' => 'Pedro Developer', 'email' => 'pedro@pmapi.local', 'role' => 'developer', 'nivel_experiencia' => 'middle', 'estado' => 'en_tarea', 'joined_date' => '2024-10-15', 'git_username' => 'pedrodev', 'github_url' => 'https://github.com/pedrodev', 'dev_id' => 6],
            ['project_id' => 2, 'name' => 'Maria Tech Lead', 'email' => 'maria@pmapi.local', 'role' => 'tech_lead', 'nivel_experiencia' => 'lead', 'estado' => 'disponible', 'joined_date' => '2024-01-15', 'git_username' => 'marialead', 'github_url' => 'https://github.com/marialead', 'dev_id' => 5],
            ['project_id' => 2, 'name' => 'Luis Tester', 'email' => 'luis@pmapi.local', 'role' => 'tester', 'nivel_experiencia' => 'middle', 'estado' => 'fuera', 'joined_date' => '2024-09-01', 'git_username' => 'luisqa', 'github_url' => 'https://github.com/luisqa', 'dev_id' => 4],

            // Project 3 - Mobile App
            ['project_id' => 3, 'name' => 'Ana Designer', 'email' => 'ana@pmapi.local', 'role' => 'designer', 'nivel_experiencia' => 'middle', 'estado' => 'disponible', 'joined_date' => '2024-08-15', 'git_username' => 'anadesigner', 'github_url' => 'https://github.com/anadesigner', 'dev_id' => 3],
            ['project_id' => 3, 'name' => 'Carlos Developer', 'email' => 'carlos@pmapi.local', 'role' => 'developer', 'nivel_experiencia' => 'senior', 'estado' => 'ocupado', 'joined_date' => '2024-06-01', 'git_username' => 'carlosdev', 'github_url' => 'https://github.com/carlosdev', 'dev_id' => 2],

            // Project 4 - Analytics Dashboard
            ['project_id' => 4, 'name' => 'Maria Tech Lead', 'email' => 'maria@pmapi.local', 'role' => 'tech_lead', 'nivel_experiencia' => 'lead', 'estado' => 'disponible', 'joined_date' => '2024-01-15', 'git_username' => 'marialead', 'github_url' => 'https://github.com/marialead', 'dev_id' => 5],
            ['project_id' => 4, 'name' => 'Carlos Developer', 'email' => 'carlos@pmapi.local', 'role' => 'developer', 'nivel_experiencia' => 'senior', 'estado' => 'en_tarea', 'joined_date' => '2024-06-01', 'git_username' => 'carlosdev', 'github_url' => 'https://github.com/carlosdev', 'dev_id' => 2],
            ['project_id' => 4, 'name' => 'Sofia Junior Dev', 'email' => 'sofia@pmapi.local', 'role' => 'developer', 'nivel_experiencia' => 'junior', 'estado' => 'disponible', 'joined_date' => '2025-01-06', 'git_username' => 'sofiadev', 'github_url' => 'https://github.com/sofiadev', 'dev_id' => 7],
        ];

        foreach ($defaults as $member) {
            TeamMember::firstOrCreate(
                [
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'project_id' => $member['project_id'],
                ],
                $member
            );
        }
    }
}
