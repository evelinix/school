<?php

use Aws\Exception\AwsException;
use Aws\S3\S3Client;

/**
 * Enterprise-Grade Server & Laravel 13 Readiness Diagnostic Tool v2.0
 *
 * Place this file in your Laravel project's /public directory.
 * Access via: https://your-domain.com/check_php.php
 *
 * IMPORTANT: Delete this file after use in production.
 */

// =========================================================================
// CONFIGURATION
// =========================================================================
$envFile = __DIR__.'/../.env';
if (is_readable($envFile)) {
    $contents = @file_get_contents($envFile);
    if ($contents !== false) {
        foreach (explode("\n", $contents) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim(trim($value), "\"'");

                if (! array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                }
                if (! array_key_exists($key, $_SERVER)) {
                    $_SERVER[$key] = $value;
                }
                if (getenv($key) === false) {
                    putenv("{$key}={$value}");
                }
            }
        }
    }
}

$phpTimezone = getenv('APP_TIMEZONE') ?: getenv('TZ') ?: 'UTC';
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set($phpTimezone);
}

define('SHOW_SENSITIVE_INFO', filter_var(getenv('DIAG_SHOW_SENSITIVE') ?: 'true', FILTER_VALIDATE_BOOLEAN));
define('ENABLE_SHELL_TESTS', filter_var(getenv('DIAG_SHELL_TESTS') ?: 'true', FILTER_VALIDATE_BOOLEAN));
define('ENABLE_NETWORK_TESTS', filter_var(getenv('DIAG_NETWORK_TESTS') ?: 'true', FILTER_VALIDATE_BOOLEAN));

// Database
define('DB_DRIVER', getenv('DB_CONNECTION') ?: 'pgsql');
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: (DB_DRIVER === 'pgsql' ? '5432' : '3306'));
define('DB_DATABASE', getenv('DB_DATABASE') ?: '');
define('DB_USERNAME', getenv('DB_USERNAME') ?: '');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');

// Redis (with password support)
define('REDIS_HOST', getenv('REDIS_HOST') ?: ($_ENV['REDIS_HOST'] ?? '127.0.0.1'));
define('REDIS_PORT', getenv('REDIS_PORT') ?: ($_ENV['REDIS_PORT'] ?? '6379'));
define('REDIS_PASSWORD', getenv('REDIS_PASSWORD') ?: ($_ENV['REDIS_PASSWORD'] ?? null));
define('REDIS_USERNAME', getenv('REDIS_USERNAME') ?: ($_ENV['REDIS_USERNAME'] ?? null));
define('REDIS_DB', (int) (getenv('REDIS_DB') ?: ($_ENV['REDIS_DB'] ?? 0)));
define('REDIS_PREFIX', getenv('REDIS_PREFIX') ?: ($_ENV['REDIS_PREFIX'] ?? ''));
define('REDIS_TLS', filter_var(getenv('REDIS_TLS') ?: ($_ENV['REDIS_TLS'] ?? 'false'), FILTER_VALIDATE_BOOLEAN));

