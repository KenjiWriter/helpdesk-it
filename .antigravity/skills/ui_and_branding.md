---
description: Documentation on UI customization, the REGANTA brand, and Filament panel configurations.
---

# UI Customization & Corporate Branding

This application is customized for **REGANTA**. The UI styling overrides the default Laravel and Flux themes to present a professional, enterprise-grade look tailored to the client's corporate identity.

## Brand Colors

- **Primary (Reganta Orange):** `#F37021`
- **Secondary:** Black (and Zinc scales provided by Tailwind)

## Customizing the Flux Frontend

The user-facing frontend utilizes [Flux UI](https://fluxui.dev/) and Tailwind CSS. We have completely overridden the default `color-accent` variables in `resources/css/app.css` to use a custom Reganta Orange palette.

### Tailwind / Flux Palette Configuration (`app.css`)

```css
@theme {
    --color-accent-50: #fff3e6;
    --color-accent-100: #ffecd3;
    --color-accent-200: #ffd4a5;
    --color-accent-300: #ffb870;
    --color-accent-400: #ff913b;
    --color-accent-500: #f37021; /* Reganta Primary */
    --color-accent-600: #e25813;
    --color-accent-700: #be4011;
    --color-accent-800: #973216;
    --color-accent-900: #7a2b15;
    --color-accent-950: #421308;

    --color-accent: var(--color-accent-500);
    --color-accent-content: var(--color-accent-600);
    --color-accent-foreground: var(--color-white);
}

@layer theme {
    .dark {
        --color-accent: var(--color-accent-500);
        --color-accent-content: var(--color-accent-400);
        --color-accent-foreground: var(--color-white);
    }
}
```

Whenever adding primary buttons, active states, or loading spinners (`wire:loading`), you must use the `text-accent`, `bg-accent`, or `ring-accent` utility classes to ensure the colors align with the brand.

## Customizing the Filament Panel

The IT Staff IT Helpdesk panel (`/helpdesk`) is powered by Filament. Branding is configured centrally within the `HelpdeskPanelProvider.php`.

```php
// app/Providers/Filament/HelpdeskPanelProvider.php
->brandName('REGANTA Helpdesk')
->colors([
    'primary' => Color::hex('#F37021'),
])
```

## i18n Verification

The application assumes Polish (`pl`) as the default language.
1. `APP_LOCALE=pl` must be set in the `.env`.
2. Filament handles its own core translations internally when `APP_LOCALE` is correctly set to `pl`.
3. Custom User components rely on default Laravel translation resources (`lang/pl/**/*.php`) if defined, otherwise falling back to `en`.
