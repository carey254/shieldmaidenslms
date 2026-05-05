<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add custom validation rule to prevent URLs
        \Validator::extend('not_url', function ($attribute, $value, $parameters, $validator) {
            return !filter_var($value, FILTER_VALIDATE_URL) && !preg_match('/^https?:\/\//', $value);
        });

        // Add custom validation rule for CAPTCHA (simplified version)
        \Validator::extend('captcha', function ($attribute, $value, $parameters, $validator) {
            // For now, we'll implement a simple token validation
            // In production, integrate with reCAPTCHA or similar service
            return !empty($value) && strlen($value) > 10;
        });
    }
}