// SMTP
define('SMTP_HOST', getenv('MAIL_HOST') ?: '');
define('SMTP_PORT', getenv('MAIL_PORT') ?: '587');
define('SMTP_USERNAME', getenv('MAIL_USERNAME') ?: '');
define('SMTP_PASSWORD', getenv('MAIL_PASSWORD') ?: '');
define('SMTP_ENCRYPTION', strtolower(getenv('MAIL_ENCRYPTION') ?: 'tls'));
define('SMTP_FROM', getenv('MAIL_FROM_ADDRESS') ?: 'test@example.com');
define('SMTP_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'Diagnostic');
define('SMTP_TEST_TO', getenv('MAIL_TEST_TO') ?: '');

// S3 / Object Storage
define('S3_KEY', getenv('AWS_ACCESS_KEY_ID') ?: '');
define('S3_SECRET', getenv('AWS_SECRET_ACCESS_KEY') ?: '');
define('S3_REGION', getenv('AWS_DEFAULT_REGION') ?: 'us-east-1');
define('S3_BUCKET', getenv('AWS_BUCKET') ?: '');
define('S3_ENDPOINT', getenv('AWS_ENDPOINT') ?: '');
define('S3_URL', getenv('AWS_URL') ?: '');
define('S3_USE_PATH_STYLE', filter_var(getenv('AWS_USE_PATH_STYLE_ENDPOINT') ?: 'false', FILTER_VALIDATE_BOOLEAN));

// Test endpoints for network check
define('NETWORK_TEST_ENDPOINTS', ['1.1.1.1:443', '8.8.8.8:53', 'api.github.com:443']);

// =========================================================================
// DIAGNOSTIC ENGINE
// =========================================================================
class ServerCheck
{
    private array $results = [];

    private array $summary = ['pass' => 0, 'fail' => 0, 'warn' => 0, 'info' => 0];

    private bool $autoloadLoaded = false;

    private ?S3Client $s3 = null;

    private float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    public function run(): void
    {
        $this->tryLoadAutoload();

        // System Layer
        $this->checkSystemInfo();
        $this->checkDiskSpace();
        $this->checkSystemTime();
        $this->checkTools();

        // PHP Layer
        $this->checkPhpVersion();
        $this->checkPhpExtensions();
        $this->checkPhpIniSettings();
        $this->checkOpcache();
        $this->checkPhpInfo();

        // Network Layer
        $this->checkDns();
        $this->checkNetworkConnectivity();
        $this->checkSsl();
        $this->checkWebServer();
        $this->checkSecurityHeaders();

        // Data Layer
        $this->checkDatabase();
        $this->checkRedis();
        $this->checkS3();

        // Communication Layer
        $this->checkEmail();

        // Application Layer
        $this->checkFileSystem();
        $this->checkLaravelEnv();
        $this->checkLaravelSpecific();
        $this->checkLaravelLogs();
        $this->checkSecurity();

        $this->addResult('Diagnostic Runtime', 'info',
            sprintf('Completed in %.2f seconds', microtime(true) - $this->startTime),
            'Total checks executed: '.count($this->results));
    }

    // ---------------------------------------------------------------------
    // SYSTEM LAYER
    // ---------------------------------------------------------------------

    private function checkSystemInfo(): void
    {
        $os = PHP_OS_FAMILY;
        $osVersion = php_uname('r');
        $arch = php_uname('m');
        $hostname = gethostname() ?: 'Unknown';

        $this->addResult('Operating System', 'info',
            "{$os} ({$osVersion}) - {$arch}",
            'Hostname: '.$hostname);

        // CPU
        if (function_exists('shell_exec') && ENABLE_SHELL_TESTS) {
            $cpuCount = (int) @shell_exec('nproc 2>/dev/null') ?: 1;
            $this->addResult('CPU Cores', $cpuCount >= 2 ? 'pass' : 'warn',
                "{$cpuCount} core(s) detected",
                $cpuCount >= 2 ? 'Sufficient for production.' : 'Consider more cores for production.');
        }

        // Memory
        if (is_readable('/proc/meminfo')) {
            $mem = @file_get_contents('/proc/meminfo');
            if (preg_match('/MemTotal:\s+(\d+)\s+kB/', $mem, $m)) {
                $totalMB = round($m[1] / 1024);
                $this->addResult('System Memory', $totalMB >= 2048 ? 'pass' : 'warn',
                    "{$totalMB} MB total RAM",
                    $totalMB >= 2048 ? 'OK' : 'Minimum 2 GB recommended.');
            }
        }

        // Load Average
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            if ($load) {
                $la = sprintf('%.2f / %.2f / %.2f', $load[0], $load[1], $load[2]);
                $status = $load[0] < 5 ? 'pass' : ($load[0] < 10 ? 'warn' : 'fail');
                $this->addResult('Load Average', $status, $la, '1 / 5 / 15 minute averages.');
            }
        }

        // Uptime
        if (is_readable('/proc/uptime')) {
            $uptime = (int) explode(' ', @file_get_contents('/proc/uptime'))[0];
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $this->addResult('System Uptime', 'info', "{$days}d {$hours}h", '');
        }
    }

    private function checkDiskSpace(): void
    {
        $paths = [
            __DIR__.'/..' => 'Project Root',
            sys_get_temp_dir() => 'Temp Directory',
        ];

        foreach ($paths as $path => $label) {
            if (! is_dir($path)) {
                continue;
            }
            $free = @disk_free_space($path);
            $total = @disk_total_space($path);
            if ($free === false || $total === false) {
                continue;
            }

            $freeGB = round($free / 1073741824, 2);
            $totalGB = round($total / 1073741824, 2);
            $usedPct = round((($total - $free) / $total) * 100, 1);

            $status = $usedPct < 80 ? 'pass' : ($usedPct < 90 ? 'warn' : 'fail');
            $this->addResult("Disk Space ({$label})", $status,
                "{$freeGB} GB free of {$totalGB} GB ({$usedPct}% used)",
                $status === 'pass' ? 'OK' : 'Low disk space - clean up.');
        }
    }

    private function checkSystemTime(): void
    {
        $phpTz = date_default_timezone_get();
        $phpTime = date('Y-m-d H:i:s');
        $systemTime = date('Y-m-d H:i:s', time());

        $this->addResult('System Time', 'info', $phpTime.' ('.$phpTz.')', '');

        // NTP sync check
        if (is_readable('/etc/timezone')) {
            $sysTz = trim(@file_get_contents('/etc/timezone'));
            if ($sysTz) {
                $status = ($sysTz === $phpTz) ? 'pass' : 'warn';
                $this->addResult('Timezone Consistency', $status,
                    "PHP: {$phpTz} | System: {$sysTz}",
                    $status === 'pass' ? 'Consistent.' : 'PHP and system timezone differ.');
            }
        }
    }

    private function checkTools(): void
    {
        if (! ENABLE_SHELL_TESTS || ! function_exists('shell_exec')) {
            $this->addResult('CLI Tools', 'warn', 'Skipped (shell_exec disabled)', '');

            return;
        }

        $tools = [
            'composer' => 'composer --version 2>/dev/null',
            'git' => 'git --version 2>/dev/null',
            'node' => 'node --version 2>/dev/null',
            'npm' => 'npm --version 2>/dev/null',
            'nginx' => 'nginx -v 2>&1',
            'php-fpm' => 'php-fpm -v 2>&1',
            'psql' => 'psql --version 2>/dev/null',
            'redis-cli' => 'redis-cli --version 2>/dev/null',
            'supervisor' => 'supervisorctl --version 2>/dev/null',
            'curl' => 'curl --version 2>/dev/null | head -1',
            'openssl' => 'openssl version 2>/dev/null',
            'certbot' => 'certbot --version 2>/dev/null',
        ];

        foreach ($tools as $name => $cmd) {
            $output = @shell_exec($cmd);
            $available = ! empty(trim($output ?? ''));
            $this->addResult("Tool: {$name}",
                $available ? 'pass' : 'info',
                $available ? trim(substr($output, 0, 100)) : 'Not available',
                '');
        }
    }

    // ---------------------------------------------------------------------
    // PHP LAYER
    // ---------------------------------------------------------------------

    private function checkPhpVersion(): void
    {
        $required = '8.3.0';
        $current = PHP_VERSION;
        $pass = version_compare($current, $required, '>=');

        $this->addResult('PHP Version', $pass ? 'pass' : 'fail',
            "Required: >= {$required} | Installed: {$current}",
            $pass ? 'Sufficient for Laravel 13.' : 'Laravel 13 requires PHP 8.3+.');
    }

    private function checkPhpExtensions(): void
    {
        $required = [
            'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash',
            'mbstring', 'openssl', 'pcre', 'pdo', 'session', 'tokenizer', 'xml',
        ];

        $recommended = [
            'pdo_pgsql', 'pdo_mysql', 'redis', 'gd', 'imagick', 'zip',
            'intl', 'bcmath', 'sodium', 'exif', 'opcache', 'pcntl',
            'posix', 'simplexml', 'xmlreader', 'xmlwriter', 'soap',
            'ftp', 'ldap', 'gmp', 'imagick', 'ffi', 'apcu',
            'memcached', 'mongodb', 'amqp', 'ssh2',
        ];

        $loaded = array_map('strtolower', get_loaded_extensions());

        $missingReq = array_diff($required, $loaded);
        $missingRec = array_diff($recommended, $loaded);

        $this->addResult('PHP Extensions (Required)',
            empty($missingReq) ? 'pass' : 'fail',
            empty($missingReq) ? 'All '.count($required).' required extensions loaded.' : 'Missing: '.implode(', ', $missingReq),
            empty($missingReq) ? 'Laravel 13 core requirements met.' : 'Install these extensions.');

        $this->addResult('PHP Extensions (Recommended)',
            empty($missingRec) ? 'pass' : 'warn',
            empty($missingRec) ? 'All recommended extensions loaded.' : count($missingRec).' missing: '.implode(', ', $missingRec),
            empty($missingRec) ? 'Full feature set available.' : 'Optional but recommended.');

        // Grouped view
        $groups = [
            'Database' => ['pdo', 'pdo_pgsql', 'pdo_mysql', 'pgsql', 'mysqli', 'mongodb'],
            'Cache' => ['redis', 'memcached', 'apcu', 'opcache'],
            'Image' => ['gd', 'imagick', 'exif'],
            'Crypto' => ['openssl', 'sodium', 'hash', 'gmp', 'bcmath'],
            'XML' => ['dom', 'xml', 'simplexml', 'xmlreader', 'xmlwriter'],
            'Queue' => ['pcntl', 'posix', 'redis', 'amqp'],
        ];

        foreach ($groups as $group => $exts) {
            $present = array_intersect($exts, $loaded);
            $this->addResult("Extension Group: {$group}", 'info',
                count($present).'/'.count($exts).' loaded',
                'Available: '.(empty($present) ? 'none' : implode(', ', $present)));
        }

        $this->addResult('Total Loaded Extensions', 'info',
            count($loaded).' extensions',
            'All: '.implode(', ', array_slice($loaded, 0, 40)).(count($loaded) > 40 ? '...' : ''));
    }

    private function checkPhpIniSettings(): void
    {
        $settings = [
            'memory_limit' => ['256M', 'bytes_min'],
            'upload_max_filesize' => ['20M',  'bytes_min'],
            'post_max_size' => ['20M',  'bytes_min'],
            'max_execution_time' => ['60',   'int_min'],
            'max_input_time' => ['60',   'int_min'],
            'max_input_vars' => ['1000', 'int_min'],
            'date.timezone' => ['UTC',  'exact'],
            'opcache.enable' => ['1',    'exact'],
            'opcache.memory_consumption' => ['128', 'int_min'],
            'opcache.max_accelerated_files' => ['10000', 'int_min'],
            'realpath_cache_size' => ['4M',   'bytes_min'],
            'realpath_cache_ttl' => ['600',  'int_min'],
            'expose_php' => ['0',    'exact'],
            'display_errors' => ['0',    'exact'],
            'log_errors' => ['1',    'exact'],
            'allow_url_fopen' => ['1',    'exact'],
            'session.gc_maxlifetime' => ['1440', 'int_min'],
            'session.cookie_httponly' => ['1',    'exact'],
            'session.cookie_secure' => ['1',    'exact'],
        ];

        foreach ($settings as $key => $cfg) {
            $current = ini_get($key);
            $pass = $this->compareIni($current, $cfg[0], $cfg[1]);
            $this->addResult("PHP INI: {$key}",
                $pass ? 'pass' : 'warn',
                "Current: {$current} | Recommended: {$cfg[0]}",
                $pass ? 'OK' : 'Adjust in php.ini for production.');
        }
    }

    private function checkOpcache(): void
    {
        if (! function_exists('opcache_get_status')) {
            $this->addResult('OPcache', 'warn', 'Not available', 'Enable opcache for production.');

            return;
        }

        $status = @opcache_get_status(false);
        if (! $status) {
            $this->addResult('OPcache', 'warn', 'Disabled or not running', '');

            return;
        }

        $enabled = $status['opcache_enabled'] ?? false;
        $this->addResult('OPcache Enabled', $enabled ? 'pass' : 'warn',
            $enabled ? 'Active' : 'Inactive', '');

        if ($enabled) {
            $used = $status['memory_usage']['used_memory'] ?? 0;
            $free = $status['memory_usage']['free_memory'] ?? 0;
            $total = $used + $free;
            $usedMB = round($used / 1048576, 1);
            $totalMB = round($total / 1048576, 1);

            $this->addResult('OPcache Memory', 'info',
                "{$usedMB} MB used of {$totalMB} MB",
                $total > 0 ? 'Utilization: '.round(($used / $total) * 100, 1).'%' : '');

            $hitRate = $status['opcache_statistics']['opcache_hit_rate'] ?? 0;
            $this->addResult('OPcache Hit Rate',
                $hitRate > 95 ? 'pass' : ($hitRate > 80 ? 'warn' : 'info'),
                round($hitRate, 2).'%',
                $hitRate > 95 ? 'Excellent.' : 'Consider tuning opcache.');

            $this->addResult('Cached Scripts', 'info',
                ($status['opcache_statistics']['num_cached_scripts'] ?? 0).' scripts',
                'Max keys: '.ini_get('opcache.max_accelerated_files'));
        }
    }

    private function checkPhpInfo(): void
    {
        $this->addResult('PHP SAPI', 'info', php_sapi_name(),
            php_sapi_name() === 'fpm-fcgi' ? 'Recommended for production.' : 'Consider php-fpm.');

        $this->addResult('PHP Binary', 'info', PHP_BINARY, '');
        $this->addResult('Loaded php.ini', 'info',
            php_ini_loaded_file() ?: 'None (using defaults)',
            'Scan dir: '.(php_ini_scanned_files() ?: 'None'));
    }

    // ---------------------------------------------------------------------
    // NETWORK LAYER
    // ---------------------------------------------------------------------

    private function checkDns(): void
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $ip = gethostbyname($host);
        $this->addResult('DNS Resolution (self)', $ip !== $host ? 'pass' : 'warn',
            "{$host} → {$ip}",
            $ip !== $host ? 'Resolved.' : 'Could not resolve.');

        $checks = ['google.com', 'github.com', 'packagist.org'];
        foreach ($checks as $domain) {
            $resolved = gethostbyname($domain);
            $ok = $resolved !== $domain && filter_var($resolved, FILTER_VALIDATE_IP);
            $this->addResult("DNS: {$domain}", $ok ? 'pass' : 'fail',
                $ok ? $resolved : 'Failed to resolve', '');
        }
    }

    private function checkNetworkConnectivity(): void
    {
        if (! ENABLE_NETWORK_TESTS) {
            $this->addResult('Network Connectivity', 'warn', 'Skipped', '');

            return;
        }

        // Outbound internet
        foreach (NETWORK_TEST_ENDPOINTS as $endpoint) {
            [$host, $port] = explode(':', $endpoint);
            $sock = @fsockopen($host, (int) $port, $errno, $errstr, 3);
            if ($sock) {
                fclose($sock);
                $this->addResult("Outbound: {$endpoint}", 'pass', 'Reachable', '');
            } else {
                $this->addResult("Outbound: {$endpoint}", 'fail', 'Unreachable', "{$errstr} ({$errno})");
            }
        }

        // HTTPS test
        $ctx = stream_context_create(['http' => ['timeout' => 5, 'method' => 'HEAD']]);
        $resp = @get_headers('https://api.github.com', true, $ctx);
        $this->addResult('Outbound HTTPS', ! empty($resp) ? 'pass' : 'fail',
            ! empty($resp) ? 'TLS connection successful' : 'Failed to establish HTTPS',
            '');
    }

    private function checkSsl(): void
    {
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $domain = preg_replace('/:\d+$/', '', $domain);

        $isHttps = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

        if (! $isHttps && $domain !== 'localhost') {
            $this->addResult('SSL / HTTPS', 'warn', 'Current request is not HTTPS',
                'In production, always serve over HTTPS.');
        }

        $ctx = stream_context_create(['ssl' => [
            'capture_peer_cert' => true,
            'capture_peer_cert_chain' => true,
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]]);

        $client = @stream_socket_client("ssl://{$domain}:443", $errno, $errstr, 5,
            STREAM_CLIENT_CONNECT, $ctx);

        if (! $client) {
            $this->addResult('SSL Certificate', 'warn',
                "Cannot connect to {$domain}:443",
                $errstr ?: 'Server may not be listening on 443.');

            return;
        }

        $params = stream_context_get_params($client);
        $meta = stream_get_meta_data($client);
        $cert = $params['options']['ssl']['peer_certificate'] ?? null;
        $chain = $params['options']['ssl']['peer_certificate_chain'] ?? [];

        if ($cert) {
            $info = openssl_x509_parse($cert);
            $validFrom = date('Y-m-d', $info['validFrom_time_t'] ?? 0);
            $validTo = date('Y-m-d', $info['validTo_time_t'] ?? 0);
            $daysLeft = round((($info['validTo_time_t'] ?? 0) - time()) / 86400);
            $issuer = $info['issuer']['O'] ?? $info['issuer']['CN'] ?? 'Unknown';
            $subject = $info['subject']['CN'] ?? 'Unknown';
            $sigAlg = $info['signatureTypeLN'] ?? 'Unknown';

            $status = $daysLeft > 30 ? 'pass' : ($daysLeft > 7 ? 'warn' : 'fail');

            $this->addResult('SSL Certificate', $status,
                "Subject: {$subject} | Issuer: {$issuer}",
                "Valid: {$validFrom} → {$validTo} ({$daysLeft} days left) | Sig: {$sigAlg}");

            // SAN check
            $sans = $info['extensions']['subjectAltName'] ?? '';
            if ($sans) {
                $this->addResult('SSL SAN (Alt Names)', 'info',
                    substr($sans, 0, 200),
                    'Domains covered by this certificate.');
            }

            // Chain
            $this->addResult('SSL Certificate Chain', count($chain) >= 2 ? 'pass' : 'warn',
                count($chain).' certificate(s) in chain',
                count($chain) >= 2 ? 'Chain appears complete.' : 'May be missing intermediates.');
        }

        // TLS version
        $crypto = $meta['crypto'] ?? [];
        $protocol = $crypto['protocol'] ?? 'Unknown';
        $cipher = $crypto['cipher_name'] ?? 'Unknown';
        $cipherBits = $crypto['cipher_bits'] ?? 0;

        $tlsPass = in_array($protocol, ['TLSv1.2', 'TLSv1.3']);
        $this->addResult('TLS Protocol', $tlsPass ? 'pass' : 'warn',
            "{$protocol} ({$cipher}, {$cipherBits} bits)",
            $tlsPass ? 'Modern TLS in use.' : 'Upgrade to TLS 1.2 or 1.3.');

        fclose($client);

        // HSTS check
        $headers = @get_headers("https://{$domain}", true);
        $hsts = $headers['Strict-Transport-Security'] ?? null;
        $this->addResult('HSTS Header', $hsts ? 'pass' : 'warn',
            $hsts ?: 'Not set',
            $hsts ? '' : 'Add Strict-Transport-Security header.');
    }

    private function checkWebServer(): void
    {
        $software = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
        $this->addResult('Web Server', 'info', $software, 'Detected.');

        if (stripos($software, 'nginx') !== false) {
            $this->addResult('Nginx Detected', 'pass',
                'Nginx is serving requests.', '');
            $this->addResult('Nginx Document Root Hint', 'info',
                'Ensure root points to /public',
                'Config usually: root /path/to/laravel/public;');
            $this->addResult('Nginx PHP-FPM Hint', 'info',
                'fastcgi_pass should point to php-fpm socket',
                'Example: fastcgi_pass unix:/run/php-fpm/www.sock;');
        } elseif (stripos($software, 'apache') !== false) {
            $this->addResult('Apache Detected', 'pass', 'Apache is serving requests.', '');
            if (function_exists('apache_get_modules')) {
                $mods = apache_get_modules();
                $rewrite = in_array('mod_rewrite', $mods);
                $this->addResult('Apache mod_rewrite', $rewrite ? 'pass' : 'fail',
                    $rewrite ? 'Enabled' : 'Disabled',
                    $rewrite ? 'OK' : 'Enable for Laravel routing.');
            }
        }

        // HTTP/2 check
        if (ENABLE_NETWORK_TESTS) {
            $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $ctx = stream_context_create(['http' => ['timeout' => 3, 'method' => 'HEAD']]);
            $headers = @get_headers("https://{$domain}", true, $ctx);
            $this->addResult('HTTP/2 Support', 'info',
                isset($headers[0]) ? $headers[0] : 'Unknown',
                'Enable HTTP/2 in your web server for better performance.');
        }
    }

    private function checkSecurityHeaders(): void
    {
        return;
        if (! ENABLE_NETWORK_TESTS) {
            return;
        }

        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $headers = @get_headers("{$scheme}://{$domain}", true);

        if (! $headers) {
            return;
        }

        $securityHeaders = [
            'X-Frame-Options' => 'Prevents clickjacking',
            'X-Content-Type-Options' => 'Prevents MIME sniffing',
            'X-XSS-Protection' => 'XSS protection',
            'Referrer-Policy' => 'Controls referrer info',
            'Content-Security-Policy' => 'CSP for XSS mitigation',
            'Permissions-Policy' => 'Controls browser features',
        ];

        foreach ($securityHeaders as $header => $purpose) {
            $present = isset($headers[$header]);
            $this->addResult("Security Header: {$header}",
                $present ? 'pass' : 'warn',
                $present ? $headers[$header] : 'Not set',
                $purpose);
        }
    }

    // ---------------------------------------------------------------------
    // DATABASE
    // ---------------------------------------------------------------------

    private function checkDatabase(): void
    {
        if (empty(DB_DATABASE) || empty(DB_USERNAME)) {
            $this->addResult('Database', 'warn',
                'Skipped (credentials not configured)',
                'Set DB_* environment variables.');

            return;
        }

        $this->addResult('DB Driver', 'info', DB_DRIVER,
            'Testing connection to '.DB_HOST.':'.DB_PORT);

        try {
            if (DB_DRIVER === 'pgsql') {
                $dsn = 'pgsql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_DATABASE;
            } elseif (DB_DRIVER === 'mysql' || DB_DRIVER === 'mariadb') {
                $dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_DATABASE.';charset=utf8mb4';
            } else {
                $this->addResult('Database', 'warn', 'Unsupported driver: '.DB_DRIVER, '');

                return;
            }

            $start = microtime(true);
            $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $latency = round((microtime(true) - $start) * 1000, 2);

            $this->addResult('Database Connection', 'pass',
                'Connected to '.DB_HOST.':'.DB_PORT." ({$latency}ms)",
                'Database: '.DB_DATABASE);

            // Version
            if (DB_DRIVER === 'pgsql') {
                $version = $pdo->query('SHOW server_version')->fetchColumn();
                $verMajor = (int) explode('.', $version)[0];
                $this->addResult('PostgreSQL Version',
                    $verMajor >= 18 ? 'pass' : ($verMajor >= 14 ? 'warn' : 'fail'),
                    "Server version: {$version}",
                    $verMajor >= 18 ? 'PostgreSQL 18+ detected.' : 'PostgreSQL 18 recommended for latest features.');
            } elseif (DB_DRIVER === 'mysql' || DB_DRIVER === 'mariadb') {
                $version = $pdo->query('SELECT VERSION()')->fetchColumn();
                $this->addResult('MySQL/MariaDB Version', 'info',
                    "Server version: {$version}", '');
            }

            // Connection details
            try {
                $db = $pdo->query('SELECT current_database()')->fetchColumn();
                $user = $pdo->query('SELECT current_user')->fetchColumn();
                $this->addResult('Database Session', 'info',
                    "DB: {$db} | User: {$user}", '');
            } catch (Exception $e) {
            }

            // Test write (transaction + rollback)
            try {
                $pdo->beginTransaction();
                $pdo->exec('CREATE TEMP TABLE diag_test (id INT)');
                $pdo->exec('INSERT INTO diag_test VALUES (1)');
                $count = $pdo->query('SELECT COUNT(*) FROM diag_test')->fetchColumn();
                $pdo->rollBack();
                $this->addResult('Database Write Test', $count == 1 ? 'pass' : 'fail',
                    'Temp table write/read/rollback successful.',
                    'Permissions verified.');
            } catch (Exception $e) {
                $this->addResult('Database Write Test', 'fail',
                    'Write test failed.', $e->getMessage());
            }

            // Table count
            try {
                if (DB_DRIVER === 'pgsql') {
                    $count = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public'")->fetchColumn();
                } else {
                    $count = $pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE()')->fetchColumn();
                }
                $this->addResult('Database Tables', $count > 0 ? 'pass' : 'warn',
                    "{$count} tables in schema", $count > 0 ? 'OK' : 'Run migrations.');
            } catch (Exception $e) {
            }

            // Laravel migrations
            try {
                $migrations = $pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn();
                $batch = $pdo->query('SELECT MAX(batch) FROM migrations')->fetchColumn();
                $this->addResult('Laravel Migrations', 'pass',
                    "{$migrations} migrations in {$batch} batch(es)",
                    'php artisan migrate:status for details.');
            } catch (Exception $e) {
                $this->addResult('Laravel Migrations', 'warn',
                    'migrations table not found', 'Run php artisan migrate.');
            }

            // Laravel tables
            $laravelTables = ['users', 'jobs', 'failed_jobs', 'sessions', 'cache', 'cache_locks', 'password_reset_tokens'];
            foreach ($laravelTables as $table) {
                try {
                    if (DB_DRIVER === 'pgsql') {
                        $exists = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name='{$table}' AND table_schema='public'")->fetchColumn();
                    } else {
                        $exists = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name='{$table}' AND table_schema=DATABASE()")->fetchColumn();
                    }
                    if ($exists) {
                        $rows = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
                        $this->addResult("Laravel Table: {$table}", 'pass',
                            "Exists ({$rows} rows)", '');
                    }
                } catch (Exception $e) {
                }
            }

        } catch (PDOException $e) {
            $this->addResult('Database Connection', 'fail',
                'Connection failed', $e->getMessage());
        }
    }

    // ---------------------------------------------------------------------
    // REDIS (WITH PASSWORD)
    // ---------------------------------------------------------------------

    private function checkRedis(): void
    {
        // =====================================================================
        // 1. Extension Check
        // =====================================================================
        if (! extension_loaded('redis')) {
            $this->addResult('Redis Extension', 'fail',
                'phpredis not loaded',
                'Install php-redis: pecl install redis && docker-php-ext-enable redis');

            return;
        }

        $phpredisVersion = phpversion('redis') ?: 'unknown';
        $this->addResult('Redis Extension', 'pass',
            "phpredis {$phpredisVersion} loaded", '');

        // =====================================================================
        // 2. Connection
        // =====================================================================
        $redis = new Redis;
        $transport = REDIS_TLS ? 'tls' : 'tcp';
        $host = REDIS_TLS ? ('tls://'.REDIS_HOST) : REDIS_HOST;

        try {
            $connectStart = microtime(true);
            $connected = $redis->connect($host, (int) REDIS_PORT, 3.0);
            $connectMs = round((microtime(true) - $connectStart) * 1000, 2);

            if (! $connected) {
                $this->addResult('Redis Connection', 'fail',
                    'Could not connect',
                    "Host: {$host}:{REDIS_PORT} | Transport: {$transport}");

                return;
            }

            // Set read timeout agar tidak hang saat command lambat
            $redis->setOption(Redis::OPT_READ_TIMEOUT, 3);

            // Set prefix otomatis agar tidak perlu manual di setiap key
            if (! empty(REDIS_PREFIX)) {
                $redis->setOption(Redis::OPT_PREFIX, REDIS_PREFIX);
            }

            $this->addResult('Redis Connection', 'pass',
                "Connected in {$connectMs}ms",
                "Host: {$host}:".REDIS_PORT." | Transport: {$transport}");
        } catch (RedisException $e) {
            $this->addResult('Redis Connection', 'fail',
                'RedisException on connect', $e->getMessage());

            return;
        } catch (Throwable $e) {
            $this->addResult('Redis Connection', 'fail',
                'Unexpected error on connect', $e->getMessage());

            return;
        }

        // =====================================================================
        // 3. Authentication
        // =====================================================================
        $isAuthenticated = false;

        if (! empty(REDIS_PASSWORD)) {
            try {
                if (! empty(REDIS_USERNAME)) {
                    $auth = $redis->auth([REDIS_USERNAME, REDIS_PASSWORD]);
                    $authUser = REDIS_USERNAME;
                } else {
                    $auth = $redis->auth(REDIS_PASSWORD);
                    $authUser = 'default';
                }

                if ($auth) {
                    $isAuthenticated = true;
                    $this->addResult('Redis Authentication', 'pass',
                        'Authentication successful',
                        "User: {$authUser}");

                    // Verifikasi user via ACL WHOAMI (Redis 6+)
                    try {
                        $whoami = $redis->rawCommand('ACL', 'WHOAMI');
                        $this->addResult('Redis ACL User', 'info',
                            "Active user: {$whoami}",
                            $whoami === $authUser ? 'Matches configured user.' : '⚠️ User berbeda dari yang dikonfigurasi.');
                    } catch (Throwable $e) {
                        // ACL WHOAMI tidak tersedia di Redis < 6
                    }
                } else {
                    $this->addResult('Redis Authentication', 'fail',
                        'auth() returned false',
                        'Password mungkin salah atau user tidak ada.');
                    $redis->close();

                    return;
                }
            } catch (RedisException $e) {
                $msg = $e->getMessage();
                $hint = 'Periksa REDIS_PASSWORD / REDIS_USERNAME.';
                if (stripos($msg, 'WRONGPASS') !== false) {
                    $hint = 'Password salah.';
                } elseif (stripos($msg, 'NOAUTH') !== false) {
                    $hint = 'Server butuh auth tapi kredensial tidak dikirim.';
                }
                $this->addResult('Redis Authentication', 'fail',
                    'Authentication failed', "{$msg} — {$hint}");
                $redis->close();

                return;
            }
        } else {
            // Tidak ada password diset — deteksi apakah server butuh
            try {
                $redis->ping();
                $this->addResult('Redis Authentication', 'warn',
                    'Tidak ada password dikonfigurasi',
                    'Redis tanpa password = sangat berbahaya di production. Set REDIS_PASSWORD.');
            } catch (RedisException $e) {
                $msg = $e->getMessage();
                $needAuth = stripos($msg, 'NOAUTH') !== false;
                $this->addResult('Redis Authentication', $needAuth ? 'fail' : 'warn',
                    $needAuth ? 'Server butuh password, tapi tidak dikonfigurasi' : 'Ping gagal',
                    "{$msg} — Set REDIS_PASSWORD environment variable.");
                $redis->close();

                return;
            }
        }

        // =====================================================================
        // 4. PING & Latency
        // =====================================================================
        try {
            $pong = $redis->ping();
            $pingOk = ($pong === true || $pong === '+PONG' || $pong === 'PONG' || $pong === '1');
            $this->addResult('Redis PING', $pingOk ? 'pass' : 'warn',
                'Response: '.var_export($pong, true), '');

            // Latency test
            $samples = [];
            for ($i = 0; $i < 10; $i++) {
                $t = microtime(true);
                $redis->ping();
                $samples[] = (microtime(true) - $t) * 1000;
            }
            sort($samples);
            $avg = round(array_sum($samples) / count($samples), 3);
            $p95 = round($samples[(int) (count($samples) * 0.95)] ?? end($samples), 3);
            $status = $avg < 1 ? 'pass' : ($avg < 5 ? 'warn' : 'fail');
            $this->addResult('Redis Latency',
                $status,
                "Avg: {$avg}ms | P95: {$p95}ms (10 samples)",
                $status === 'pass' ? 'Sangat cepat.' : 'Latency tinggi — periksa network/load.');
        } catch (RedisException $e) {
            $this->addResult('Redis PING', 'fail', 'PING failed', $e->getMessage());
        }

        // =====================================================================
        // 5. DB Selection
        // =====================================================================
        try {
            $selected = $redis->select(REDIS_DB);
            $this->addResult('Redis DB Selection',
                $selected ? 'pass' : 'fail',
                $selected ? 'Selected DB '.REDIS_DB : 'Gagal select DB '.REDIS_DB,
                $selected ? '' : 'DB index mungkin di luar range atau dinonaktifkan di cluster mode.');
        } catch (RedisException $e) {
            $this->addResult('Redis DB Selection', 'fail',
                'Select DB '.REDIS_DB.' gagal', $e->getMessage());
        }

        // =====================================================================
        // 6. INFO (server, memory, stats, keyspace, replication)
        // =====================================================================
        try {
            $info = $redis->info();
            $version = $info['redis_version'] ?? 'Unknown';
            $mode = $info['redis_mode'] ?? 'standalone';
            $uptimeD = $info['uptime_in_days'] ?? 0;
            $role = $info['role'] ?? 'master';
            $os = $info['os'] ?? 'N/A';
            $arch = $info['arch_bits'] ?? '?';
            $pid = $info['process_id'] ?? 'N/A';

            $verMajor = (int) explode('.', $version)[0];
            $verStatus = $verMajor >= 8 ? 'pass' : ($verMajor >= 6 ? 'warn' : 'fail');
            $this->addResult('Redis Version', $verStatus,
                "Version: {$version}",
                $verMajor >= 8 ? 'Redis 8+ terdeteksi.' : 'Redis 8 direkomendasikan untuk production.');

            $this->addResult('Redis Mode', 'info',
                "Mode: {$mode} | Role: {$role}",
                "Uptime: {$uptimeD} hari | PID: {$pid}");

            $this->addResult('Redis OS', 'info',
                "{$os} ({$arch}-bit)", '');

            // Memory
            $usedMem = $info['used_memory_human'] ?? 'N/A';
            $peakMem = $info['used_memory_peak_human'] ?? 'N/A';
            $fragRatio = isset($info['mem_fragmentation_ratio'])
                ? round((float) $info['mem_fragmentation_ratio'], 2) : null;

            $this->addResult('Redis Memory', 'info',
                "Used: {$usedMem} | Peak: {$peakMem}",
                $fragRatio !== null
                    ? "Fragmentation ratio: {$fragRatio}".($fragRatio > 1.5 ? ' ⚠️ tinggi (restart/activedefrag)' : '')
                    : '');

            // Stats
            $evicted = (int) ($info['evicted_keys'] ?? 0);
            $expired = (int) ($info['expired_keys'] ?? 0);
            $hits = (int) ($info['keyspace_hits'] ?? 0);
            $misses = (int) ($info['keyspace_misses'] ?? 0);
            $hitRate = ($hits + $misses) > 0 ? round($hits / ($hits + $misses) * 100, 2) : 0;

            $this->addResult('Redis Cache Stats', 'info',
                "Hits: {$hits} | Misses: {$misses} | Hit rate: {$hitRate}%",
                "Evicted: {$evicted} | Expired: {$expired}"
                .($evicted > 0 ? ' ⚠️ ada eviction — pertimbangkan tambah maxmemory.' : ''));

            // Redis 8 multithreading (io-threads)
            if (isset($info['io_threads_active'])) {
                $ioActive = (int) $info['io_threads_active'];
                $this->addResult('Redis 8 I/O Threads', $ioActive > 0 ? 'pass' : 'info',
                    "Active: {$ioActive}",
                    $ioActive > 0 ? 'Multithreading aktif — performa lebih baik.' : 'Single-threaded.');
            }

            // Replication
            if ($mode === 'standalone') {
                if ($role === 'slave' || $role === 'replica') {
                    $masterHost = $info['master_host'] ?? '?';
                    $masterPort = $info['master_port'] ?? '?';
                    $linkStatus = $info['master_link_status'] ?? '?';
                    $this->addResult('Redis Replication', $linkStatus === 'up' ? 'pass' : 'fail',
                        "Replica of {$masterHost}:{$masterPort} ({$linkStatus})", '');
                } else {
                    $slaves = (int) ($info['connected_slaves'] ?? 0);
                    $this->addResult('Redis Replication', 'info',
                        "Master, {$slaves} slave(s) terhubung", '');
                }
            }

            // Cluster
            if ($mode === 'cluster') {
                try {
                    $clusterInfo = $redis->rawCommand('CLUSTER', 'INFO');
                    $this->addResult('Redis Cluster', 'pass',
                        'Cluster mode aktif',
                        substr((string) $clusterInfo, 0, 200));
                } catch (Throwable $e) {
                    $this->addResult('Redis Cluster', 'warn',
                        'Cluster mode terdeteksi tapi CLUSTER INFO gagal', $e->getMessage());
                }
            }

        } catch (RedisException $e) {
            $this->addResult('Redis INFO', 'warn', 'INFO gagal', $e->getMessage());
        }

        // =====================================================================
        // 7. CONFIG: Max Memory, Policy, Persistence
        // =====================================================================
        try {
            $maxMem = $redis->config('GET', 'maxmemory');
            $policy = $redis->config('GET', 'maxmemory-policy');
            $maxMemVal = (int) ($maxMem['maxmemory'] ?? 0);
            $maxMemHuman = $maxMemVal > 0 ? round($maxMemVal / 1048576, 1).' MB' : 'unlimited';
            $policyVal = $policy['maxmemory-policy'] ?? 'N/A';

            $memStatus = $maxMemVal > 0 ? 'pass' : 'warn';
            $this->addResult('Redis Max Memory', $memStatus,
                "Max: {$maxMemHuman} | Policy: {$policyVal}",
                $maxMemVal > 0 ? 'Memory limit terkonfigurasi.' : '⚠️ Tanpa maxmemory, Redis bisa OOM-kill host.');

            $save = $redis->config('GET', 'save');
            $aof = $redis->config('GET', 'appendonly');
            $saveVal = $save['save'] ?? '';
            $aofVal = ($aof['appendonly'] ?? 'no') === 'yes';

            $persistStatus = (! empty($saveVal) || $aofVal) ? 'pass' : 'warn';
            $this->addResult('Redis Persistence Config', $persistStatus,
                'RDB save: '.($saveVal ?: 'disabled').' | AOF: '.($aofVal ? 'enabled' : 'disabled'),
                $persistStatus === 'pass' ? 'Persistence aktif.' : '⚠️ Tidak ada persistence — data hilang saat restart.');
        } catch (RedisException $e) {
            // CONFIG mungkin di-rename/di-disable (security hardening)
            $this->addResult('Redis Config', 'info',
                'CONFIG command tidak tersedia',
                'Kemungkinan di-rename untuk hardening (bagus!).');
        }

        // =====================================================================
        // 8. Data Type Operations (String, Hash, List, Set, ZSet, TTL)
        // =====================================================================
        $cleanup = [];
        try {
            // --- String ---
            $testKey = 'diag:str:'.uniqid();
            $cleanup[] = $testKey;
            $redis->set($testKey, 'hello-world', 30);
            $retrieved = $redis->get($testKey);
            $this->addResult('Redis String Set/Get',
                $retrieved === 'hello-world' ? 'pass' : 'fail',
                $retrieved === 'hello-world' ? 'OK' : 'Value mismatch',
                $retrieved === 'hello-world' ? '' : "Expected 'hello-world', got: ".var_export($retrieved, true));

            $ttl = $redis->ttl($testKey);
            $this->addResult('Redis TTL', $ttl > 0 ? 'pass' : 'warn',
                "TTL: {$ttl}s", $ttl > 0 ? 'Expiry berfungsi.' : 'TTL tidak diset atau tidak jalan.');

            $exists = $redis->exists($testKey);
            $this->addResult('Redis EXISTS', $exists ? 'pass' : 'fail',
                "exists() = {$exists}", '');

            // --- Hash ---
            $hashKey = 'diag:hash:'.uniqid();
            $cleanup[] = $hashKey;
            $redis->hMSet($hashKey, ['a' => '1', 'b' => '2']);
            $hashLen = $redis->hLen($hashKey);
            $this->addResult('Redis Hash', $hashLen === 2 ? 'pass' : 'fail',
                "Hash length: {$hashLen}", $hashLen === 2 ? 'hMSet/hLen OK.' : 'Expected 2, got '.$hashLen);

            // --- List ---
            $listKey = 'diag:list:'.uniqid();
            $cleanup[] = $listKey;
            $redis->rPush($listKey, 'x', 'y', 'z');
            $listLen = $redis->lLen($listKey);
            $this->addResult('Redis List', $listLen === 3 ? 'pass' : 'fail',
                "List length: {$listLen}", '');

            // --- Set ---
            $setKey = 'diag:set:'.uniqid();
            $cleanup[] = $setKey;
            $redis->sAdd($setKey, 'm', 'n', 'm');
            $setCard = $redis->sCard($setKey);
            $this->addResult('Redis Set', $setCard === 2 ? 'pass' : 'fail',
                "Set cardinality: {$setCard} (unique)",
                $setCard === 2 ? 'sAdd deduplicate OK.' : '');

            // --- Sorted Set ---
            $zKey = 'diag:zset:'.uniqid();
            $cleanup[] = $zKey;
            $redis->zAdd($zKey, 1.0, 'one');
            $redis->zAdd($zKey, 2.0, 'two');
            $zCard = $redis->zCard($zKey);
            $this->addResult('Redis Sorted Set', $zCard === 2 ? 'pass' : 'fail',
                "ZSet cardinality: {$zCard}", '');

            // --- MEMORY USAGE ---
            try {
                $mem = $redis->rawCommand('MEMORY', 'USAGE', $testKey);
                $this->addResult('Redis MEMORY USAGE', 'info',
                    'Key size: '.($mem !== false ? "{$mem} bytes" : 'N/A'), '');
            } catch (Throwable $e) {
            }

        } catch (RedisException $e) {
            $this->addResult('Redis Data Operations', 'fail',
                'Operasi data gagal', $e->getMessage());
        }

        // =====================================================================
        // 9. Pipeline
        // =====================================================================
        try {
            $p1 = 'diag:pipe:1:'.uniqid();
            $p2 = 'diag:pipe:2:'.uniqid();
            $cleanup[] = $p1;
            $cleanup[] = $p2;

            $results = $redis->multi(Redis::PIPELINE)
                ->set($p1, 'a', 30)
                ->set($p2, 'b', 30)
                ->get($p1)
                ->get($p2)
                ->exec();

            $pipelineOk = is_array($results) && count($results) === 4
                && $results[2] === 'a' && $results[3] === 'b';

            $this->addResult('Redis Pipeline',
                $pipelineOk ? 'pass' : 'fail',
                $pipelineOk ? 'Pipeline 4 commands OK' : 'Pipeline hasil tidak sesuai',
                $pipelineOk ? '' : 'Results: '.json_encode($results));
        } catch (RedisException $e) {
            $this->addResult('Redis Pipeline', 'fail', 'Pipeline gagal', $e->getMessage());
        }

        // =====================================================================
        // 10. PERSIST & UNLINK
        // =====================================================================
        try {
            $persistKey = 'diag:persist:'.uniqid();
            $cleanup[] = $persistKey;
            $redis->setex($persistKey, 60, 'x');
            $ttlBefore = $redis->ttl($persistKey);
            $redis->persist($persistKey);
            $ttlAfter = $redis->ttl($persistKey);
            $ok = $ttlBefore > 0 && $ttlAfter === -1;
            $this->addResult('Redis PERSIST', $ok ? 'pass' : 'warn',
                "TTL before: {$ttlBefore}s, after: {$ttlAfter}",
                $ok ? 'PERSIST menghapus TTL.' : 'Perilaku PERSIST tidak sesuai.');
        } catch (RedisException $e) {
        }

        // =====================================================================
        // 11. Cleanup — SELALU dijalankan
        // =====================================================================
        try {
            if (! empty($cleanup)) {
                $deleted = $redis->del($cleanup);
                $expected = count($cleanup);
                $this->addResult('Redis Cleanup Test Keys',
                    $deleted === $expected ? 'pass' : 'warn',
                    "Deleted {$deleted}/{$expected} test keys",
                    $deleted === $expected ? 'Semua key uji dibersihkan.' : '⚠️ Beberapa key mungkin sudah expired.');
            }
        } catch (Throwable $e) {
            $this->addResult('Redis Cleanup', 'warn',
                'Cleanup gagal', $e->getMessage());
        }

        // =====================================================================
        // 12. Slowlog
        // =====================================================================
        try {
            $slowlog = $redis->rawCommand('SLOWLOG', 'GET', '10');
            $count = is_array($slowlog) ? count($slowlog) : 0;
            $this->addResult('Redis Slowlog',
                $count > 0 ? 'warn' : 'pass',
                "{$count} slow query tercatat",
                $count > 0 ? 'Periksa SLOWLOG GET untuk detail.' : 'Tidak ada slow query.');
        } catch (Throwable $e) {
            // SLOWLOG mungkin di-disable via rename-command
        }

        // =====================================================================
        // 13. DBSIZE
        // =====================================================================
        try {
            $dbsize = $redis->dbSize();
            $this->addResult('Redis DBSIZE', 'info',
                'DB '.REDIS_DB.": {$dbsize} keys", '');
        } catch (Throwable $e) {
        }

        // =====================================================================
        // Close
        // =====================================================================
        try {
            $redis->close();
        } catch (Throwable $e) {
            // ignore
        }
    }

    // ---------------------------------------------------------------------
    // S3 STORAGE
    // ---------------------------------------------------------------------

    private function checkS3(): void
    {
        // Determine mode: AWS SDK via composer, or fallback to raw HTTP
        if (empty(S3_BUCKET)) {
            $this->addResult('S3 Storage', 'warn',
                'Skipped (AWS_BUCKET not set)',
                'Set AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_BUCKET.');

            return;
        }

        if (empty(S3_KEY) || empty(S3_SECRET)) {
            $this->addResult('S3 Storage', 'warn',
                'Skipped (AWS credentials not set)',
                'Set AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY.');

            return;
        }

        $this->addResult('S3 Config', 'info',
            'Bucket: '.S3_BUCKET.' | Region: '.S3_REGION,
            S3_ENDPOINT ? 'Endpoint: '.S3_ENDPOINT : 'Using AWS default endpoint.');

        // Try AWS SDK
        if ($this->autoloadLoaded && class_exists('\Aws\S3\S3Client')) {
            $this->checkS3WithSdk();
        } else {
            $this->addResult('S3 SDK', 'warn',
                'AWS SDK not available',
                'Run: composer require aws/aws-sdk-php. Falling back to raw HTTP test.');

            // Fallback: simple signed request test
            $this->checkS3Raw();
        }
    }

    private function checkS3WithSdk(): void
    {
        try {
            $config = [
                'version' => 'latest',
                'region' => S3_REGION,
                'credentials' => ['key' => S3_KEY, 'secret' => S3_SECRET],
                'http' => ['timeout' => 10, 'connect_timeout' => 5],
            ];
            if (! empty(S3_ENDPOINT)) {
                $config['endpoint'] = S3_ENDPOINT;
                $config['use_path_style_endpoint'] = S3_USE_PATH_STYLE;
            }

            $this->s3 = new S3Client($config);

            // Head bucket
            $this->s3->headBucket(['Bucket' => S3_BUCKET]);
            $this->addResult('S3 Bucket Access', 'pass',
                "Bucket '".S3_BUCKET."' accessible",
                'Region: '.S3_REGION);

            // Bucket location
            try {
                $loc = $this->s3->getBucketLocation(['Bucket' => S3_BUCKET]);
                $this->addResult('S3 Bucket Location', 'info',
                    'Region: '.($loc['LocationConstraint'] ?: 'us-east-1'),
                    '');
            } catch (Exception $e) {
            }

            // Versioning
            try {
                $ver = $this->s3->getBucketVersioning(['Bucket' => S3_BUCKET]);
                $this->addResult('S3 Versioning', 'info',
                    'Status: '.($ver['Status'] ?? 'Disabled'),
                    ($ver['Status'] ?? '') === 'Enabled' ? 'Enabled.' : 'Not enabled.');
            } catch (Exception $e) {
            }

            // Encryption
            try {
                $enc = $this->s3->getBucketEncryption(['Bucket' => S3_BUCKET]);
                $algo = $enc['ServerSideEncryptionConfiguration']['Rules'][0]['ApplyServerSideEncryptionByDefault']['SSEAlgorithm'] ?? 'Unknown';
                $this->addResult('S3 Default Encryption', 'pass',
                    'Algorithm: '.$algo, '');
            } catch (Exception $e) {
                $this->addResult('S3 Default Encryption', 'warn',
                    'Not configured or inaccessible',
                    'Consider enabling SSE for compliance.');
            }

            // Put test object
            $testKey = 'diagnostics/test-'.uniqid().'.txt';
            $testContent = 'S3 diagnostic at '.date('c')."\n".str_repeat('X', 500);
            $start = microtime(true);

            $this->s3->putObject([
                'Bucket' => S3_BUCKET,
                'Key' => $testKey,
                'Body' => $testContent,
                'ContentType' => 'text/plain',
                'ServerSideEncryption' => 'AES256',
            ]);
            $putTime = round((microtime(true) - $start) * 1000, 2);
            $this->addResult('S3 Put Object', 'pass',
                "Uploaded test object ({$putTime}ms)",
                'Key: '.$testKey);

            // Get test object
            $start = microtime(true);
            $result = $this->s3->getObject(['Bucket' => S3_BUCKET, 'Key' => $testKey]);
            $body = (string) $result['Body'];
            $getTime = round((microtime(true) - $start) * 1000, 2);

            if ($body === $testContent) {
                $this->addResult('S3 Get Object', 'pass',
                    "Retrieved and verified ({$getTime}ms)",
                    'Content integrity OK.');
            } else {
                $this->addResult('S3 Get Object', 'fail',
                    'Content mismatch',
                    'Expected '.strlen($testContent).' bytes, got '.strlen($body));
            }

            // Head object
            $head = $this->s3->headObject(['Bucket' => S3_BUCKET, 'Key' => $testKey]);
            $this->addResult('S3 Head Object', 'pass',
                'Size: '.($head['ContentLength'] ?? '?').' bytes',
                'ETag: '.($head['ETag'] ?? '?'));

            // Copy object
            $copyKey = $testKey.'.copy';
            try {
                $this->s3->copyObject([
                    'Bucket' => S3_BUCKET,
                    'Key' => $copyKey,
                    'CopySource' => S3_BUCKET.'/'.$testKey,
                ]);
                $this->addResult('S3 Copy Object', 'pass',
                    'Copy operation successful.', '');
                $this->s3->deleteObject(['Bucket' => S3_BUCKET, 'Key' => $copyKey]);
            } catch (Exception $e) {
                $this->addResult('S3 Copy Object', 'warn',
                    'Copy failed', $e->getMessage());
            }

            // List objects
            $list = $this->s3->listObjectsV2(['Bucket' => S3_BUCKET, 'MaxKeys' => 10]);
            $this->addResult('S3 List Objects', 'pass',
                'Listed '.($list['KeyCount'] ?? 0).' object(s)',
                '');

            // Multipart upload test (small)
            try {
                $mpKey = 'diagnostics/multipart-'.uniqid();
                $mp = $this->s3->createMultipartUpload(['Bucket' => S3_BUCKET, 'Key' => $mpKey]);
                $uploadId = $mp['UploadId'];
                $part = $this->s3->uploadPart([
                    'Bucket' => S3_BUCKET,
                    'Key' => $mpKey,
                    'UploadId' => $uploadId,
                    'PartNumber' => 1,
                    'Body' => str_repeat('M', 5 * 1024 * 1024), // 5MB min
                ]);
                $this->s3->completeMultipartUpload([
                    'Bucket' => S3_BUCKET,
                    'Key' => $mpKey,
                    'UploadId' => $uploadId,
                    'MultipartUpload' => [
                        'Parts' => [['PartNumber' => 1, 'ETag' => $part['ETag']]],
                    ],
                ]);
                $this->addResult('S3 Multipart Upload', 'pass',
                    'Multipart upload works (5MB test).', '');
                $this->s3->deleteObject(['Bucket' => S3_BUCKET, 'Key' => $mpKey]);
            } catch (Exception $e) {
                $this->addResult('S3 Multipart Upload', 'warn',
                    'Multipart failed', $e->getMessage());
            }

            // Presigned URL
            try {
                $cmd = $this->s3->getCommand('GetObject', ['Bucket' => S3_BUCKET, 'Key' => $testKey]);
                $presigned = (string) $this->s3->createPresignedRequest($cmd, '+5 minutes')->getUri();
                $this->addResult('S3 Presigned URL', 'pass',
                    'Generated URL (valid 5 min)',
                    SHOW_SENSITIVE_INFO ? substr($presigned, 0, 150).'...' : 'Hidden');
            } catch (Exception $e) {
                $this->addResult('S3 Presigned URL', 'warn',
                    'Failed', $e->getMessage());
            }

            // Delete cleanup
            $this->s3->deleteObject(['Bucket' => S3_BUCKET, 'Key' => $testKey]);
            $this->addResult('S3 Delete Object', 'pass',
                'Cleanup successful.', '');

            // Verify deletion
            try {
                $this->s3->headObject(['Bucket' => S3_BUCKET, 'Key' => $testKey]);
                $this->addResult('S3 Verify Delete', 'fail',
                    'Object still exists after delete.', '');
            } catch (AwsException $e) {
                if ($e->getAwsErrorCode() === 'NotFound' || $e->getStatusCode() === 404) {
                    $this->addResult('S3 Verify Delete', 'pass',
                        'Object confirmed deleted.', '');
                }
            }

            // Object lock / legal hold check
            try {
                $this->s3->getObjectLockConfiguration(['Bucket' => S3_BUCKET]);
                $this->addResult('S3 Object Lock', 'info',
                    'Enabled on bucket.', '');
            } catch (Exception $e) {
                // Not enabled, fine
            }

        } catch (AwsException $e) {
            $this->addResult('S3 Storage', 'fail',
                'AWS error',
                ($e->getAwsErrorCode() ?: '').': '.($e->getAwsErrorMessage() ?: $e->getMessage()));
        } catch (Exception $e) {
            $this->addResult('S3 Storage', 'fail',
                'Unexpected error', $e->getMessage());
        }
    }

    private function checkS3Raw(): void
    {
        // Simple unauthenticated reachability check against the configured endpoint.
        if (empty(S3_ENDPOINT)) {
            $host = S3_BUCKET.'.s3.'.S3_REGION.'.amazonaws.com';
            $port = 443;
            $scheme = 'ssl';
            $label = "{$host}:{$port}";
        } else {
            $parsed = parse_url(S3_ENDPOINT);
            $host = $parsed['host'] ?? S3_ENDPOINT;
            $port = (int) ($parsed['port'] ?? (($parsed['scheme'] ?? 'https') === 'http' ? 80 : 443));
            $scheme = (($parsed['scheme'] ?? 'https') === 'http') ? 'tcp' : 'ssl';
            $label = "{$host}:{$port}";
        }

        $socket = @fsockopen("{$scheme}://{$host}", $port, $errno, $errstr, 5);

        if ($socket) {
            $this->addResult('S3 Endpoint Reachability', 'pass',
                "Reachable: {$label}", '');
            fclose($socket);
        } else {
            $this->addResult('S3 Endpoint Reachability', 'fail',
                "Cannot reach {$label}", $errstr ?: 'Connection failed');
        }
    }

    // ---------------------------------------------------------------------
    // EMAIL (SMTP)
    // ---------------------------------------------------------------------

    private function checkEmail(): void
    {
        if (empty(SMTP_HOST)) {
            $this->addResult('Email (SMTP)', 'warn',
                'Skipped (MAIL_HOST not set)', 'Configure SMTP settings.');

            return;
        }

        $host = SMTP_HOST;
        $port = (int) SMTP_PORT;
        $encryption = SMTP_ENCRYPTION;
        $timeout = 10;
        $transport = ($encryption === 'ssl') ? 'ssl://' : '';

        $connection = @stream_socket_client("{$transport}{$host}:{$port}", $errno, $errstr, $timeout);

        if (! $connection) {
            $this->addResult('Email (SMTP Connection)', 'fail',
                "Cannot connect to {$host}:{$port}", "{$errstr} ({$errno})");

            return;
        }

        stream_set_timeout($connection, $timeout);
        $greeting = fgets($connection, 512);

        if (! str_starts_with($greeting, '220')) {
            fclose($connection);
            $this->addResult('Email (SMTP Connection)', 'warn',
                'Unexpected greeting', trim($greeting));

            return;
        }

        $this->addResult('Email (SMTP Connection)', 'pass',
            "Connected to {$host}:{$port} ({$encryption})", '');

        // EHLO
        $ehloHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
        fwrite($connection, "EHLO {$ehloHost}\r\n");
        $ehloResp = $this->readSmtpMultiline($connection);

        // STARTTLS
        if ($encryption === 'tls' && stripos($ehloResp, 'STARTTLS') !== false) {
            fwrite($connection, "STARTTLS\r\n");
            $resp = fgets($connection, 512);
            if (str_starts_with($resp, '220')) {
                $ok = @stream_socket_enable_crypto($connection, true,
                    STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
                if ($ok) {
                    $this->addResult('Email (STARTTLS)', 'pass',
                        'TLS enabled successfully.', '');
                    fwrite($connection, "EHLO {$ehloHost}\r\n");
                    $ehloResp = $this->readSmtpMultiline($connection);
                } else {
                    $this->addResult('Email (STARTTLS)', 'fail',
                        'Failed to enable TLS.', '');
                }
            }
        }

        // Auth
        if (! empty(SMTP_USERNAME) && ! empty(SMTP_PASSWORD)) {
            $authOk = false;

            // Try AUTH LOGIN
            fwrite($connection, "AUTH LOGIN\r\n");
            $r1 = fgets($connection, 512);
            if (str_starts_with($r1, '334')) {
                fwrite($connection, base64_encode(SMTP_USERNAME)."\r\n");
                $r2 = fgets($connection, 512);
                if (str_starts_with($r2, '334')) {
                    fwrite($connection, base64_encode(SMTP_PASSWORD)."\r\n");
                    $r3 = fgets($connection, 512);
                    $authOk = str_starts_with($r3, '235');
                    $this->addResult('Email (SMTP Auth)',
                        $authOk ? 'pass' : 'fail',
                        $authOk ? 'Authentication successful' : 'Authentication failed',
                        'User: '.SMTP_USERNAME.' | Response: '.trim($r3));
                }
            }
        } else {
            $this->addResult('Email (SMTP Auth)', 'warn',
                'No credentials configured', 'Set MAIL_USERNAME and MAIL_PASSWORD.');
        }

        // Optional send test
        if (! empty(SMTP_TEST_TO)) {
            $this->sendTestEmail($connection);
        }

        fwrite($connection, "QUIT\r\n");
        fclose($connection);
    }

    private function sendTestEmail($connection): void
    {
        fwrite($connection, 'MAIL FROM:<'.SMTP_FROM.">\r\n");
        $fromResp = fgets($connection, 512);
        if (! str_starts_with($fromResp, '250')) {
            $this->addResult('Email (Test Send)', 'fail',
                'MAIL FROM rejected', trim($fromResp));

            return;
        }

        fwrite($connection, 'RCPT TO:<'.SMTP_TEST_TO.">\r\n");
        $toResp = fgets($connection, 512);
        if (! str_starts_with($toResp, '250') && ! str_starts_with($toResp, '251')) {
            $this->addResult('Email (Test Send)', 'fail',
                'RCPT TO rejected', trim($toResp));

            return;
        }

        fwrite($connection, "DATA\r\n");
        $dataResp = fgets($connection, 512);
        if (! str_starts_with($dataResp, '354')) {
            $this->addResult('Email (Test Send)', 'fail',
                'DATA rejected', trim($dataResp));

            return;
        }

        $headers = 'From: '.SMTP_FROM_NAME.' <'.SMTP_FROM.">\r\n";
        $headers .= 'To: <'.SMTP_TEST_TO.">\r\n";
        $headers .= 'Subject: [Diagnostic] Test Email from '.($_SERVER['HTTP_HOST'] ?? 'Server')."\r\n";
        $headers .= 'Date: '.date('r')."\r\n";
        $headers .= 'Message-ID: <'.uniqid().'@'.($_SERVER['HTTP_HOST'] ?? 'localhost').">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: Diagnostic Tool v2.0\r\n\r\n";
        $body = "This is an automated diagnostic test email.\n\n";
        $body .= 'Server: '.($_SERVER['HTTP_HOST'] ?? 'Unknown')."\n";
        $body .= 'Timestamp: '.date('c')."\n";
        $body .= 'PHP: '.PHP_VERSION."\n\n";
        $body .= 'If you received this, SMTP delivery is functional.';

        fwrite($connection, $headers.$body."\r\n.\r\n");
        $sendResp = fgets($connection, 512);

        if (str_starts_with($sendResp, '250')) {
            $this->addResult('Email (Test Send)', 'pass',
                'Test email sent to '.SMTP_TEST_TO,
                'Check the inbox.');
        } else {
            $this->addResult('Email (Test Send)', 'fail',
                'Send failed', trim($sendResp));
        }
    }

    private function readSmtpMultiline($connection): string
    {
        $response = '';
        while ($line = fgets($connection, 512)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
            if (strlen($line) < 4) {
                break;
            }
        }

        return $response;
    }

    // ---------------------------------------------------------------------
    // FILE SYSTEM
    // ---------------------------------------------------------------------

    private function checkFileSystem(): void
    {
        $paths = [
            __DIR__.'/../storage' => ['writable', 'critical'],
            __DIR__.'/../storage/app' => ['writable', 'critical'],
            __DIR__.'/../storage/app/public' => ['writable', 'optional'],
            __DIR__.'/../storage/framework' => ['writable', 'critical'],
            __DIR__.'/../storage/framework/cache' => ['writable', 'critical'],
            __DIR__.'/../storage/framework/sessions' => ['writable', 'critical'],
            __DIR__.'/../storage/framework/views' => ['writable', 'critical'],
            __DIR__.'/../storage/logs' => ['writable', 'critical'],
            __DIR__.'/../bootstrap/cache' => ['writable', 'critical'],
            __DIR__.'/../.env' => ['exists', 'critical'],
            __DIR__.'/../vendor/autoload.php' => ['exists', 'critical'],
            __DIR__.'/../composer.json' => ['exists', 'optional'],
            __DIR__.'/../artisan' => ['exists', 'critical'],
        ];

        foreach ($paths as $path => $cfg) {
            [$mode, $severity] = $cfg;
            $exists = file_exists($path);
            $relative = str_replace(__DIR__.'/../', '', $path);

            if ($mode === 'writable') {
                $pass = $exists && is_writable($path);
                $status = $pass ? 'pass' : ($severity === 'critical' ? 'fail' : 'warn');
                $detail = $pass ? 'Writable' : ($exists ? 'NOT writable' : 'Missing');
            } else {
                $pass = $exists;
                $status = $pass ? 'pass' : ($severity === 'critical' ? 'fail' : 'warn');
                $detail = $pass ? 'Exists' : 'Missing';
            }

            $this->addResult($relative, $status, $detail,
                $pass ? 'OK' : 'Critical for Laravel operation.');

            // Ownership check
            if ($exists && SHOW_SENSITIVE_INFO) {
                $owner = @fileowner($path);
                $perms = @substr(sprintf('%o', fileperms($path)), -4);
                if ($owner !== false) {
                    $ownerName = function_exists('posix_getpwuid')
                        ? (posix_getpwuid($owner)['name'] ?? $owner)
                        : $owner;
                    // info only
                }
            }
        }
    }

    // ---------------------------------------------------------------------
    // LARAVEL CONFIGURATION
    // ---------------------------------------------------------------------

    private function checkLaravelEnv(): void
    {
        $envFile = __DIR__.'/../.env';
        if (! is_readable($envFile)) {
            $this->addResult('Laravel .env', 'warn',
                '.env not readable', '');

            return;
        }

        $contents = @file_get_contents($envFile);
        $envVars = [];
        foreach (explode("\n", $contents) as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                $envVars[trim($k)] = trim(trim($v), '"\'');
            }
        }

        // Critical keys
        $critical = ['APP_KEY', 'APP_ENV', 'APP_DEBUG', 'APP_URL'];
        foreach ($critical as $key) {
            $present = ! empty($envVars[$key]);
            if ($key === 'APP_DEBUG') {
                $isDebug = strtolower($envVars[$key] ?? '') === 'true';
                $this->addResult(".env: {$key}", $isDebug ? 'warn' : 'pass',
                    $isDebug ? 'true (DEBUG ENABLED)' : 'false',
                    $isDebug ? 'Disable APP_DEBUG in production!' : 'OK');
            } elseif ($key === 'APP_KEY') {
                $this->addResult(".env: {$key}",
                    $present && strlen($envVars[$key]) >= 20 ? 'pass' : 'fail',
                    $present ? 'Set ('.strlen($envVars[$key]).' chars)' : 'MISSING',
                    'Run php artisan key:generate if missing.');
            } elseif ($key === 'APP_URL') {
                $url = $envVars[$key] ?? '';
                $isHttps = str_starts_with($url, 'https://');
                $this->addResult(".env: {$key}",
                    $isHttps ? 'pass' : 'warn',
                    $url ?: 'Not set',
                    $isHttps ? 'HTTPS in production.' : 'Use HTTPS in production.');
            } else {
                $this->addResult(".env: {$key}", $present ? 'pass' : 'warn',
                    $envVars[$key] ?? 'Not set', '');
            }
        }

        // Other important keys
        $important = ['APP_ENV', 'LOG_CHANNEL', 'CACHE_STORE', 'SESSION_DRIVER',
            'QUEUE_CONNECTION', 'FILESYSTEM_DISK', 'BROADCAST_CONNECTION',
            'MAIL_MAILER', 'DB_CONNECTION'];

        foreach ($important as $key) {
            if (isset($envVars[$key])) {
                $this->addResult(".env: {$key}", 'info',
                    $envVars[$key], '');
            }
        }

        // APP_ENV check
        $appEnv = strtolower($envVars['APP_ENV'] ?? '');
        if ($appEnv === 'production') {
            $this->addResult('Laravel Environment', 'pass',
                'production', 'Good for production.');
        } elseif (! empty($appEnv)) {
            $this->addResult('Laravel Environment', 'warn',
                $appEnv, 'Set APP_ENV=production in production.');
        }
    }

    private function checkLaravelSpecific(): void
    {
        // Version
        $composerFile = __DIR__.'/../composer.json';
        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);
            $laravel = $composer['require']['laravel/framework'] ?? 'Unknown';
            $php = $composer['require']['php'] ?? 'Unknown';

            $this->addResult('Laravel Framework', 'info',
                'Required: '.$laravel, 'PHP constraint: '.$php);

            // Check if Laravel 13
            if (preg_match('/13\./', $laravel)) {
                $this->addResult('Laravel 13', 'pass',
                    'Laravel 13 detected.', '');
            }

            // Count deps
            $deps = count($composer['require'] ?? []);
            $devDeps = count($composer['require-dev'] ?? []);
            $this->addResult('Composer Dependencies', 'info',
                "{$deps} runtime, {$devDeps} dev", '');
        }

        // Cache/config/route cache status
        $bootstrapCache = __DIR__.'/../bootstrap/cache';
        foreach (['config.php', 'routes-v7.php', 'packages.php', 'services.php', 'events.php'] as $file) {
            $path = $bootstrapCache.'/'.$file;
            if (file_exists($path)) {
                $size = round(filesize($path) / 1024, 1);
                $this->addResult("Bootstrap Cache: {$file}", 'pass',
                    "Cached ({$size} KB)", '');
            }
        }

        // Storage symlink
        $storageLink = __DIR__.'/storage';
        if (is_link($storageLink)) {
            $target = readlink($storageLink);
            $valid = $target && is_dir($storageLink);
            $this->addResult('Storage Symlink',
                $valid ? 'pass' : 'fail',
                $valid ? 'Valid link → '.$target : 'Broken link',
                $valid ? 'OK' : 'Recreate with php artisan storage:link.');
        } else {
            $this->addResult('Storage Symlink', 'warn',
                'Not created', 'Run php artisan storage:link if serving uploaded files.');
        }

        // Maintenance mode
        if (file_exists(__DIR__.'/../storage/framework/maintenance.php')) {
            $this->addResult('Maintenance Mode', 'warn',
                'Application is DOWN for maintenance',
                'Run php artisan up to restore.');
        } else {
            $this->addResult('Maintenance Mode', 'pass',
                'Application is live', '');
        }

        // Schedule cache
        $scheduleCache = __DIR__.'/../bootstrap/cache/schedule.php';
        $this->addResult('Scheduler Cache', file_exists($scheduleCache) ? 'pass' : 'info',
            file_exists($scheduleCache) ? 'Cached' : 'Not cached',
            'Consider: php artisan schedule:cache');
    }

    private function checkLaravelLogs(): void
    {
        $logDir = __DIR__.'/../storage/logs';
        if (! is_dir($logDir)) {
            return;
        }

        $logFiles = glob($logDir.'/*.log');
        if (empty($logFiles)) {
            $this->addResult('Laravel Logs', 'info',
                'No log files', '');

            return;
        }

        // Recent log file
        usort($logFiles, fn ($a, $b) => filemtime($b) - filemtime($a));
        $latest = $logFiles[0];
        $sizeMB = round(filesize($latest) / 1048576, 2);
        $modified = date('Y-m-d H:i:s', filemtime($latest));

        $this->addResult('Laravel Logs', $sizeMB > 100 ? 'warn' : 'info',
            'Latest: '.basename($latest)." ({$sizeMB} MB)",
            "Modified: {$modified}");

        // Count recent errors
        $content = @file_get_contents($latest, false, null, max(0, filesize($latest) - 100000));
        if ($content) {
            $errorCount = substr_count($content, '.ERROR:');
            $criticalCount = substr_count($content, '.CRITICAL:');
            $this->addResult('Recent Log Errors',
                $criticalCount > 0 ? 'fail' : ($errorCount > 10 ? 'warn' : 'pass'),
                "{$errorCount} ERROR, {$criticalCount} CRITICAL (last 100KB)",
                $errorCount > 0 ? 'Check storage/logs/ for details.' : 'Clean.');
        }
    }

    // ---------------------------------------------------------------------
    // SECURITY
    // ---------------------------------------------------------------------

    private function checkSecurity(): void
    {
        $this->addResult('Security Notice', 'info',
            'Delete check_php.php after use!',
            'This diagnostic tool should not remain on production.');

        // .env exposure via HTTP
        if (ENABLE_NETWORK_TESTS) {
            $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $envUrl = "{$scheme}://{$domain}/.env";

            $ctx = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
            $body = @file_get_contents($envUrl, false, $ctx);

            if ($body && (str_contains($body, 'APP_KEY') || str_contains($body, 'DB_PASSWORD'))) {
                $this->addResult('Security: .env Exposure', 'fail',
                    'CRITICAL: .env is publicly accessible!',
                    'Fix web server configuration immediately.');
            } else {
                $this->addResult('Security: .env Exposure', 'pass',
                    '.env is not accessible via HTTP.', '');
            }
        }

        // Check for exposed .git
        if (ENABLE_NETWORK_TESTS) {
            $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $ctx = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
            $body = @file_get_contents("{$scheme}://{$domain}/.git/config", false, $ctx);

            if ($body && str_contains($body, '[core]')) {
                $this->addResult('Security: .git Exposure', 'fail',
                    'CRITICAL: .git directory is accessible!',
                    'Block .git in your web server config.');
            } else {
                $this->addResult('Security: .git Exposure', 'pass',
                    '.git not accessible.', '');
            }
        }

        // Check for composer.json/lock exposure
        if (ENABLE_NETWORK_TESTS) {
            $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $ctx = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
            $body = @file_get_contents("{$scheme}://{$domain}/composer.json", false, $ctx);

            if ($body && str_contains($body, '"require"')) {
                $this->addResult('Security: composer.json Exposure', 'fail',
                    'composer.json is publicly accessible.',
                    'Block access in web server.');
            } else {
                $this->addResult('Security: composer.json Exposure', 'pass',
                    'Not accessible.', '');
            }
        }
    }

    // ---------------------------------------------------------------------
    // UTILITIES
    // ---------------------------------------------------------------------

    private function tryLoadAutoload(): void
    {
        $autoload = __DIR__.'/../vendor/autoload.php';
        if (file_exists($autoload)) {
            try {
                require_once $autoload;
                $this->autoloadLoaded = true;
            } catch (Throwable $e) {
                // Silent
            }
        }
    }

    private function compareIni(string $current, string $recommended, string $mode): bool
    {
        switch ($mode) {
            case 'bytes_min':
                return $this->parseBytes($current) >= $this->parseBytes($recommended);
            case 'int_min':
                return (int) $current >= (int) $recommended;
            case 'exact':
                return $current === $recommended;
            default:
                return true;
        }
    }

    private function parseBytes(string $val): int
    {
        $val = trim($val);
        if ($val === '' || $val === '-1') {
            return PHP_INT_MAX;
        }
        $last = strtolower($val[strlen($val) - 1] ?? '');
        $num = (int) $val;
        switch ($last) {
            case 'g': $num *= 1024;
            case 'm': $num *= 1024;
            case 'k': $num *= 1024;
        }

        return $num;
    }

    private function addResult(string $label, string $status, string $value, string $note = ''): void
    {
        $this->results[] = compact('label', 'status', 'value', 'note');
        if (isset($this->summary[$status])) {
            $this->summary[$status]++;
        }
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getSummary(): array
    {
        return $this->summary;
    }

    public function getOverallStatus(): string
    {
        if ($this->summary['fail'] > 0) {
            return 'fail';
        }
        if ($this->summary['warn'] > 0) {
            return 'warn';
        }

        return 'pass';
    }
}

