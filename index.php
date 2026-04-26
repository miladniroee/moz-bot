<?php

require_once __DIR__ . '/App/boot.php';

new \App\Middleware()->handleRequest();