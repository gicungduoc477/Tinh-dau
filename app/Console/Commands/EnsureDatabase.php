<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use Exception;

class EnsureDatabase extends Command
{
    protected $signature = 'app:ensure-db {--migrate : Run migrations after creating DB}';
    protected $description = 'Create the configured database if it does not exist (useful for local dev)';

    public function handle()
    {
        $this->info('Checking database configuration...');

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $db = config('database.connections.mysql.database');

        if (empty($db)) {
            $this->error('DB_DATABASE is empty in your configuration. Please set DB_DATABASE in .env');
            return 1;
        }

        try {
            // Connect without specifying database
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            $this->info("Connected to MySQL server at {$host}:{$port}");

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $this->info("Database `{$db}` ensured (created if it did not exist)");

            if ($this->option('migrate')) {
                $this->info('Running migrations...');
                $exit = $this->call('migrate', ['--force' => true]);
                if ($exit === 0) {
                    $this->info('Migrations completed');
                } else {
                    $this->error('Migrations failed; check the output above');
                    return $exit;
                }
            }

            return 0;
        } catch (Exception $e) {
            $this->error('Failed to create database: ' . $e->getMessage());
            return 1;
        }
    }
}
