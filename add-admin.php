<?php

/**
 * Quick script — adds the admin@controller.com admin account to an EXISTING
 * database without wiping anything.
 *
 * Run from your project root:
 *     php add-admin.php
 *
 * Then you can log in with:  admin@controller.com / password
 */

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::updateOrCreate(
    ['email' => 'admin@controller.com'],
    [
        'first_name' => 'Admin',
        'last_name'  => 'Controller',
        'username'   => 'admin.controller',
        'password'   => bcrypt('password'),
        'role_id'    => 1,
        'student_id' => now()->format('y') . '-AD-0002',
        'user_code'  => now()->format('y') . '-AD-0002',
        'is_active'  => true,
    ]
);

echo "Admin ready: {$user->email}  (password: password)\n";
