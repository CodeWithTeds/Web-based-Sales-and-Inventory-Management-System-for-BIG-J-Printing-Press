<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the database and create admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database setup...');
        
        // Run migrations with fresh option
        $this->info('Refreshing database...');
        $this->call('migrate:fresh');
        
        // Run seeders
        $this->info('Seeding database...');
        $this->call('db:seed');
        
        $this->info('\nSetup completed successfully!');
        $this->info('Admin credentials:');
        $this->info('Username: admin');
        $this->info('Password: admin');
        
        return Command::SUCCESS;
    }
}