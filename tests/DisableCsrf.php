<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

trait DisableCsrf
{
    /**
     * Setup the test environment without CSRF.
     */
    protected function setUpDisableCsrf(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}
