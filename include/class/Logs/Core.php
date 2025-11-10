<?php
declare(strict_types=1);

namespace gik25microdata\Logs;

use DateTimeImmutable;
use DateTimeZone;
use gik25microdata\Logs\Resolver\LogSourceResolver;

if (!defined('ABSPATH')) {
    exit;
}

// Polyfills for PHP < 8.0 (plugin minimum is 7.4)
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }
        $len = strlen($needle);
        if ($len === 0) {
            return true;
        }
        return substr($haystack, -$len) === $needle;
    }
}

final class Trace
{
    private static string $file = '';
    private static string $traceId = '';

    public static function init(): void
    {
        if (!defined('GIK25_LOG_TRACE') || !GIK25_LOG_TRACE) {
            return;
        }
        if (!defined('WP_CONTENT_DIR')) {
            return;
        }
        $dir = WP_CONTENT_DIR . '/uploads/gik25-debug';
        if (!is_dir($dir)) {
            if (function_exists('wp_mkdir_p')) {
                @wp_mkdir_p($dir);
            } else {
                @mkdir($dir, 0775, true);
            }
        }
        self::$file = $dir . '/gik25-rest-traces.log';
        if (file_exists(self::$file) && @filesize(self::$file) > 50 * 1024 * 1024) {
            @rename(self::$file, $dir . '/gik25-rest-traces-' . date('Ymd-His') . '.log');
        }
        if (!self::$traceId) {
            try {
                self::$traceId = bin2hex(random_bytes(8));
            } catch (\Throwable $e) {
                self::$traceId = (string) wp_generate_uuid4();
            }
        }
    }

    public static function id(): string
    {
        return self::$traceId ?: 'no-trace';
    }

    public static function log(string $phase, array $data): void
    {
        if (!defined('GIK25_LOG_TRACE') || !GIK25_LOG_TRACE) {
            return;
        }
        $row = [
            'ts'     => microtime(true),
            'trace'  => self::id(),
            'phase'  => $phase,
            'memory' => memory_get_usage(true),
            'data'   => $data,
        ];
        $json = json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (self::$file) {
            @file_put_contents(self::$file, $json . PHP_EOL, FILE_APPEND);
        }
        if (function_exists('error_log')) {
            @error_log('[GIK25][' . self::id() . '][' . $phase . '] ' . $json);
        }
    }
}

// Classe LogSourceResolver rimossa: duplicato di Logs/Resolver/LogSourceResolver.php
// Usa gik25microdata\Logs\Resolver\LogSourceResolver invece

final class LogReader
{
    public static function readTail(string $file, int $maxBytes = 2_000_000): string
    {
        $isGz = str_ends_with($file, '.gz');
        if ($isGz) {
            $fp = @gzopen($file, 'rb');
            if (!$fp) {
                return '';
            }
            $buf = '';
            while (!gzeof($fp)) {
                $buf .= (string) gzread($fp, 16384);
                if (strlen($buf) > $maxBytes) {
                    $buf = substr($buf, -$maxBytes);
                }
            }
            gzclose($fp);
            return $buf;
        }
        $size = @filesize($file) ?: 0;
        $fp = @fopen($file, 'rb');
        if (!$fp) {
            return '';
        }
        if ($size > $maxBytes) {
            fseek($fp, -$maxBytes, SEEK_END);
        }
        $buf = (string) stream_get_contents($fp);
        fclose($fp);
        return $buf;
    }
}

final class LogParser
{
    private const SEV_MAP = [
        'emerg' => 'fatal', 'alert' => 'fatal', 'crit' => 'fatal', 'error' => 'error', 'warn' => 'warning', 'notice' => 'info', 'info' => 'info', 'debug' => 'debug',
        'PHP Fatal error' => 'fatal', 'PHP Parse error' => 'fatal', 'PHP Warning' => 'warning', 'PHP Notice' => 'info', 'PHP Deprecated' => 'warning',
    ];

