<?php

const APP_DIR = __DIR__;

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


new \App\Middleware(\App\Application::class);