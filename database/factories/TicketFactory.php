<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'department_id' => null,
            'assignee_id'   => null,
            'priority'      => TicketPriority::Normal,
            'category'      => fake()->randomElement(TicketCategory::cases()),
            'status'        => TicketStatus::New,
            'description'   => fake()->paragraph(),
            'hardware_name' => null,
            'resolved_at'   => null,
            'rating_time'   => null,
            'rating_quality' => null,
        ];
    }
}
