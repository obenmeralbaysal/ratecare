<?php

namespace App\Middleware;

/**
 * Base Middleware Class
 */
abstract class BaseMiddleware
{
    /**
     * Handle the request
     * Return false to block the request
     */
    abstract public function handle();
}
