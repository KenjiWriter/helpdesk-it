---
name: i18n_and_user_management
description: Documentation on the i18n setup and strict Admin UserPolicy.
---

# i18n & User Management — IT Helpdesk

> Read this file before modifying localization (i18n) settings or the User management policies.

---

## Internationalization (i18n)

The IT Helpdesk application is configured to support Polish (`pl`) as the default language and English (`en`) as the fallback language.

- **Locale Settings**: `config/app.php` sets `'locale' => 'pl'` and `'fallback_locale' => 'en'`.
- **Language Files**: Storage for translation strings is located in the `lang/pl/` and `lang/en/` directories in the project root. These directories were created via `php artisan lang:publish` and manual creation.

When adding user-facing strings, utilize Laravel's translation functions (e.g., `__('string')`) and define the translations in the appropriate JSON or PHP files within these directories.

---

## Admin User Management

Administrators can manage users via the `UserResource` in the Filament panel.

### Strict `UserPolicy`

To ensure that only Administrators can manage users, the application implements a strict `UserPolicy` (`app/Policies/UserPolicy.php`).

- **Intercepting Checks**: The `before()` method intercepts all authorization checks for the `User` model.
- **Admin Only**: It strictly returns `true` **only** if the user's role is `UserRole::Admin`. All other roles (including `it_staff`) receive `false`.

```php
// app/Policies/UserPolicy.php
public function before(User $user, string $ability): ?bool
{
    if ($user->role === UserRole::Admin) {
        return true;
    }

    return false;
}
```

### Reset Password Action

The `UserResource` features a custom "Reset Password" action located on both the table rows (`UsersTable.php`) and the edit page (`EditUser.php`). This allows Administrators to securely force a password reset for any user. The action prompts for a new password, which is then dynamically hashed (`Hash::make()`) and saved to the user's record.