    /**
     * @return array{ts:?string, severity:?string, context:?string, line:string}
     */
    public static function parseLine(string $line): array
    {
        $ts = null;
        $sev = null;

        if (preg_match('~^\[(.*?)\]\s+\[(?:php:)?([a-z]+)\]~i', $line, $m)) {
            $ts = $m[1] ?? null;
            $sev = strtolower($m[2] ?? '');
            $sev = self::SEV_MAP[$sev] ?? $sev;
        } elseif (preg_match('~^(\d{4}/\d{2}/\d{2}\s+\d{2}:\d{2}:\d{2})\s+\[([a-z]+)\]~i', $line, $m)) {
            $ts  = $m[1] ?? null;
            $sev = strtolower($m[2] ?? '');
            $sev = self::SEV_MAP[$sev] ?? $sev;
        } elseif (preg_match('~^(PHP (?:Fatal error|Parse error|Warning|Notice|Deprecated))~', $line, $m)) {
            $sev = self::SEV_MAP[$m[1]] ?? 'info';
        }

        $ctx = 'unknown';
        if (str_contains($line, 'wp-cron.php')) {
            $ctx = 'wp_cron';
        } elseif (str_contains($line, 'wp-json')) {
            $ctx = 'rest_api';
        } elseif (str_contains($line, 'wp-admin')) {
            $ctx = 'backend';
        } elseif (str_contains($line, 'wp-cli.phar') || str_contains($line, 'WP_CLI')) {
            $ctx = 'wp_cli';
        } elseif (str_contains($line, 'admin-ajax.php')) {
            $ctx = 'ajax';
        } else {
            $ctx = 'frontend';
        }

        return ['ts' => $ts, 'severity' => $sev, 'context' => $ctx, 'line' => rtrim($line, "\r\n")];
    }
}

