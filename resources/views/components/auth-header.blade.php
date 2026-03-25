@props([
    'title',
    'description',
])

@php
    $appName = \App\Models\Setting::get('app_name', 'REGANTA Helpdesk');
    $appLogo = \App\Models\Setting::get('app_logo');
    $showLogoOnly = (bool) \App\Models\Setting::get('show_logo_only', false);
    $appLogoUrl = $appLogo ? asset('storage/' . $appLogo) : null;
    $finalBrandName = ($showLogoOnly && $appLogoUrl) ? '' : $appName;
@endphp

<div class="flex flex-col items-center justify-center w-full text-center gap-4">
    <div class="flex flex-col items-center justify-center">
        @if ($appLogoUrl)
            <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-12 w-auto mb-2 {{ $showLogoOnly ? 'scale-110' : '' }}" />
        @endif
        @if ($finalBrandName)
            <div class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">
                {{ $finalBrandName }}
            </div>
        @endif
    </div>

    <div class="flex flex-col w-full">
        <flux:heading size="xl">{{ $title }}</flux:heading>
        <flux:subheading>{{ $description }}</flux:subheading>
    </div>
</div>
