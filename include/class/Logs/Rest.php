<?php
declare(strict_types=1);

namespace gik25microdata\Logs;

if (!defined('ABSPATH')) {
    exit;
}

final class Rest
{
    public static function init(): void
    {
        if (!function_exists('add_action')) {
            return;
        }
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        if (!function_exists('register_rest_route')) {
            return;
        }
        register_rest_route('gik25/v1', '/logs', [
            'methods'  => 'GET',
            'callback' => [self::class, 'rest_logs'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            'args' => [
                'severity' => ['type' => 'string',  'required' => false],
                'file'     => ['type' => 'string',  'required' => false],
                'since'    => ['type' => 'string',  'required' => false],
                'until'    => ['type' => 'string',  'required' => false],
                'context'  => ['type' => 'string',  'required' => false],
                'limit'    => ['type' => 'integer', 'required' => false],
                'offset'   => ['type' => 'integer', 'required' => false],
                'hours'    => ['type' => 'integer', 'required' => false],
                'baseline' => ['type' => 'string',  'required' => false],
                'format'   => ['type' => 'string',  'required' => false, 'enum' => ['json', 'csv']],
                'debug'    => ['type' => 'boolean', 'required' => false],
            ],
        ]);
    }

    public static function rest_logs(\WP_REST_Request $req): \WP_REST_Response
    {
        Trace::init();

        $base = null;
        if (class_exists('gik25microdata\\Logs\\Resolver\\LogSourceResolver')) {
            try {
                $base = \gik25microdata\Logs\Resolver\LogSourceResolver::find_logs_directory() ?: null;
            } catch (\Throwable $e) {
                $base = null;
            }
        }

        $opts = [
            'base'     => $base ?: '/home/1340912.cloudwaysapps.com/gwvyrysadj/logs',
            'severity' => (string) ($req->get_param('severity') ?? 'fatal,error,warning,info,debug'),
            'file'     => $req->get_param('file'),
            'since'    => $req->get_param('since'),
            'until'    => $req->get_param('until'),
            'context'  => (string) ($req->get_param('context') ?? 'wp_cli,ajax,wp_cron,frontend,backend,rest_api,unknown'),
            'limit'    => (int) ($req->get_param('limit') ?? 1000),
            'offset'   => (int) ($req->get_param('offset') ?? 0),
            'hours'    => (int) ($req->get_param('hours') ?? 0),
        ];

        $format = strtolower((string) ($req->get_param('format') ?? 'json'));

        $out = Pipeline::run($opts);
        $formattedErrors = self::formatForViewer($out['errors'] ?? [], $out['debug'] ?? []);
        $out['errors'] = $formattedErrors;
        $out['total'] = count($formattedErrors);

        $baselineRaw = $req->get_param('baseline');
        if ($baselineRaw) {
            $decoded = rawurldecode((string) $baselineRaw);
            $json = json_decode($decoded, true);
            $out['debug']['baseline_diff'] = self::baselineDiff(is_array($json) ? $json : null, $out['debug']);
            Trace::log('baseline.diff', $out['debug']['baseline_diff'] ?? []);
        }

        if ($format === 'csv') {
            return self::exportCsv($formattedErrors);
        }

        return new \WP_REST_Response($out, 200);
    }

    /**
     * @param array<int,array<string,mixed>> $rows
     * @param array<string,mixed> $debug
     * @return array<int,array<string,mixed>>
     */
    private static function formatForViewer(array $rows, array $debug): array
    {
        $selectedFile = $debug['paths']['selected_error_file'] ?? null;
        $formatted = [];
        foreach ($rows as $index => $row) {
            $tsUnix = $row['timestamp_unix'] ?? null;
            $id = $row['id'] ?? md5(($row['ts'] ?? '') . ($row['line'] ?? '') . '-' . $index);
            $formatted[] = [
                'id' => $id,
                'timestamp' => $tsUnix,
                'severity' => $row['severity'] ?? 'info',
                'message' => $row['line'] ?? '',
                'file' => $row['php_file'] ?? $row['file'] ?? $selectedFile,
                'line' => $row['php_line'] ?? null,
                'contexts' => $row['context'] ? [$row['context']] : [],
                'count' => 1,
                'first_seen' => $tsUnix,
                'last_seen' => $tsUnix,
            ];
        }
        return $formatted;
    }

    /**
     * @param array<int,array<string,mixed>> $errors
     */
    private static function exportCsv(array $errors): \WP_REST_Response
    {
        $lines = [];
        $lines[] = 'ID,Timestamp,Severity,Message,File,Line,Context,Count,First Seen,Last Seen';
        foreach ($errors as $error) {
            $lines[] = implode(',', [
                self::csvEscape((string) ($error['id'] ?? '')),
                self::csvEscape(isset($error['timestamp']) ? (string) $error['timestamp'] : ''),
                self::csvEscape((string) ($error['severity'] ?? '')),
                self::csvEscape((string) ($error['message'] ?? '')),
                self::csvEscape((string) ($error['file'] ?? '')),
                self::csvEscape(isset($error['line']) ? (string) $error['line'] : ''),
                self::csvEscape(isset($error['contexts']) ? implode('|', (array) $error['contexts']) : ''),
                self::csvEscape((string) ($error['count'] ?? 0)),
                self::csvEscape(isset($error['first_seen']) ? (string) $error['first_seen'] : ''),
                self::csvEscape(isset($error['last_seen']) ? (string) $error['last_seen'] : ''),
            ]);
        }
        $body = implode("\n", $lines);
        $response = new \WP_REST_Response($body, 200);
        $response->header('Content-Type', 'text/csv; charset=utf-8');
        $response->header('Content-Disposition', 'attachment; filename="gik25-logs-' . gmdate('Ymd-His') . '.csv"');
        return $response;
    }

    private static function csvEscape(string $value): string
    {
        $escaped = str_replace('"', '""', $value);
        if (strpbrk($escaped, ",\n")) {
            return '"' . $escaped . '"';
        }
        return $escaped;
    }

    /**
     * @param array<string,mixed>|null $baseline
     * @param array<string,mixed> $currentDebug
     * @return array<string,mixed>
     */
    private static function baselineDiff(?array $baseline, array $currentDebug): array
    {
        $diff = ['notes' => [], 'delta' => []];
        if (!$baseline) {
            $diff['notes'][] = 'No baseline provided';
            return $diff;
        }

        $bFile = $baseline['paths']['selected_error_file'] ?? null;
        $cFile = $currentDebug['paths']['selected_error_file'] ?? null;
        if ($bFile !== $cFile) {
            $diff['delta']['selected_error_file'] = ['baseline' => $bFile, 'current' => $cFile];
            $diff['notes'][] = 'Different selected_error_file';
        }

        foreach (['apache_error_candidates','nginx_error_candidates','php_error_candidates'] as $key) {
            $bCount = is_array($baseline['paths'][$key] ?? null) ? count($baseline['paths'][$key]) : 0;
            $cCount = is_array($currentDebug['paths'][$key] ?? null) ? count($currentDebug['paths'][$key]) : 0;
            if ($bCount !== $cCount) {
                $diff['delta'][$key] = ['baseline' => $bCount, 'current' => $cCount];
                $diff['notes'][] = 'Different ' . $key . ' count';
            }
        }

        $bBefore = (int) ($baseline['issues_before_filters'] ?? -1);
        $cBefore = (int) ($currentDebug['issues_before_filters'] ?? -1);
        $bAfter  = (int) ($baseline['issues_after_filters'] ?? -1);
        $cAfter  = (int) ($currentDebug['issues_after_filters'] ?? -1);
        if ($bBefore !== $cBefore || $bAfter !== $cAfter) {
            $diff['delta']['issues'] = ['baseline' => [$bBefore, $bAfter], 'current' => [$cBefore, $cAfter]];
            $diff['notes'][] = 'Different issues counts (before/after filters)';
        }

        $bReason = $baseline['reason'] ?? null;
        $cReason = $currentDebug['reason'] ?? null;
        if (($bReason ?? '') !== ($cReason ?? '')) {
            $diff['delta']['reason'] = ['baseline' => $bReason, 'current' => $cReason];
            $diff['notes'][] = 'Different zero-reason';
        }

        return $diff;
    }
}
