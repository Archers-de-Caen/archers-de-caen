<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__) . '/vendor/autoload.php';

/* @phpstan-ignore-next-line is set by symfony */
if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

if (false === (bool) $_SERVER['APP_DEBUG'] && null === ($_SERVER['TEST_TOKEN'] ?? null)) {
    /*
     * Ensure a fresh cache when debug mode is disabled. When using paratest, this
     * file is required once at the very beginning, and once per process. Checking that
     * TEST_TOKEN is not set ensures this is only run once at the beginning.
     */
    (new Filesystem())->remove(__DIR__ . '/../var/cache/test');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0o000);
}
