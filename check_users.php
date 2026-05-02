<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::with('role')->get();
echo "Total Users: " . $users->count() . "\n";
echo str_pad("ID", 5) . " | " . str_pad("Name", 20) . " | " . str_pad("Email", 25) . " | " . str_pad("Role", 15) . "\n";
echo str_repeat("-", 70) . "\n";
foreach ($users as $user) {
    echo str_pad($user->id, 5) . " | " . str_pad(substr($user->name, 0, 20), 20) . " | " . str_pad(substr($user->email, 0, 25), 25) . " | " . str_pad($user->role->name ?? 'None', 15) . "\n";
}