final class Pipeline
{
    public static function run(array $opts): array
    {
        $trace = ['steps' => [], 'annotations' => []];

        $base = $opts['base'] ?? '/home/1340912.cloudwaysapps.com/gwvyrysadj/logs';
        $disc_raw = LogSourceResolver::discover($base);
        $overrideInput = isset($opts['file']) ? trim((string) $opts['file']) : '';
        
        // Converti formato discover() in formato candidates per compatibilità
        $candidates = [];
        $all_files = [];
        foreach ($disc_raw as $entry) {
            $type = $entry['type'] ?? 'unknown';
            if (!isset($candidates[$type])) {
                $candidates[$type] = [];
            }
            $candidates[$type][] = $entry['path'];
            $all_files[] = $entry['path'];
        }
        
        $disc = [
            'base' => $base,
            'candidates' => $candidates,
            'all' => $all_files,
        ];
        
        $sel = $overrideInput !== '' ? self::resolveFileOverride($overrideInput, $disc) : null;
        if (!$sel) {
            $sel = LogSourceResolver::selectErrorFile($candidates);
        }

        $trace['discovery'] = [
            'base'                 => $base,
            'candidates'           => $candidates,
            'selected_error_file'  => $sel,
            'override_request'     => $overrideInput ?: null,
            'override_matched'     => $overrideInput !== '' ? $sel : null,
            'all_discovered_files' => $disc['all'],
        ];

        if (!$sel || !is_readable($sel)) {
            $trace['annotations'][] = 'selected_error_file missing or unreadable';
            return [
                'total' => 0, 'errors' => [],
                'debug' => self::debugBlock($opts, $disc, $sel, 0, 0, 'selected_error_missing', $trace),
            ];
        }

        $raw = LogReader::readTail($sel, 4_000_000);
        $lines = $raw === '' ? [] : (preg_split('~\R~', $raw) ?: []);
        $countRead = count($lines);
        Trace::log('read', ['file' => $sel, 'bytes' => strlen($raw), 'lines' => $countRead]);

        $parsed = [];
        $noSev = 0;
        $noTs = 0;
        $tzContext = self::resolveTimezone();
        foreach ($lines as $ln) {
            if ($ln === '') {
                continue;
            }
            $row = LogParser::parseLine($ln);
            if (!$row['severity']) {
                $noSev++;
            }
            $row = self::enrichRow($row, $tzContext);
            if ($row['timestamp_unix'] === null) {
                $noTs++;
            }
            $parsed[] = $row;
        }
        $trace['parsing'] = ['read_lines' => $countRead, 'parsed_lines' => count($parsed), 'severity_missing' => $noSev, 'timestamp_missing' => $noTs];

        $before = count($parsed);
        $filtersApplied = [];

        [$parsed, $sevInfo] = self::filterSeverity($parsed, (string) ($opts['severity'] ?? 'fatal,error,warning,info,debug'));
        $filtersApplied[] = $sevInfo;

        [$parsed, $ctxInfo] = self::filterContext($parsed, (string) ($opts['context'] ?? 'wp_cli,ajax,wp_cron,frontend,backend,rest_api,unknown'));
        $filtersApplied[] = $ctxInfo;

        [$parsed, $timeInfo] = self::filterTime($parsed, $opts['since'] ?? null, $opts['until'] ?? null, (int) ($opts['hours'] ?? 0), $tzContext);
        $filtersApplied[] = $timeInfo;

        $trace['filters'] = $filtersApplied;

        $totalAfterFilters = count($parsed);
        Trace::log('filtering', ['before' => $before, 'after' => $totalAfterFilters, 'steps' => $filtersApplied]);

        $limit  = max(1, min(5000, (int) ($opts['limit'] ?? 1000)));
        $offset = max(0, (int) ($opts['offset'] ?? 0));
        $slice  = array_slice($parsed, $offset, $limit);

        $reason = null;
        if ($totalAfterFilters === 0) {
            $reason = self::explainZero($before, $filtersApplied, $noSev, $noTs);
            $trace['annotations'][] = $reason;
        }

        return [
            'total'  => $totalAfterFilters,
            'errors' => $slice,
            'limit'  => $limit,
            'offset' => $offset,
            'debug'  => self::debugBlock($opts, $disc, $sel, $before, $totalAfterFilters, $reason, $trace),
        ];
    }

    /**
     * Filtro generico per lista di valori
     * 
     * @param array $rows Righe da filtrare
     * @param string $list Lista valori separati da virgola
     * @param callable $extractor Callback che estrae il valore da confrontare: fn($row) => string|null
     * @param string $name Nome del filtro per il report
     * @param bool $includeEmpty Se true, include righe senza valore (default: false)
     * @return array{0: array, 1: array} [righe filtrate, info filtro]
     */
    private static function filterByList(array $rows, string $list, callable $extractor, string $name, bool $includeEmpty = false): array
    {
        $allowed = array_filter(array_map('trim', explode(',', strtolower($list))));
        $before = count($rows);
        if (!$allowed) {
            return [$rows, ['name' => $name, 'before' => $before, 'after' => $before, 'allowed' => '<none>', 'note' => "no {$name} filter"]];
        }
        
        $filtered = array_values(array_filter($rows, function($r) use ($extractor, $allowed, $includeEmpty) {
            $value = $extractor($r);
            if ($includeEmpty && empty($value)) {
                return true; // Includi righe senza valore
            }
            return !empty($value) && in_array(strtolower((string) $value), $allowed, true);
        }));
        
        $note = $includeEmpty ? "righe senza {$name} incluse" : null;
        return [$filtered, ['name' => $name, 'before' => $before, 'after' => count($filtered), 'allowed' => implode(',', $allowed), 'note' => $note]];
    }

    private static function filterSeverity(array $rows, string $list): array
    {
        return self::filterByList(
            $rows,
            $list,
            fn($r) => $r['severity'] ?? null,
            'severity',
            true // Include righe senza severità
        );
    }