// =========================================================================
// RUN
// =========================================================================
$checker = new ServerCheck;
$checker->run();
$results = $checker->getResults();
$summary = $checker->getSummary();
$overall = $checker->getOverallStatus();

function statusBadge(string $status): string
{
    return match ($status) {
        'pass' => '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>PASS</span>',
        'fail' => '<span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i>FAIL</span>',
        'warn' => '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill me-1"></i>WARN</span>',
        'info' => '<span class="badge bg-info text-dark"><i class="bi bi-info-circle-fill me-1"></i>INFO</span>',
        default => '<span class="badge bg-secondary">UNKNOWN</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Diagnostics v2.0 | Laravel 13 Readiness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --bs-body-bg: #f4f6f9; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--bs-body-bg); }
        .header-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #fff; border-radius: 1rem; padding: 2rem;
            margin-bottom: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header-card h1 { font-weight: 700; letter-spacing: -0.5px; }
        .summary-card { border: none; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06); transition: transform 0.2s; }
        .summary-card:hover { transform: translateY(-2px); }
        .summary-card .display-6 { font-weight: 700; }
        .result-table { background: #fff; border-radius: 1rem; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .result-table thead { background-color: #1e293b; color: #fff; position: sticky; top: 0; z-index: 10; }
        .result-table td { vertical-align: middle; padding: 0.85rem 1rem; }
        .note-text { font-size: 0.85rem; color: #64748b; margin-top: 0.25rem; }
        .footer { margin-top: 3rem; padding: 1.5rem 0; text-align: center; color: #94a3b8; font-size: 0.85rem; }
        .badge { font-weight: 500; padding: 0.4em 0.7em; }
        .table-hover tbody tr:hover { background-color: #f8fafc; }
        .overall-indicator { display: inline-flex; align-items: center; gap: 0.5rem; font-size: 1.1rem; font-weight: 600; }
        .sticky-filter { position: sticky; top: 0; z-index: 11; background: white; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; }
        .table-responsive { max-height: 80vh; overflow-y: auto; }
        tr.filtered-out { display: none !important; }
    </style>
</head>
<body>
<div class="container-fluid py-4 px-md-4">

    <div class="header-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-shield-check me-2"></i>Enterprise Server Diagnostics</h1>
                <p class="mb-0 opacity-75">Laravel 13 Readiness • v2.0 • Full-Stack Health Check</p>
            </div>
            <div class="text-end">
                <span class="overall-indicator">
                    Overall: <?= statusBadge($overall) ?>
                </span>
                <br>
                <small class="opacity-50"><?= date('Y-m-d H:i:s T') ?></small>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['Passed', $summary['pass'], 'success', 'check-circle-fill'],
            ['Failed', $summary['fail'], 'danger', 'x-circle-fill'],
            ['Warnings', $summary['warn'], 'warning', 'exclamation-triangle-fill'],
            ['Info', $summary['info'], 'info', 'info-circle-fill'],
            ['Total Checks', count($results), 'secondary', 'list-check'],
        ];
foreach ($cards as [$label, $count, $color, $icon]) { ?>
        <div class="col-6 col-md-4 col-xl">
            <div class="card summary-card border-start border-4 border-<?= $color ?> h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1" style="font-size:0.7rem;"><?= $label ?></h6>
                            <span class="display-6 text-<?= $color ?>"><?= $count ?></span>
                        </div>
                        <i class="bi bi-<?= $icon ?> text-<?= $color ?> fs-1 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- Results Table -->
    <div class="result-table">
        <div class="sticky-filter d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-dark active" onclick="filterRows('all')">All</button>
                <button type="button" class="btn btn-outline-success" onclick="filterRows('pass')">Pass</button>
                <button type="button" class="btn btn-outline-danger" onclick="filterRows('fail')">Fail</button>
                <button type="button" class="btn btn-outline-warning" onclick="filterRows('warn')">Warn</button>
                <button type="button" class="btn btn-outline-info" onclick="filterRows('info')">Info</button>
            </div>
            <input type="text" id="search" class="form-control form-control-sm" style="max-width:300px;"
                   placeholder="Search checks..." oninput="searchRows(this.value)">
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0" id="resultsTable">
                <thead>
                    <tr>
                        <th style="width:25%;">Check</th>
                        <th style="width:10%;">Status</th>
                        <th style="width:65%;">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r) { ?>
                    <tr data-status="<?= $r['status'] ?>">
                        <td class="fw-semibold"><?= htmlspecialchars($r['label']) ?></td>
                        <td><?= statusBadge($r['status']) ?></td>
                        <td>
                            <?= htmlspecialchars($r['value']) ?>
                            <?php if (! empty($r['note'])) { ?>
                                <div class="note-text"><?= htmlspecialchars($r['note']) ?></div>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <p class="mb-1">
            <i class="bi bi-shield-lock me-1"></i>
            <strong>Security Reminder:</strong> Delete <code>check_php.php</code> from production after use.
        </p>
        <p class="mb-0">&copy; <?= date('Y') ?> Enterprise Diagnostics v2.0</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentFilter = 'all';

function filterRows(status) {
    currentFilter = status;
    document.querySelectorAll('.sticky-filter .btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    applyFilters();
}

function searchRows(query) {
    applyFilters(query);
}

function applyFilters(query = null) {
    const q = (query ?? document.getElementById('search').value).toLowerCase();
    document.querySelectorAll('#resultsTable tbody tr').forEach(row => {
        const matchStatus = currentFilter === 'all' || row.dataset.status === currentFilter;
        const matchSearch = !q || row.textContent.toLowerCase().includes(q);
        row.classList.toggle('filtered-out', !(matchStatus && matchSearch));
    });
}
</script>
</body>
</html>