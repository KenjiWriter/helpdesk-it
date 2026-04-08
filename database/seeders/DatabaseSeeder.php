<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create IT Department
        $itDepartment = \App\Models\Department::firstOrCreate(['name' => 'IT']);

        // Create Admin
        User::factory()->admin()->create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create 2 IT Staff
        User::factory()->itStaff()->create([
            'name' => 'Kamil Technik',
            'email' => 'it1@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->itStaff()->create([
            'name' => 'Marek Serwisant',
            'email' => 'it2@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create 10 Regular Users
        User::factory()->create([
            'name' => 'Anna Kowalska',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Jan Nowak',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory(8)->create([
            'password' => bcrypt('password'),
        ]);

        // Run Ticket Seeder
        $this->call(TicketSeeder::class);
    }
}
