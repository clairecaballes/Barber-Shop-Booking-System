<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Force the application into the testing environment. Some shells (e.g. Git Bash
     * on Windows) export APP_ENV=local, which leaks into the container via $_ENV and
     * disables Laravel's test-only middleware bypasses (e.g. CSRF verification).
     */
    public function createApplication(): Application
    {
        $_ENV['APP_ENV'] = 'testing';
        $_SERVER['APP_ENV'] = 'testing';
        putenv('APP_ENV=testing');

        $app = parent::createApplication();
        $app['env'] = 'testing';

        return $app;
    }
}
