<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    protected $signature = 'user:create {name} {email} {password}';

    protected $description = 'Create a new user';

    public function handle()
    {
        $name     = $this->argument('name');
        $email    = $this->argument('email');
        $password = $this->argument('password');

        User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Пользователь $name успешно создан");
    }
}
