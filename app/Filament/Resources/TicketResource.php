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
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Tickets';

    protected static ?string $modelLabel = 'Ticket';

    protected static ?int $navigationSort = 1;

    // ─── Form ────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Request Details')
                ->description('Original ticket information submitted by the user. Read-only.')
                ->columns(2)
                ->schema([
                    TextInput::make('user.name')
                        ->label('Submitted By')
                        ->disabled(),

                    TextInput::make('department.name')
                        ->label('Department')
                        ->disabled(),

                    Textarea::make('description')
                        ->label('Description')
                        ->disabled()
                        ->rows(5)
                        ->columnSpanFull(),

                    TextInput::make('hardware_name')
                        ->label('Hardware / Asset')
                        ->disabled(),
                ]),

            Section::make('IT Management')
                ->description('Fields IT staff can update.')
                ->columns(2)
                ->schema([
                    Select::make('priority')
                        ->label('Priority')
                        ->options(TicketPriority::class)
                        ->required(),

                    Select::make('category')
                        ->label('Category')
                        ->options(TicketCategory::class)
                        ->required(),

                    Select::make('status')
                        ->label('Status')
                        ->options(TicketStatus::class)
                        ->required(),

                    Select::make('assignee_id')
                        ->label('Assigned To')
                        ->relationship(
                            name: 'assignee',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) => $query->where('role', UserRole::ItStaff->value),
                        )
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    DateTimePicker::make('resolved_at')
                        ->label('Resolved At')
                        ->nullable(),
                ]),

            Section::make('User Ratings')
                ->description('Submitted by the user after ticket resolution.')
                ->columns(2)
                ->schema([
                    TextInput::make('rating_time')
                        ->label('Time Rating (1–6)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(6)
                        ->disabled(),

                    TextInput::make('rating_quality')
                        ->label('Quality Rating (1–6)')
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
                    ->label('Priority')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->sortable(),

                BadgeColumn::make('category')
                    ->label('Category'),

                TextColumn::make('user.name')
                    ->label('Submitted By')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('assignee.name')
                    ->label('Assignee')
                    ->placeholder('Unassigned')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('department.name')
                    ->label('Department')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Opened')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(TicketStatus::class)
                    ->multiple(),

                SelectFilter::make('priority')
                    ->label('Priority')
                    ->options(TicketPriority::class)
                    ->multiple(),

                SelectFilter::make('category')
                    ->label('Category')
                    ->options(TicketCategory::class)
                    ->multiple(),

                SelectFilter::make('assignee_id')
                    ->label('Assignee')
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
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
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
