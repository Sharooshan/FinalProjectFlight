<?php
// routes.php

$request = $_SERVER['REQUEST_URI'];

switch ($request) {
    case '/intelliflight/public/register':
    case '/intelliflight/public/register.php':
        include __DIR__ . '/../public/register.php';
        break;

    case '/intelliflight/public/login':
    case '/intelliflight/public/login.php':
        include __DIR__ . '/../public/login.php';
        break;

    case '/intelliflight/public/dashboard':
    case '/intelliflight/public/dashboard.php':
        include __DIR__ . '/../public/dashboard.php';
        break;

    default:
        http_response_code(404);
        echo "Page not found";
        break;
}
