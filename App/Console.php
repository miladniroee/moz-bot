<?php

namespace App;

use JetBrains\PhpStorm\NoReturn;
use PDO;
use PDOException;

class Console
{
    const string ENV_DIR = APP_DIR . '/.env';
    const array COMMAND_LIST = [
        'init' => 'Create .env file and generate APP_KEY',
        'personalize' => 'Creates PersonalizeText class',
        'migrate' => 'To migrate sql files',
    ];

    #[NoReturn] public function __construct(public array $argv)
    {
        array_shift($argv);

        if (file_exists(self::ENV_DIR)) {
            \App\Middleware::loadEnv(self::ENV_DIR);
        }

        if (count($argv) > 0 && array_key_exists($argv[0], self::COMMAND_LIST)) {
            if (method_exists($this,$argv[0])) {
                $this->{$argv[0]}();
            }
        } else {
            echo "\n\t\t" . colorful('  Moz Bot 🍌  ', 'light_white', 'blue');
            echo "\n\n";
            foreach (self::COMMAND_LIST as $command => $description) {
                echo "- " . colorful($command, 'green') . "\t" . $description . "\n";
            }
        }
        exit(0);
    }


    function init(): void
    {
        if (!file_exists(self::ENV_DIR)) {
            copy(APP_DIR . '/.env.example', self::ENV_DIR);
            echo "Environment file created\n";
            \App\Middleware::loadEnv(self::ENV_DIR);
        }


        if (config('DEV') == 'false') {
            echo "\n" . colorful(' Warn ', 'white', 'yellow', 'bold') . " You are trying to generate APP_KEY on production. Are you sure?\n";
            echo "\nType {yes} or {no} " . colorful('[default: yes]', 'yellow') . ": ";
            $answer = trim(fgets(STDIN));
            if ($answer === 'no' || $answer === 'n') {
                echo "\n" . colorful(" Abort ", "white", "red", 'bold') . " Key generation aborted.\n\n";
                exit(0);
            }
        }

        $key = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(random_bytes(32)));

        if ($this->updateEnvFile($key)) {
            echo "\n" . colorful(' Success ', 'white', 'green', 'bold') . " " . " Your key generated. Use it in webhook.\n\n";
            echo colorful($key, 'white', style: 'mono') . "\n\n";
            exit(0);
        } else {
            echo colorful(" Error ", "white", "red", 'bold') . " Failed to write to .env file\n\n";
            exit(1);
        }


    }

    private function personalize(): void
    {
        $PERSONALIZE_FILE_DIR = '/App/Utils/PersonalizeText.php';
        if (file_exists(__DIR__ . $PERSONALIZE_FILE_DIR)) {
            echo "\n";
            echo colorful("File [$PERSONALIZE_FILE_DIR] Exists\n", 'red');
            echo "\n";
            exit(1);
        }
        copy(__DIR__ . "$PERSONALIZE_FILE_DIR.sample", __DIR__ . $PERSONALIZE_FILE_DIR);
        echo "\n";
        echo colorful("Created: [$PERSONALIZE_FILE_DIR]\n", 'green');
        echo "\n";
        exit(0);
    }

    private function migrate(): void
    {
        $conn = new PDO('mysql:host=localhost;dbname=' . config('DB_NAME'), config('DB_USER'), config('DB_PASS'));
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $result = $conn->query("SHOW TABLES LIKE 'migrations'");

            if ($result->rowCount() == 0) {

                $conn->exec("
            CREATE TABLE migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration_name VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
                echo colorful("Migrations table created.\n\n", 'blue');
            }
        } catch (PDOException $e) {
            echo colorful($e->getMessage(), 'red');
            exit(1);
        }

        $migrations = $conn->query('SELECT `migration_name` FROM `migrations` WHERE  1')->fetchAll();

        $MIGRATIONS_DIR = APP_DIR . '/App/Database';

        $files = scandir($MIGRATIONS_DIR);

        $migrationCount = 0;

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (!file_exists($MIGRATIONS_DIR . '/' . $file)) continue;
            if (in_array($file, array_column($migrations, 'migration_name'))) continue;


            try {
                echo colorful("Migrating:\t" . $file . "\n", 'light_yellow');

                $query = file_get_contents($MIGRATIONS_DIR . '/' . $file);
                $conn->prepare($query)->execute();

                echo colorful("Migrated:\t" . $file . "\n", 'light_green');

                $conn->prepare("INSERT INTO `migrations` (`migration_name`) VALUES (?)")->execute([$file]);

                $migrationCount++;
            } catch (PDOException $e) {
                echo colorful($e->getMessage() . "\n", 'light_red');
                exit(1);
            }
        }

        if ($migrationCount === 0) {
            echo "\n" . colorful(' Info ', 'white', 'blue', 'bold') . ' ' . "No migration found.\n\n";
        }
        exit(0);
    }


    private function updateEnvFile($value): false|int
    {
        $keyLine = "APP_KEY=$value";

        if (!file_exists(self::ENV_DIR)) {
            return file_put_contents(self::ENV_DIR, $keyLine . "\n");
        }

        $content = file_get_contents(self::ENV_DIR);

        if (preg_match("/^APP_KEY=.*$/m", $content)) {
            $content = preg_replace("/^APP_KEY=.*$/m", $keyLine, $content);
        } else {
            $content .= "\n" . $keyLine . "\n";
        }


        return file_put_contents(self::ENV_DIR, $content);
    }

}