<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$credentials = [
    'email' => 'user2@example.com',
    'password' => 'password',
];

echo "Attempting login for user2@example.com with password 'password'..." . PHP_EOL;

if (Auth::guard('web')->attempt($credentials)) {
    echo "Login successful!" . PHP_EOL;
    $user = Auth::user();
    echo "Logged in as: {$user->email} (Role: {$user->role->value})" . PHP_EOL;
} else {
    echo "Login failed." . PHP_EOL;
    $user = User::where('email', 'user2@example.com')->first();
    if ($user) {
        echo "User exists in DB." . PHP_EOL;
        echo "Password hash in DB: {$user->password}" . PHP_EOL;
        echo "Direct Hash Check: " . (\Illuminate\Support\Facades\Hash::check('password', $user->password) ? 'Success' : 'Fail') . PHP_EOL;
    } else {
        echo "User NOT found in DB." . PHP_EOL;
    }
}
