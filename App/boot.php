<?php

const APP_DIR = __DIR__ . '/..';

spl_autoload_register(function ($class) {
    require_once APP_DIR . '/' . str_replace('\\', '/', $class) . '.php';
});

function text_dir($filename): string
{
    return APP_DIR . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Texts' . DIRECTORY_SEPARATOR . $filename . '.php';
}

function config($key, $default = null)
{
    return getenv($key) ?: $default;
}


function colorful($text, $color = null, $bg = null, $style = null)
{
    $codes = [
        'black' => '0;30',
        'red' => '0;31',
        'green' => '0;32',
        'yellow' => '0;33',
        'blue' => '0;34',
        'magenta' => '0;35',
        'cyan' => '0;36',
        'white' => '0;37',
        'gray' => '1;30',
        'light_red' => '1;31',
        'light_green' => '1;32',
        'light_yellow' => '1;33',
        'light_blue' => '1;34',
        'light_magenta' => '1;35',
        'light_cyan' => '1;36',
        'light_white' => '1;37'
    ];

    $bg_codes = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'white' => '47'
    ];

    $style_codes = [
        'bold' => '1',
        'dim' => '2',
        'underline' => '4',
        'blink' => '5',
        'reverse' => '7',
        'hidden' => '8'
    ];

    $code_parts = [];

    if ($color && isset($codes[$color])) {
        $code_parts[] = $codes[$color];
    }

    if ($bg && isset($bg_codes[$bg])) {
        $code_parts[] = $bg_codes[$bg];
    }

    if ($style && isset($style_codes[$style])) {
        $code_parts[] = $style_codes[$style];
    }

    if (empty($code_parts)) {
        return $text;
    }

    return "\033[" . implode(';', $code_parts) . "m" . $text . "\033[0m";
}