<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class InstallOwner extends Command
{
    protected $signature = 'cashtrack:install-owner {email} {--name=Owner}';

    protected $description = 'Install the first Owner using a hidden password prompt; refuses to replace an existing Owner.';

    public function handle(InventoryService $inventory): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Run interactively; passwords must not appear in command arguments.');

            return self::FAILURE;
        }
        $password = $this->secret('Kata laluan baharu (minimum 12 aksara)');
        $confirmation = $this->secret('Ulang kata laluan');
        $values = Validator::make(['email' => strtolower($this->argument('email')), 'name' => $this->option('name'),
            'password' => $password, 'password_confirmation' => $confirmation],
            ['email' => ['required', 'email', 'unique:users,email'], 'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:12', 'max:200', 'confirmed']])->validate();
        $inventory->locked(function () use ($values, $inventory): void {
            $role = Role::firstOrCreate(['name' => 'Owner']);
            if (User::where('role_id', $role->id)->exists()) {
                throw new \RuntimeException('Owner already exists. Use password recovery; no account was overwritten.');
            }
            Role::firstOrCreate(['name' => 'Cashier']);
            $owner = User::create([...$values, 'role_id' => $role->id, 'is_active' => true]);
            $inventory->audit($owner->id, 'owner.dipasang', 'user', $owner->id, [], ['name' => $owner->name, 'email' => $owner->email, 'email_verified' => false]);
        });
        $this->info('Owner dicipta. Email ialah ID login; belum disahkan melalui email.');

        return self::SUCCESS;
    }
}
