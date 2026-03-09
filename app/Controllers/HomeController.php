<?php

declare(strict_types=1);

namespace App\Controllers;

class HomeController
{
    public function index(): void
    {
        view('home', [
            'appName' => env('APP_NAME', 'Codex Demo PHP App'),
        ]);
    }
}
