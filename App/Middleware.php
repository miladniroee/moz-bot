<?php

namespace App;

use Exception;

class Middleware
{
    public function handleRequest(): void
    {
        self::loadEnv(APP_DIR . '/.env');


        if (!config('DEV')) {
            // Check if Sec is provided
            if (!isset($_GET['sec']) || urldecode($_GET['sec']) !== config('APP_KEY')):
                error_log("ip: {$_SERVER['REMOTE_ADDR']} | [sec:" . ($_GET['sec'] ?? 'null') . "] provided is wrong");
                exit();
            endif;
        }

        new \App\Application();
    }

    public function handleCommand():void
    {
        global $argv;
        new \App\Console($argv);
    }

    /**
     * @throws Exception
     */
    public static function loadEnv($path): void
    {
        if (!file_exists($path)) {
            throw new Exception(".env file not found: " . $path);
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {

            // skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            // split key and value
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // remove quotes if exist
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}