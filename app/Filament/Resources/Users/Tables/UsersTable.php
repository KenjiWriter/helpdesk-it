<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\TextInput;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                TextColumn::make('role')
                    ->label(__('Role'))
                    ->formatStateUsing(fn ($state) => is_string($state) ? ucfirst($state) : $state->label())
                    ->badge()
                    ->color(fn ($state) => match (is_string($state) ? $state : $state->value) {
                        'admin' => 'danger',
                        'it_staff' => 'warning',
                        'user' => 'primary',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('department.name')
                    ->label(__('Department'))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->required()
                            ->minLength(8),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'password' => Hash::make($data['password']),
                        ]);
                    })
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
