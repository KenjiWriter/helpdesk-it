<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class AssignUserRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:role {email} {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a role to a user by email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $roleInput = $this->argument('role');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email '{$email}' not found.");
            return Command::FAILURE;
        }

        $role = UserRole::tryFrom($roleInput);

        if (! $role) {
            $validRoles = collect(UserRole::cases())->map(fn ($r) => $r->value)->implode(', ');
            $this->error("Invalid role '{$roleInput}'. Valid roles are: {$validRoles}.");
            return Command::FAILURE;
        }

        $user->role = $role;
        $user->save();

        $this->info("Successfully assigned role '{$role->label()}' to {$user->name} ({$user->email}).");

        return Command::SUCCESS;
    }
}
