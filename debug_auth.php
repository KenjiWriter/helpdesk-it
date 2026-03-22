<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'user2@example.com')->first();
if ($user) {
    echo "User: {$user->email}" . PHP_EOL;
    echo "Role: {$user->role->value}" . PHP_EOL;
    echo "Has Password: " . ($user->password ? 'Yes' : 'No') . PHP_EOL;
    echo "Password Verification (password): " . (Hash::check('password', $user->password) ? 'Success' : 'Fail') . PHP_EOL;
} else {
    echo "User not found" . PHP_EOL;
}