    private static function filterContext(array $rows, string $list): array
    {
        return self::filterByList(
            $rows,
            $list,
            fn($r) => $r['context'] ?? null,
            'context',
            false // Escludi righe senza contesto
        );
    }

    private static function filterTime(array $rows, ?string $since, ?string $until, int $hours, DateTimeZone $tz): array
    {
        $before = count($rows);

        $sinceDt = $since ? new DateTimeImmutable($since, $tz) : null;
        $untilDt = $until ? new DateTimeImmutable($until, $tz) : null;
        if ($hours > 0 && !$sinceDt && !$untilDt) {
            $untilDt = new DateTimeImmutable('now', $tz);
            $sinceDt = $untilDt->modify('-' . $hours . ' hours');
        }

        if (!$sinceDt && !$untilDt) {
            return [$rows, ['name' => 'time', 'before' => $before, 'after' => $before, 'since' => null, 'until' => null, 'note' => 'no time filter']];
        }

        $ok = [];
        foreach ($rows as $r) {
            $tsUnix = $r['timestamp_unix'] ?? null;
            // Se non ha timestamp, lo includiamo comunque (non filtriamo righe senza timestamp)
            if (!$tsUnix) {
                $ok[] = $r;
                continue;
            }
            $dt = (new DateTimeImmutable('@' . $tsUnix))->setTimezone($tz);
            if ($sinceDt && $dt < $sinceDt) {
                continue;
            }
            if ($untilDt && $dt > $untilDt) {
                continue;
            }
            $r['timestamp_iso'] = $dt->format(DateTimeImmutable::ATOM);
            $ok[] = $r;
        }
        return [$ok, ['name' => 'time', 'before' => $before, 'after' => count($ok), 'since' => $sinceDt ? $sinceDt->format(DATE_ATOM) : null, 'until' => $untilDt ? $untilDt->format(DATE_ATOM) : null]];
    }

    private static function parseAnyTs(string $raw, DateTimeZone $tz): ?DateTimeImmutable
    {
        $raw = trim($raw, '[]');
        $candidates = [
            'D M d H:i:s.u Y',
            'D M d H:i:s Y',
            'Y/m/d H:i:s',
            DATE_ATOM,
            'Y-m-d H:i:s',
        ];
        foreach ($candidates as $fmt) {
            $dt = DateTimeImmutable::createFromFormat($fmt, $raw, $tz);
            if ($dt !== false) {
                return $dt;
            }
        }
        $t = strtotime($raw);
        return $t ? (new DateTimeImmutable('@' . $t))->setTimezone($tz) : null;
    }

    private static function explainZero(int $before, array $filters, int $noSev, int $noTs): string
    {
        $notes = [];
        $notes[] = 'read=' . $before;
        foreach ($filters as $f) {
            $notes[] = ($f['name'] ?? 'filter') . ':' . ($f['before'] ?? 0) . '→' . ($f['after'] ?? 0);
        }
        if ($noSev > 0) {
            $notes[] = 'severity_missing=' . $noSev . ' (parser non ha riconosciuto la severità: controlla regex/format)';
        }
        if ($noTs > 0) {
            $notes[] = 'timestamp_missing=' . $noTs . ' (righe scartate dai filtri tempo)';
        }
        return 'zero_after_filters: ' . implode(' | ', $notes);
    }

