<?php
namespace App\Controllers;
use App\Auth;
use App\Csrf;
class BaseController
{
    protected function requireAdmin(): void {
        Auth::guardAdmin();
    }
    protected function checkCsrf(?string $token): void {
        if (!\App\Csrf::check($token)) {
            throw new \RuntimeException('CSRF token không hợp lệ.');
        }
    }
}
