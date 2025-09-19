#!/bin/bash

# Navigate to the project directory
cd /Applications/XAMPP/xamppfiles/htdocs/IMS/app

# Run migrations with fresh option to reset the database
php artisan migrate:fresh

# Run the database seeder
php artisan db:seed

echo "\nSetup completed successfully!"