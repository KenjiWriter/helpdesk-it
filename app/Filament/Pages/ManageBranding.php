<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Enums\UserRole;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;

class ManageBranding extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.manage-branding';

    public ?array $data = [];

    public static function getNavigationSort(): ?int
    {
        return 100;
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-paint-brush';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role === UserRole::Admin;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Ustawienia');
    }

    public static function getNavigationLabel(): string
    {
        return __('Ustawienia wyglądu');
    }

    public function getTitle(): string
    {
        return __('Ustawienia wyglądu (Branding)');
    }

    public function mount(): void
    {
        $this->form->fill([
            'app_name' => Setting::get('app_name', 'IT Helpdesk'),
            'app_logo' => Setting::get('app_logo'),
            'show_logo_only' => (bool) Setting::get('show_logo_only', false),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('Główne Ustawienia'))
                    ->components([
                        TextInput::make('app_name')
                            ->label(__('Nazwa Aplikacji'))
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('app_logo')
                            ->label(__('Logo Aplikacji'))
                            ->image()
                            ->disk('public')
                            ->directory('logos')
                            ->nullable(),
                        Toggle::make('show_logo_only')
                            ->label(__('Wyświetlaj tylko logo'))
                            ->default(false),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('app_name', $data['app_name']);
        Setting::set('app_logo', $data['app_logo'] ?? null);
        Setting::set('show_logo_only', $data['show_logo_only'] ?? false);

        Notification::make()
            ->title(__('Zapisano'))
            ->body(__('Ustawienia wyglądu zostały pomyślnie zaktualizowane.'))
            ->success()
            ->send();
    }
}
