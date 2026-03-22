<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$emails = ['admin@example.com', 'user2@example.com'];

foreach ($emails as $email) {
    echo "--- Testing: $email ---" . PHP_EOL;
    $credentials = [
        'email' => $email,
        'password' => 'password',
    ];

    if (Auth::guard('web')->attempt($credentials)) {
        echo "Login successful for $email!" . PHP_EOL;
        Auth::logout();
    } else {
        echo "Login FAILED for $email." . PHP_EOL;
        $user = User::where('email', $email)->first();
        if ($user) {
            echo "Hash in DB: {$user->password}" . PHP_EOL;
            echo "Direct Check: " . (\Illuminate\Support\Facades\Hash::check('password', $user->password) ? 'Success' : 'Fail') . PHP_EOL;
        }
    }
}
