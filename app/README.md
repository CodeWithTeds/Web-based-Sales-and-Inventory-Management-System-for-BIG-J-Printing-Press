# Inventory Management System (IMS)

A web-based Sales and Inventory Management System for BIG J Printing Press. This system helps streamline printing business operations by providing tools for inventory tracking, sales monitoring, and business reporting.

## Features

- Inventory Tracking: Monitor stock levels, set reorder points, and receive alerts
- Sales Monitoring: Track sales performance, customer orders, and payment status
- Automated Reports: Generate detailed reports on sales, inventory, and business performance
- User Management: Role-based access control for administrators and staff

## Requirements

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- XAMPP (or similar local development environment)

## Installation

1. Clone the repository to your local machine:
   ```
   git clone https://github.com/your-username/IMS.git
   ```

2. Navigate to the project directory:
   ```
   cd IMS/app
   ```

3. Install dependencies:
   ```
   composer install
   ```

4. Create a copy of the `.env.example` file and rename it to `.env`:
   ```
   cp .env.example .env
   ```

5. Generate an application key:
   ```
   php artisan key:generate
   ```

6. Configure your database settings in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ims
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. Run the setup command to migrate the database and create an admin user:
   ```
   php artisan setup:admin
   ```

8. Start the development server:
   ```
   php artisan serve
   ```

9. Access the application at `http://localhost:8000`

## Admin Credentials

After running the setup command, you can log in with the following credentials:

- Username: admin
- Password: admin

## Development

### Git Conventions

This project follows specific Git naming conventions. Please refer to the [Git Naming Convention](docs/git-naming-convention.md) document for details on branch naming, commit messages, and pull request formats.

## License

This project is licensed under the MIT License - see the LICENSE file for details.