<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketMessage;
use App\Enums\UserRole;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get Regular Users (ensure we have them from DatabaseSeeder)
        $regularUsers = User::where('role', UserRole::User)->get();
        
        // 2. Get IT Staff
        $itStaff = User::where('role', UserRole::ItStaff)->get();

        if ($regularUsers->isEmpty() || $itStaff->isEmpty()) {
            return;
        }

        // 3. Generate exactly 30 tickets
        for ($i = 0; $i < 30; $i++) {
            $user = $regularUsers->random();
            // Random creation date between 30 days ago and 2 days ago
            $createdAt = Carbon::now()->subDays(rand(2, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            Ticket::withoutEvents(function () use ($user, $createdAt, $itStaff) {
                $ticket = Ticket::create([
                    'user_id' => $user->id,
                    'priority' => TicketPriority::Normal,
                    'category' => fake()->randomElement(TicketCategory::cases()),
                    'status' => TicketStatus::New,
                    'description' => fake()->paragraph(),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Initial History: "Zgłoszenie utworzone"
                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'description' => 'Zgłoszenie utworzone',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // 4. Randomly Assign (~20 of 30 tickets)
                // We use a counter or a probability to hit exactly/roughly 20.
                // Let's use probability but ensure we have IT staff assigned.
                if (rand(1, 100) <= 70) { // ~70% chance to be assigned
                    $staff = $itStaff->random();
                    // Advance 1 to 24 hours
                    $assignedAt = (clone $createdAt)->addHours(rand(1, 24));
                    
                    if ($assignedAt->isBefore(now())) {
                        $ticket->update([
                            'assignee_id' => $staff->id,
                            'status' => TicketStatus::InProgress,
                            'updated_at' => $assignedAt,
                        ]);

                        TicketHistory::create([
                            'ticket_id' => $ticket->id,
                            'user_id' => $staff->id,
                            'description' => "Przypisano do: {$staff->name}",
                            'created_at' => $assignedAt,
                            'updated_at' => $assignedAt,
                        ]);

                        // 5. Randomly Resolve (~15 of those assigned)
                        if (rand(1, 100) <= 75) { // ~75% chance of assigned being resolved
                            // Advance another 1 to 48 hours
                            $resolvedAt = (clone $assignedAt)->addHours(rand(1, 48));
                            
                            if ($resolvedAt->isBefore(now())) {
                                $ticket->update([
                                    'status' => TicketStatus::Resolved,
                                    'resolved_at' => $resolvedAt,
                                    'updated_at' => $resolvedAt,
                                ]);

                                // Resolution Note
                                TicketMessage::create([
                                    'ticket_id' => $ticket->id,
                                    'user_id' => $staff->id,
                                    'body' => 'Problem został rozwiązany. Prosimy o sprawdzenie poprawności działania.',
                                    'created_at' => $resolvedAt,
                                    'updated_at' => $resolvedAt,
                                ]);

                                TicketHistory::create([
                                    'ticket_id' => $ticket->id,
                                    'user_id' => $staff->id,
                                    'description' => 'Status zmieniony na: Rozwiązany',
                                    'created_at' => $resolvedAt,
                                    'updated_at' => $resolvedAt,
                                ]);
                            }
                        }
                    }
                }
            });
        }
    }
}
