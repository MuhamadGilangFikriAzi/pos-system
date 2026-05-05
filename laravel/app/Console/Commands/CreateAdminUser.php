<?php
namespace App\\Console\\Commands;
use Illuminate\\Console\\Command;
use App\\Models\\User;

class CreateAdminUser extends Command
{
    protected \\ = 'make:admin';
    protected \\ = 'Create default admin user';

    public function handle()
    {
        if (!User::where('email', 'admin@pos.com')->exists()) {
            \\ = User::create([
                'name' => 'Admin',
                'email' => 'admin@pos.com',
                'password' => bcrypt('admin123')
            ]);
            \\->info('Admin created: ' . \\->email);
        } else {
            \\->info('Admin already exists');
        }
    }
}
