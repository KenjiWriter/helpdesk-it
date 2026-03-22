<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    public static function getNavigationLabel(): string
    {
        return __('Tickets');
    }

    public static function getModelLabel(): string
    {
        return __('Ticket');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tickets');
    }

    protected static ?int $navigationSort = 1;

    // ─── Form ────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make(__('Request Details'))
                ->description(__('Original ticket information submitted by the user. Read-only.'))
                ->columns(2)
                ->schema([
                    TextInput::make('user.name')
                        ->label(__('Submitted By'))
                        ->disabled(),

                    TextInput::make('department.name')
                        ->label(__('Department'))
                        ->disabled(),

                    Textarea::make('description')
                        ->label(__('Description'))
                        ->disabled()
                        ->rows(5)
                        ->columnSpanFull(),

                    TextInput::make('hardware_name')
                        ->label(__('Hardware / Asset'))
                        ->disabled(),
                ]),

            Section::make(__('IT Management'))
                ->description(__('Fields IT staff can update.'))
                ->columns(2)
                ->schema([
                    Select::make('priority')
                        ->label(__('Priority'))
                        ->options(TicketPriority::class)
                        ->required(),

                    Select::make('category')
                        ->label(__('Category'))
                        ->options(TicketCategory::class)
                        ->required(),

                    Select::make('status')
                        ->label(__('Status'))
                        ->options(TicketStatus::class)
                        ->required(),

                    Select::make('assignee_id')
                        ->label(__('Assigned To'))
                        ->relationship(
                            name: 'assignee',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) => $query->where('role', UserRole::ItStaff->value),
                        )
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    DateTimePicker::make('resolved_at')
                        ->label(__('Resolved At'))
                        ->nullable(),
                ]),

            Section::make(__('User Ratings'))
                ->description(__('Submitted by the user after ticket resolution.'))
                ->columns(2)
                ->schema([
                    TextInput::make('rating_time')
                        ->label(__('Time Rating (1–6)'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(6)
                        ->disabled(),

                    TextInput::make('rating_quality')
                        ->label(__('Quality Rating (1–6)'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(6)
                        ->disabled(),
                ]),

        ]);
    }

    // ─── Table ───────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),

                BadgeColumn::make('priority')
                    ->label(__('Priority'))
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->sortable(),

                BadgeColumn::make('category')
                    ->label(__('Category')),

                TextColumn::make('user.name')
                    ->label(__('Submitted By'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('assignee.name')
                    ->label(__('Assignee'))
                    ->placeholder(__('Unassigned'))
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('department.name')
                    ->label(__('Department'))
                    ->placeholder(__('—'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('Opened'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(TicketStatus::class)
                    ->multiple(),

                SelectFilter::make('priority')
                    ->label(__('Priority'))
                    ->options(TicketPriority::class)
                    ->multiple(),

                SelectFilter::make('category')
                    ->label(__('Category'))
                    ->options(TicketCategory::class)
                    ->multiple(),

                SelectFilter::make('assignee_id')
                    ->label(__('Assignee'))
                    ->relationship(
                        name: 'assignee',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->where('role', UserRole::ItStaff->value),
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->actions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ─── Pages ───────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit'   => Pages\EditTicket::route('/{record}/edit'),
            'view'   => Pages\ViewTicket::route('/{record}'),
        ];
    }
}
