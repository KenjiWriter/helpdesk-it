<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case ItStaff = 'it_staff';
    case Admin = 'admin';

    public function label(): string
    {
        return match($this) {
            UserRole::User    => 'User',
            UserRole::ItStaff => 'IT Staff',
            UserRole::Admin   => 'Admin',
        };
    }

    public function canAccessPanel(): bool
    {
        return match($this) {
            UserRole::ItStaff, UserRole::Admin => true,
            default                            => false,
        };
    }
}
