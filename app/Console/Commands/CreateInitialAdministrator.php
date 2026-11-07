<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateInitialAdministrator extends Command
{
    protected $signature = 'siak:make-admin {email? : Login email}';
    protected $description = 'Create a local SIAK Intel administrator account interactively.';

    public function handle(): int
    {
        $email = strtolower(trim((string) ($this->argument('email') ?: $this->ask('Email login'))));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || User::query()->where('email', $email)->exists()) {
            $this->error('Email tidak valid atau sudah terdaftar.');
            return self::FAILURE;
        }
        $name = trim((string) $this->ask('Nama pengguna'));
        if ($name === '') {
            $this->error('Nama wajib diisi.');
            return self::FAILURE;
        }
        $password = (string) $this->secret('Kata sandi minimal 12 karakter');
        if (mb_strlen($password) < 12) {
            $this->error('Kata sandi minimal 12 karakter.');
            return self::FAILURE;
        }
        $role = Role::query()->where('slug', 'administrator-sistem')->first();
        if (! $role) {
            $this->error('Peran bawaan belum ada. Jalankan migrate dan db:seed terlebih dahulu.');
            return self::FAILURE;
        }
        $user = User::query()->forceCreate(['name' => $name, 'email' => $email, 'password' => $password, 'is_active' => true]);
        $user->roles()->sync([$role->getKey()]);
        $this->info('Akun administrator lokal berhasil dibuat.');
        return self::SUCCESS;
    }
}