    private static function debugBlock(array $opts, array $disc, ?string $sel, int $before, int $after, ?string $reason, array $trace): array
    {
        return [
            'filters' => [
                'severity' => $opts['severity'] ?? null,
                'file'     => $opts['file'] ?? null,
                'since'    => $opts['since'] ?? null,
                'until'    => $opts['until'] ?? null,
                'context'  => $opts['context'] ?? null,
                'limit'    => (int) ($opts['limit'] ?? 1000),
                'offset'   => (int) ($opts['offset'] ?? 0),
            ],
            'paths' => [
                'base' => $disc['base'] ?? '',
                'apache_error_candidates' => $disc['candidates']['apache_error'] ?? [],
                'nginx_error_candidates'  => $disc['candidates']['nginx_error'] ?? [],
                'php_error_candidates'    => $disc['candidates']['php_error'] ?? [],
                'all_error_candidates'    => array_values(array_unique(array_merge(
                    $disc['candidates']['apache_error'] ?? [],
                    $disc['candidates']['nginx_error'] ?? [],
                    $disc['candidates']['php_error'] ?? [],
                    $disc['candidates']['php_fpm_error'] ?? [],
                ))),
                'selected_error_file'     => $sel,
            ],
            'hours' => (int) ($opts['hours'] ?? 0),
            'issues_before_filters' => $before,
            'issues_after_filters'  => $after,
            'reason' => $reason,
            'trace_id' => Trace::id(),
            'phases'  => $trace,
        ];
    }

    private static function resolveTimezone(): DateTimeZone
    {
        $tzString = function_exists('wp_timezone_string') ? wp_timezone_string() : null;
        if (!$tzString && function_exists('get_option')) {
            $option = get_option('timezone_string');
            if (is_string($option) && $option !== '') {
                $tzString = $option;
            }
        }
        try {
            return new DateTimeZone($tzString ?: 'Europe/Rome');
        } catch (\Throwable $e) {
            return new DateTimeZone('Europe/Rome');
        }
    }

    private static function enrichRow(array $row, DateTimeZone $tz): array
    {
        $row['ts'] = isset($row['ts']) ? trim((string) $row['ts']) : null;
        $dt = $row['ts'] ? self::parseAnyTs($row['ts'], $tz) : null;
        if ($dt instanceof DateTimeImmutable) {
            $row['timestamp_iso'] = $dt->format(DateTimeImmutable::ATOM);
            $row['timestamp_unix'] = $dt->getTimestamp();
        } else {
            $row['timestamp_iso'] = null;
            $row['timestamp_unix'] = null;
        }
        return $row;
    }

    private static function resolveFileOverride(string $input, array $disc): ?string
    {
        $trimmed = trim($input);
        if ($trimmed === '') {
            return null;
        }
        $candidate = $trimmed;
        if (!self::isAbsolutePath($candidate)) {
            $base = rtrim($disc['base'] ?? '', '/') . '/';
            $candidate = $base . ltrim($trimmed, '/');
        }
        if (@is_file($candidate) && @is_readable($candidate)) {
            Trace::log('resolver.override', ['input' => $input, 'matched' => $candidate, 'mode' => 'direct']);
            return $candidate;
        }
        foreach ($disc['all'] ?? [] as $file) {
            if (stripos($file, $trimmed) !== false) {
                Trace::log('resolver.override', ['input' => $input, 'matched' => $file, 'mode' => 'substring']);
                return $file;
            }
        }
        Trace::log('resolver.override', ['input' => $input, 'matched' => null]);
        return null;
    }

    private static function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }
        return $path[0] === '/' || preg_match('~^[A-Za-z]:\\\\~', $path) === 1;
    }
}

// Optional WP-CLI helper for diagnostics
if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('gik25:logs', function ($args, $assoc) {
        Trace::init();
        $base = $assoc['base'] ?? null;
        if (!$base && class_exists('gik25microdata\\Logs\\Resolver\\LogSourceResolver')) {
            $base = LogSourceResolver::find_logs_directory() ?? null;
        }
        $out = Pipeline::run([
            'base'     => $base ?: '/home/1340912.cloudwaysapps.com/gwvyrysadj/logs',
            'severity' => $assoc['severity'] ?? 'fatal,error,warning,info,debug',
            'context'  => $assoc['context'] ?? 'wp_cli,ajax,wp_cron,frontend,backend,rest_api,unknown',
            'hours'    => (int) ($assoc['hours'] ?? 0),
            'limit'    => (int) ($assoc['limit'] ?? 50),
            'offset'   => 0,
        ]);
        \WP_CLI\Utils\format_items('json', [$out], ['total', 'debug']);
    });

}
