<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),
                Select::make('role')
                    ->label(__('Role'))
                    ->options([
                        'user' => 'User',
                        'it_staff' => 'IT Staff',
                        'admin' => 'Admin',
                    ])
                    ->default('user')
                    ->required(),
                Select::make('department_id')
                    ->label(__('Department'))
                    ->relationship('department', 'name')
                    ->required(),
            ]);
    }
}
