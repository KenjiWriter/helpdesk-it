@php
    $appName = \App\Models\Setting::get('app_name', 'REGANTA Helpdesk');
    $appLogo = \App\Models\Setting::get('app_logo');
    $showLogoOnly = (bool) \App\Models\Setting::get('show_logo_only', false);
@endphp
@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="$showLogoOnly ? '' : $appName" {{ $attributes }}>
        <x-slot name="logo" class="{{ $appLogo ? 'flex size-8 items-center justify-center -ml-1.5' : 'flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground' }}">
            @if($appLogo)
                <img src="{{ asset('storage/' . $appLogo) }}" alt="{{ $appName }}" class="max-h-8 max-w-full object-contain" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$showLogoOnly ? '' : $appName" {{ $attributes }}>
        <x-slot name="logo" class="{{ $appLogo ? 'flex size-8 items-center justify-center -ml-1.5' : 'flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground' }}">
            @if($appLogo)
                <img src="{{ asset('storage/' . $appLogo) }}" alt="{{ $appName }}" class="max-h-8 max-w-full object-contain" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:brand>
@endif
