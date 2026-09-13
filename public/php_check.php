<?php

/**
 * public/php_check.php
 * Enterprise Server & Laravel Readiness Diagnostic Tool v3.0
 *
 * IMPORTANT: Delete this file and public/php_check/ after use in production.
 * Access: https://your-domain.com/php_check.php
 */

// ── Bootstrap ─────────────────────────────────────────────────────────────────
$startTime = microtime(true);

require_once __DIR__.'/php_check/config.php';
require_once __DIR__.'/php_check/helpers.php';

// ── Load check modules ────────────────────────────────────────────────────────
require_once __DIR__.'/php_check/checks/SystemCheck.php';
require_once __DIR__.'/php_check/checks/PhpCheck.php';
require_once __DIR__.'/php_check/checks/NetworkCheck.php';
require_once __DIR__.'/php_check/checks/DatabaseCheck.php';
require_once __DIR__.'/php_check/checks/RedisCheck.php';
require_once __DIR__.'/php_check/checks/StorageCheck.php';
require_once __DIR__.'/php_check/checks/EmailCheck.php';
require_once __DIR__.'/php_check/checks/LaravelCheck.php';
require_once __DIR__.'/php_check/checks/SecurityCheck.php';

// ── Load vendor autoload (needed for S3 SDK etc.) ─────────────────────────────
$autoloadLoaded = diagTryLoadAutoload();

// ── Run all checks ────────────────────────────────────────────────────────────
/** @var DiagResult[] $results */
$results = array_merge(
    (new SystemCheck)->run(),
    (new PhpCheck)->run(),
    (new NetworkCheck)->run(),
    (new DatabaseCheck)->run(),
    (new RedisCheck)->run(),
    (new StorageCheck($autoloadLoaded))->run(),
    (new EmailCheck)->run(),
    (new LaravelCheck)->run(),
    (new SecurityCheck)->run(),
);

// ── Aggregate summary ─────────────────────────────────────────────────────────
$summary = ['pass' => 0, 'fail' => 0, 'warn' => 0, 'info' => 0];
foreach ($results as $r) {
    if (isset($summary[$r->status])) {
        $summary[$r->status]++;
    }
}

$overall = 'pass';
if ($summary['fail'] > 0) {
    $overall = 'fail';
} elseif ($summary['warn'] > 0) {
    $overall = 'warn';
}

$runtime = round(microtime(true) - $startTime, 2);

// ── Render ────────────────────────────────────────────────────────────────────
require __DIR__.'/php_check/views/layout.php';
